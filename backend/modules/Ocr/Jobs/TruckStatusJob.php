<?php

namespace Modules\Ocr\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Modules\Ocr\Models\OcrMatch;
use Modules\BijacInvoice\Clients\BijacApiClient;
use Illuminate\Support\Facades\Log;

use Modules\BijacInvoice\Services\InvoiceService;
use Modules\BijacInvoice\Models\Invoice;
use Modules\BijacInvoice\Models\Customer;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Ocr\OcrComputedResolver;

class TruckStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $log;
    public $type;

    public function __construct($log, $type)
    {
        $this->log = $log;
        $this->type = $type;
    }

    public function handle()
    {
        $match = OcrMatch::with(["bijacs" => function ($query) {
            $query->with('invoices');
        }])->find($this->log);
        $noInvoice = '';

        if ($match) {

            try {
                // Check total_vehicles <= ocr_vehicles condition
                // Use same logic as OcrMatchController for consistent data
                $resolver = new OcrComputedResolver();
                $context = [
                    'custom_tariff' => config('ocr.custom_tariff'),
                    'gate' => $match->gate_number,
                    'log_time' => $match->log_time,
                ];
                $batchComputedData = $resolver->getBatchComputedData([$match->id], $context);
                $computed = $batchComputedData[$match->id] ?? [];

                if (isset($computed['total_tu']) && isset($computed['ocr_tu'])) {
                    if ($computed['total_tu'] < $computed['ocr_tu']) {

                        // Check if we already tried noInvoice for this match to avoid infinite loop
                        $noInvoiceCacheKey = 'truckstatus_noinvoice_attempted_' . $match->id;
                        if (!cache()->get($noInvoiceCacheKey)) {
                            // Mark that we're attempting noInvoice
                            cache()->put($noInvoiceCacheKey, true, now()->addMinutes(30));

                            // Execute noInvoice to try to get better invoice data
                            $this->noInvoice($this->log, $match);

                            // Reload match after noInvoice execution
                            $match = OcrMatch::with(["bijacs" => function ($query) {
                                $query->with('invoices');
                            }])->find($this->log);

                            // Re-compute data after noInvoice
                            if ($match) {
                                $batchComputedDataAfter = $resolver->getBatchComputedData([$match->id], $context);
                                $computedAfter = $batchComputedDataAfter[$match->id] ?? [];

                                // Check condition again after noInvoice
                                if (isset($computedAfter['total_tu']) && isset($computedAfter['ocr_tu'])) {
                                    if ($computedAfter['total_tu'] < $computedAfter['ocr_tu']) {
                                        // Still true after noInvoice, log it
                                        log::build(['driver' => 'single', 'path' => storage_path("logs/total_tu_check.log"),])
                                            ->info("TruckStatusJob: gate({$match->gate_number}) total_tu ({$computedAfter['total_tu']}) < ocr_tu ({$computedAfter['ocr_tu']}) after noInvoice attempt for match ID: {$match->id}, plate: {$match->plate_number}, container: {$match->container_code}");
                                    }
                                }
                            }
                        } else {
                            // Already attempted noInvoice for this match, just log
                            log::build(['driver' => 'single', 'path' => storage_path("logs/total_tu_check.log"),])
                                ->info("TruckStatusJob: total_tu ({$computed['total_tu']}) < ocr_tu ({$computed['ocr_tu']}) - noInvoice already attempted for match ID: {$match->id}, plate: {$match->plate_number}, container: {$match->container_code}");
                        }
                    }
                }
            } catch (\Throwable $th) {
                // Log error if logging fails
            }


            if (!$match->bijac_has_invoice && $match->bijacs->first()) {
                try {
                    if (!empty($match->plate_number)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob invoice not found for plate_number : {$match->plate_number}  ");
                    } elseif (!empty($match->container_code)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob invoice not found for container_code : {$match->container_code}  ");
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }


                $noInvoice = '_req';
                $this->noInvoice($this->log, $match);
                // sleep(3);

                $match = null;
                $match = OcrMatch::with(["bijacs" => function ($query) {
                    $query->with('invoices');
                }])->find($this->log);
            }

            if (str_contains($match->match_status, '_Creq')) {
                $noInvoice = '_Creq';
            } elseif (str_contains($match->match_status, '_req')) {
                $noInvoice = '_req';
            }

            if ($match->bijac_has_invoice) {

                try {
                    if (!empty($match->plate_number)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob bijac_has_invoice plate_number : {$match->plate_number}  ");
                    } elseif (!empty($match->container_code)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob bijac_has_invoice container_code : {$match->container_code}  ");
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }


                if ($match->bijacs->first()->type === 'gcoms') {
                    return $match->forceFill([
                        'match_status' => 'gcoms_ok' . $noInvoice
                    ])->save();
                } else {
                    if ($match->plate_number && $match->container_code)
                        return $match->forceFill([
                            'match_status' => 'ccs_ok' . $noInvoice
                        ])->save();
                    else if ($match->plate_number)
                        return $match->forceFill([
                            'match_status' => 'plate_ccs_ok' . $noInvoice
                        ])->save();
                    else
                        return $match->forceFill([
                            'match_status' => 'container_ccs_ok' . $noInvoice
                        ])->save();
                }
            } else if ($match->bijacs->first()) {

                try {
                    if (!empty($match->plate_number)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob _bijacs->first plate_number : {$match->plate_number}  ");
                    } elseif (!empty($match->container_code)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob _bijacs->first container_code : {$match->container_code}  ");
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }


                $bijac = $match->bijacs->first();
                if ($bijac->type === 'gcoms') {
                    return $match->forceFill([
                        'match_status' => 'gcoms_nok' . $noInvoice
                    ])->save();
                } else {
                    if ($match->plate_number && $match->container_code)
                        return $match->forceFill([
                            'match_status' => 'ccs_nok' . $noInvoice
                        ])->save();
                    else if ($match->plate_number)
                        return $match->forceFill([
                            'match_status' => 'plate_ccs_nok' . $noInvoice
                        ])->save();
                    else
                        return $match->forceFill([
                            'match_status' => 'container_ccs_nok' . $noInvoice
                        ])->save();
                }
            } else {
                try {
                    if (!empty($match->plate_number)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob _without bijac : {$match->plate_number}  ");
                    } elseif (!empty($match->container_code)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $match->gate_number . ".log"),])
                            ->info("TruckStatusJob _without bijac : {$match->container_code}  ");
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                if ($match->plate_number)
                    return $match->forceFill([
                        'match_status' => 'plate_without_bijac' . $noInvoice
                    ])->save();
                else
                    return $match->forceFill([
                        'match_status' => 'container_without_bijac' . $noInvoice
                    ])->save();
            }

            //item.total_vehicles) < parseInt(item.ocr_vehicles



        }

        try {
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog"),])
                ->info("TruckStatusJob match not find ");
        } catch (\Throwable $th) {
        }
    }

    public function noInvoice($log, $match, $forAll = true)
    {
        // return;
        log::build(['driver' => 'single', 'path' => storage_path("logs/invoiceMoredi.log"),])
            ->info("noInvoice run match:{$match->plate_number} id : {$match->id} ");
        // return;
        if (!cache()->get('truckstatus_reran_' . $log)) {
            cache()->put('truckstatus_reran_' . $log, true, 60);
            try {
                $doned = [];
                foreach ($match->bijacs as $key => $value) {
                    if (isset($doned[$value->receipt_number])) continue;
                    $doned[$value->receipt_number] = true;
                    if ($forAll && str_starts_with($value->receipt_number, 'BSRGCB')) continue;


                    $invoiceService_ = new InvoiceService();
                    $attempts = 0;
                    $maxAttempts = 3;
                    $invoiceService = null;
                    while ($attempts < $maxAttempts) {
                        $invoiceService = $invoiceService_->getWithReceiptNumber($value->receipt_number);
                        // Log::error("getWithReceiptNumber : {$value->receipt_number} - {$match->plate_number}");
                        if (!empty($invoiceService)) {
                            // Log::error("getWithReceiptNumber DONED : {$value->receipt_number} - {$match->plate_number}");
                            log::build(['driver' => 'single', 'path' => storage_path("logs/invoiceMoredi.log"),])
                                ->info("getWithReceiptNumber invoice find match id : {$match->id} _ receipt_number:{$value->receipt_number} - plate_number:{$match->plate_number}");
                            break;
                        }
                        $attempts++;
                        // Log::error("getWithReceiptNumber not find : {$value->receipt_number} - {$match->plate_number}");
                        log::build(['driver' => 'single', 'path' => storage_path("logs/invoiceMoredi.log"),])
                            ->info("getWithReceiptNumber not find match id : {$match->id} _ receipt_number:{$value->receipt_number} - plate_number:{$match->plate_number}");
                        sleep(1);
                    }

                    if ($invoiceService && isset($invoiceService[0])) {

                        $invoiceService = $invoiceService[0];
                        if ($invoiceService && !isset($invoiceService['InvoiceNumber'])) continue;

                        $saveinvoice = new InvoiceService();
                        $saveinvoice->save_invoice($invoiceService);
                        /*
                        $customerUpdating = [];
                            $customerFields = [
                                "title" => "GoodsOwnerName",
                                "shenase_meli" => "GoodsOwnerNationalID",
                                "postal_code" => "GoodsOwnerPostalCode",
                                // "shenase_meli" => "GoodsOwnerEconommicCode",
                            ];
                            foreach ($customerFields as $key => $field) {
                                $customerUpdating[$key] = $invoiceService[$field] ?? null;
                            }
                            $customer = Customer::updateOrCreate(
                                ['shenase_meli' => $invoiceService["GoodsOwnerNationalID"]],
                                $customerUpdating
                            );

                            $fildes = [];
                            $fildes["source_invoice_id"] = time();
                            $fildes['customer_id'] = $customer->id;
                            $fildes["invoice_number"] = $invoiceService['InvoiceNumber'];
                            $fildes["receipt_number"] = $invoiceService['ReceiptNumber'];
                            $fildes["pay_date"] =  $this->MiladiConvertor($invoiceService['InvoiceDate']);
                            $fildes["pay_trace"] = $invoiceService['PayRequestTraceNo'];
                            $fildes["amount"] = $invoiceService['ParkingCost'];
                            $fildes["weight"] = $invoiceService['Weight'];
                            $fildes["tax"] = $invoiceService['Total'] - $invoiceService['ParkingCost'];
                            $fildes["kutazh"] = null;
                            $fildes["number"] = null;
                            $fildes["request_date"] = now();

                            Invoice::create($fildes);
                        */
                        log::build(['driver' => 'single', 'path' => storage_path("logs/invoiceMoredi.log"),])
                            ->info("[TruckStatusJob] down noInvoice match:{$match->plate_number} id : {$match->id} ");
                        break;
                    }

                    sleep(1);
                }
                // $match = OcrMatch::with(["bijacs" => function ($query) {
                //     $query->with('invoices');
                // }])->find($log);
            } catch (\Throwable $e) {
                log::build(['driver' => 'single', 'path' => storage_path("logs/invoiceMoredi.log"),])
                    ->info("❌ [TruckStatusJob] Error during noInvoice match:{$match->plate_number} id : {$match->id} ");
            }
            // return $match;
        }
    }
}
