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

            if (!$match->bijac_has_invoice && $match->bijacs->first()) {
                // Log::error("noInvoice STARTED : {$match->plate_number} -  {$match->container_code}");

                $noInvoice = '_req';
                $this->noInvoice($this->log, $match);
                // sleep(3);

                $match = null;
                $match = OcrMatch::with(["bijacs" => function ($query) {
                    $query->with('invoices');
                }])->find($this->log);
            } elseif (str_contains($match->match_status, '_req')) {
                $noInvoice = '_req';
            }

            if ($match->bijac_has_invoice) {
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
                $bijac = $match->bijacs->first();
                if ($bijac->type === 'gcoms') {
                    return $match->forceFill([
                        'match_status' => 'gcoms_nok'
                    ])->save();
                } else {
                    if ($match->plate_number && $match->container_code)
                        return $match->forceFill([
                            'match_status' => 'ccs_nok'
                        ])->save();
                    else if ($match->plate_number)
                        return $match->forceFill([
                            'match_status' => 'plate_ccs_nok'
                        ])->save();
                    else
                        return $match->forceFill([
                            'match_status' => 'container_ccs_nok'
                        ])->save();
                }
            } else {
                Log::error("plate without bijac : {$match->plate_number} - {$match->id}");

                if ($match->plate_number)
                    return $match->forceFill([
                        'match_status' => 'plate_without_bijac'
                    ])->save();
                else
                    return $match->forceFill([
                        'match_status' => 'container_without_bijac'
                    ])->save();
            }
        }
    }

    public  function MiladiConvertor($data1)
    {
        $miladiDate = null;
        if ($data1 && !empty(trim($data1))) {
            $jalaliDate = Verta::parse($data1);
            $miladiDate = $jalaliDate->formatGregorian('Y-m-d H:i:s');
        }
        return $miladiDate;
    }
    public function noInvoice($log, $match)
    {
        // return;
        if (!cache()->get('truckstatus_reran_' . $log)) {
            cache()->put('truckstatus_reran_' . $log, true, 60);
            try {
                $doned = [];
                foreach ($match->bijacs as $key => $value) {
                    if (isset($doned[$value->receipt_number])) continue;
                    $doned[$value->receipt_number] = true;
                    if (str_starts_with($value->receipt_number, 'BSRGCB')) continue;

                    // $invoiceService_ = new InvoiceService();
                    // $invoiceService =  $invoiceService_->getWithReceiptNumber($value->receipt_number);

                    $invoiceService_ = new InvoiceService();
                    $attempts = 0;
                    $maxAttempts = 3;
                    $invoiceService = null;
                    while ($attempts < $maxAttempts) {
                        $invoiceService = $invoiceService_->getWithReceiptNumber($value->receipt_number);
                        // Log::error("getWithReceiptNumber : {$value->receipt_number} - {$match->plate_number}");
                        if (!empty($invoiceService)) {
                            Log::error("getWithReceiptNumber DONED : {$value->receipt_number} - {$match->plate_number}");
                            break;
                        }
                        $attempts++;
                        Log::error("getWithReceiptNumber not find : {$value->receipt_number} - {$match->plate_number}");
                        sleep(1);
                    }

                    if ($invoiceService && isset($invoiceService[0])) {

                        $invoiceService = $invoiceService[0];
                        if ($invoiceService && !isset($invoiceService['InvoiceNumber'])) continue;

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
                        Log::error("DONED : {$value->receipt_number} - {$value->id}");
                        break;
                    }

                    sleep(1);
                }
                // $match = OcrMatch::with(["bijacs" => function ($query) {
                //     $query->with('invoices');
                // }])->find($log);
            } catch (\Throwable $e) {
                Log::error("❌ [TruckStatusJob] Error during noInvoice for receipt_number = " . $e->getMessage());
            }
            // return $match;
        }
    }
}
