<?php

namespace Modules\Ocr\Controller;

// use App\Models\Log;
use Illuminate\Support\Facades\Log;

use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\Invoice;
use Modules\Collector\Services\GcomsService;
use Modules\Collector\Services\CcsService;
use Modules\Ocr\Jobs\TruckStatusJob;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\Models\TruckLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ocr\TruckMatcher;
use Modules\BijacInvoice\BijacMatcher;
use Modules\BijacInvoice\Services\InvoiceService;
use Modules\Ocr\Controller\OcrLogController;
use SimpleXMLElement;
use Modules\Sse\Models\SSE;
use Modules\Auth\Controllers\AuthController;

class GateController extends Controller
{
    public function checkBy()
    {
        try {
            //    return request();
            $bijstatus = '';
            $params = request('params');
            $ocrMatch = OcrMatch::with('bijacs')->find(request('id'));

            if (isset($params['bijac_number'])) {
                $AuthController = new AuthController();
                $AuthController->savelog($ocrMatch, "serachBijac", "جست و جو برای بیجک");

                $bijac = Bijac::selectRaw("id , container_number , plate_normal , plate_normal as plate_number , bijac_date , receipt_number")->where($params)->get();
                if ($bijac->isEmpty()) $bijstatus = '_Creq ';

                if ($bijac->isEmpty()) {
                    $bijac = $this->bijakrequest($params);
                }
                if (empty($bijac->first())) {
                    $bijac = $this->bijakrequest($params, "ccs");
                }
                if (empty($bijac->first())) {
                    SSE::create([
                        // 'message' => ['data' => $item->toArray()],
                        'message' => ['data' => $ocrMatch->id],
                        'event' => 'ocr-match',
                        'model' => OcrMatch::class,
                        'receiver_id' => $ocrMatch->gate_number,
                    ]);
                    return ['message' => 'not found'];
                }

                $controller = new OcrLogController();
                $check = $controller->checkPlateIsDuplicate(
                    $ocrMatch->plate_number,
                    $ocrMatch->gate_number,
                    $bijac
                );
                // if (!$check) {
                //     $controller = new OcrLogController();
                //     $check = $controller->checkIsDuplicateContainer(
                //         $ocrMatch->container_code,
                //         $ocrMatch->gate_number,
                //         $bijac
                //     );
                // } 
                if (!$check) {
                    $bijstatus = '_Creq';
                    $check = $bijac->first();
                    // if (isset($baseBij)) $check = $baseBij;
                }
                // $threeDaysAgo = now()->subDays(3);
                // $bijacDate = \Carbon\Carbon::parse($check->bijac_date);
                // if (!$bijacDate->greaterThanOrEqualTo($threeDaysAgo)) {
                //     $bijstatus = '_Creq';
                //     // return ['message' => 'not found'];
                // }


                if (isset($check->plate_normal)) {
                    $ocrMatch->bijacs()->sync([$check->id]);
                    $ocrMatch->plate_number_3 = $check->plate_normal;
                    $ocrMatch->container_code_3 = $check->container_number;
                    $ocrMatch->match_status =  $ocrMatch->match_status . $bijstatus;
                    $ocrMatch->saveQuietly();

                    $AuthController = new AuthController();
                    $AuthController->savelog($ocrMatch, "mache", "ثبت بیجک دستی");
                }
                $receipt_number = $check->receipt_number;


                TruckStatusJob::dispatch($ocrMatch->id, 'plate');
                if (!!$ocrMatch->container_code_image_url) {
                    TruckStatusJob::dispatch($ocrMatch->id, 'container');
                }
            }
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $ocrMatch->id],
                'event' => 'ocr-match',
                'model' => OcrMatch::class,
                'receiver_id' => $ocrMatch->gate_number,
            ]);

            if (isset($receipt_number) or isset($params['receipt_number'])) {
                if (isset($params['receipt_number'])) {
                    $AuthController = new AuthController();
                    $AuthController->savelog($ocrMatch, "serachInvoice", "جست و جو برای فاکتور");

                    $receipt_number = $params['receipt_number'];

                    $ocrMatch = OcrMatch::with('bijacs')->find(request('id'));
                    if (!isset($ocrMatch->id)) {
                        return ['message' => 'err'];
                    } else if ($ocrMatch->bijacs->isNotEmpty()) {
                        $result = $ocrMatch->bijacs->filter(function ($res) use ($receipt_number) {
                            if ($res->receipt_number == $receipt_number) return true;
                        });
                        if ($result->isEmpty()) {
                            SSE::create([
                                // 'message' => ['data' => $item->toArray()],
                                'message' => ['data' => $ocrMatch->id],
                                'event' => 'ocr-match',
                                'model' => OcrMatch::class,
                                'receiver_id' => $ocrMatch->gate_number,
                            ]);
                            return ['message' => 'receipt_number is wrong'];
                        }
                    }
                }

                // if ($ocrMatch->bijac_has_invoice)  return ['message' => 'bijac_has_invoice'];
                $QU = Invoice::select("*")
                    ->where(function ($q) {
                        $q->where("invoice_number", "NOT LIKE", "AFTAB_CE%")
                            ->where("receipt_number", "NOT LIKE", "AFTAB_CE%");
                    })
                    ->where('receipt_number', "LIKE", $receipt_number)
                    ->with(["bijacs" => function ($q) {
                        $q->with("ocrMatches");
                    }]);
                $DBfactor = clone $QU;
                $DBfactor = $DBfactor->get();

                if ($DBfactor->isEmpty()) {
                    $InvoiceService = new InvoiceService();
                    $APIfactor = $InvoiceService->getWithReceiptNumber($receipt_number);
                    if ($APIfactor && isset($APIfactor[0])) {
                        $APIfactor = $APIfactor[0];
                        if (isset($APIfactor['InvoiceNumber'])) {
                            $saveinvoice = new InvoiceService();
                            $saveinvoice->save_invoice($APIfactor);

                            $DBfactor = clone $QU;
                            $DBfactor = $DBfactor->get();
                        }
                    }
                }
                if (!$DBfactor->isEmpty()) {
                    $bijacs = Bijac::where('receipt_number', $receipt_number)->get();

                    $bijacIds = $bijacs->pluck('id');
                    $bijac    = $bijacs->first();

                    if ($bijacIds->isEmpty()) {
                        $bijac = Bijac::create([
                            "source_bijac_id" => time() . rand(0, 9999),
                            "plate" => $ocrMatch->plate_number,
                            "plate_normal" => $ocrMatch->plate_number,
                            "dangerous_code" => 0,
                            "receipt_number" => $receipt_number,
                            // "bijac_number" => "aftabB-" . preg_replace('/\D/', '', $receipt_number),
                            "bijac_number" => jdate()->format("ydmHis"),
                            "gross_weight" => 0,
                            "bijac_date" => now(),
                            "container_size" =>  $ocrMatch->container_size,
                            "container_number" => $ocrMatch->container_code,
                            "is_single_carry" => 0,
                            "exit_permission_iD" => 0,
                            "type" => "aftab",
                        ]);

                        $bijacIds = [$bijac->id];
                    } else {
                        $bijacIds = $bijacIds->toArray();
                    }

                    $bijstatus = '_Creq ';
                    $ocrMatch->bijacs()->sync($bijacIds);
                    $ocrMatch->plate_number_3 = $bijac->plate_normal;
                    $ocrMatch->container_code_3 = str_replace(' ', '', $bijac->container_number);
                    $ocrMatch->match_status =  $ocrMatch->match_status . $bijstatus;
                    $ocrMatch->saveQuietly();
                } else {

                    SSE::create([
                        // 'message' => ['data' => $item->toArray()],
                        'message' => ['data' => $ocrMatch->id],
                        'event' => 'ocr-match',
                        'model' => OcrMatch::class,
                        'receiver_id' => $ocrMatch->gate_number,
                    ]);
                    return ['message' => 'not found'];
                }


                TruckStatusJob::dispatch($ocrMatch->id, 'plate');
                if (!!$ocrMatch->container_code_image_url) {
                    TruckStatusJob::dispatch($ocrMatch->id, 'container');
                }
            }

            //BSRCC14040177207
            // 1404021755
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $ocrMatch->id],
                'event' => 'ocr-match',
                'model' => OcrMatch::class,
                'receiver_id' => $ocrMatch->gate_number,
            ]);


            return ['message' => 'success'];
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    private function bijakrequest($params, $type = "Gcoms")
    {
        $bijac = [];
        try {
            if (isset($params['bijac_number'])) {
                if ($type == "Gcoms") {
                    $GcomsService = new GcomsService();
                    $DBBijac = $GcomsService->getBijacTaki($params['bijac_number']);
                    if (!empty($DBBijac) && isset($DBBijac['Travel'])) {
                        $bijac_1 = Bijac::create([
                            "source_bijac_id" => time(),
                            "plate" => $DBBijac['Travel'],
                            "plate_normal" => $DBBijac['Travel'],
                            "dangerous_code" => ($DBBijac['Dangerous'] == 'true') ? 1 : 0,
                            "receipt_number" => "BSRGCBI" . $DBBijac['StoreReceiptSerial'],
                            "bijac_number" => $DBBijac['GatePassSerial'],
                            "gross_weight" => $DBBijac['GrossWeight'],
                            "is_single_carry" => 0,
                            // "bijac_date" => now(),
                            "pack_number" => $DBBijac['PackNB'],
                            "bijac_date" => $DBBijac['GatePassDate'],
                            "vehicles_type" => $DBBijac['VehiclesTypeDec'],
                            "type" => "gcoms",
                        ]);
                        $AuthController = new AuthController();
                        $AuthController->savelog($bijac_1);
                        $bijac = [$bijac_1];
                    }
                } else if ($type == 'ccs') {
                    $CcsService = new CcsService();
                    $bijac_ = $CcsService->getByReceipt($params['bijac_number']);
                    if (!empty($bijac_)) {
                        $bijacs = [];
                        $time = time();
                        foreach ($bijac_ as $key => $value) {
                            if (!isset($value['VehicleNumber'])) continue;
                            $bijac_ = Bijac::create([
                                "source_bijac_id" => $time + $key,
                                "plate" => $value['VehicleNumber'],
                                "plate_normal" => $value['VehicleNumber'],
                                "dangerous_code" => ($value['HazardousCode'] == 'true') ? 1 : 0,
                                "receipt_number" => $value['ReceiptNumber'],
                                "bijac_number" => $value['ExitPermissionNumber'],
                                "gross_weight" => $value['Weight'],
                                "bijac_date" => now(),
                                "container_size" => $value['ContainerSize'],
                                "container_number" => $value['ContainerNumber'],
                                "is_single_carry" => ($value['IsSingleCarry'] == 'true') ? 1 : 0,
                                "exit_permission_iD" => $value['ExitPermissionID'],
                                "type" => "ccs",

                                // "bijac_date" => $DBBijac['GatePassDate'],
                            ]);
                            $AuthController = new AuthController();
                            $AuthController->savelog($bijac_);
                            $bijacs[] = $bijac_;
                        }
                        $bijac = $bijacs;
                    }
                }
            }
            return collect($bijac);
            //code...
        } catch (\Throwable $th) {
            return collect($bijac);
            throw $th;
        }
    }

    public function findAftabInvoice()
    {
        $invoicenumber = preg_replace('/\D/', '', request('data', 0));
        if (strlen($invoicenumber) < 4) return ['status' => 'error', 'reload' => 1, 'message' => 'شماره وارد شده اشتباه است'];
        $OcrMatch = OcrMatch::select('id')->with(['bijacs' => function ($q) {
            $q->select('id');
        }])->find(request('selectedTruckBase', 0));
        if (!isset($OcrMatch->id) || $OcrMatch->bijacs->isNotEmpty()) return ['status' => 'error', 'reload' => 1, 'message' => 'برای این پلاک قبلا بیجک ثبت شده است'];

        $customTariff = config('ocr.custom_tariff');
        $Invoice = Invoice::select("id", "amount", "receipt_number")
            ->where("invoice_number", "LIKE", "AFTAB_CE%" . $invoicenumber)
            ->with("bijacs")
            ->first();
        if (!isset($Invoice->id)) return ['status' => 'error', 'message' => 'فاکتور یافت نشد'];

        $Invoice->Tariff = $customTariff;

        return $Invoice;
    }
    public function addbijac()
    {
        $OcrMatch = OcrMatch::with('bijacs')->find(request('selectedTruckBase'));
        if (!$OcrMatch || $OcrMatch->bijacs->isNotEmpty()) return ['status' => 'error', 'message' => 'قبلا ثبت شده است'];
        $Invoice = Invoice::find(request('id'));
        if (!$Invoice)  return ['status' => 'error', 'message' => 'فاکتور یافت نشد'];

        $DATA = [
            "source_bijac_id" => time(),
            "plate" => $OcrMatch->plate_number,
            "plate_normal" => $OcrMatch->plate_number,
            "receipt_number" => $Invoice->receipt_number,
            "container_size" => request('tu', 1),
            "bijac_date" => now(),
            "bijac_number" => $Invoice->receipt_number,
            "type" => "aftab",
        ];
        $Bijac = Bijac::create($DATA);
        $OcrMatch->bijacs()->attach($Bijac->id);

        TruckStatusJob::dispatch($OcrMatch->id, 'plate');
        if (!!$OcrMatch->container_code_image_url) {
            TruckStatusJob::dispatch($OcrMatch->id, 'container');
        }

        $Invoice = $Invoice->fresh(['bijacs']);
        $customTariff = config('ocr.custom_tariff');
        $Invoice->Tariff = $customTariff;

        return ['status' => 'success', 'message' => 'با موفقیت ثبت شد', "DATA" => $Invoice];
    }
}
