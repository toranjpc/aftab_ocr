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
            $params = request('params');
            $ocrMatch = OcrMatch::with('bijacs')->find(request('id'));

            if (isset($params['bijac_number'])) {
                $bijstatus = '';
                $bijac = Bijac::selectRaw("id , container_number , plate_normal , plate_normal as plate_number , bijac_date , receipt_number")->where($params)->get();
                if ($bijac->isEmpty()) $bijstatus = '_Creq ';

                if ($bijac->isEmpty()) $bijac = $this->bijakrequest($params);
                if ($bijac->isEmpty()) $bijac = $this->bijakrequest($params, "ccs");
                if ($bijac->isEmpty()) return ['message' => 'not found'];

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
            }
            if (isset($receipt_number) or isset($params['receipt_number'])) {
                // if ($ocrMatch->bijac_has_invoice)  return ['message' => 'bijac_has_invoice'];
                if (isset($params['receipt_number'])) $receipt_number = $params['receipt_number'];

                $QU = Invoice::select("*")
                    ->where('receipt_number', $receipt_number)
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
                            // $DBfactor = clone $QU;
                            // $DBfactor = $DBfactor->get();
                        }
                    }
                }
            }

            /*
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $ocrMatch->id],
                'event' => 'ocr-match',
                'model' => OcrMatch::class,
                'receiver_id' => $ocrMatch->gate_number,
            ]);
            */

            TruckStatusJob::dispatch($ocrMatch->id, 'plate');
            if (!!$ocrMatch->container_code_image_url) {
                TruckStatusJob::dispatch($ocrMatch->id, 'container');
            }

            return ['message' => 'success'];
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    private function bijakrequest($params, $type = "Gcoms")
    {
        $bijac = [];
        if (isset($params['bijac_number'])) {
            if ($type == "Gcoms") {
                $GcomsService = new GcomsService();
                $DBBijac = $GcomsService->getBijacTaki($params['bijac_number']);
                if (!empty($DBBijac) && isset($DBBijac['Travel'])) {
                    $bijac = Bijac::create([
                        "source_bijac_id" => time(),
                        "plate" => $DBBijac['Travel'],
                        "plate_normal" => $DBBijac['Travel'],
                        "dangerous_code" => ($DBBijac['Dangerous'] == 'true') ? 1 : 0,
                        "receipt_number" => "BSRGCBI" . $DBBijac['StoreReceiptSerial'],
                        "bijac_number" => $DBBijac['GatePassSerial'],
                        "gross_weight" => $DBBijac['GrossWeight'],
                        "is_single_carry" => 0,
                        "pack_number" => $DBBijac['PackNB'],
                        "bijac_date" => $DBBijac['GatePassDate'],
                        "vehicles_type" => $DBBijac['VehiclesTypeDec'],
                        "type" => "gcoms",
                    ]);
                    $AuthController = new AuthController();
                    $AuthController->savelog($bijac);
                }
                $bijac = collect([$bijac]);
            } else if ($type == 'ccs') {
                $CcsService = new CcsService();
                $bijac = $CcsService->getByReceipt($params['bijac_number']);
                if (!empty($bijac)) {
                    $bijacs = [];
                    $time = time();
                    foreach ($bijac as $key => $value) {
                        if (!isset($value['VehicleNumber'])) continue;
                        $bijac_ =    Bijac::create([
                            "source_bijac_id" => $time + $key,
                            "plate" => $value['VehicleNumber'],
                            "plate_normal" => $value['VehicleNumber'],
                            "dangerous_code" => ($value['HazardousCode'] == 'true') ? 1 : 0,
                            "receipt_number" => $value['ReceiptNumber'],
                            "bijac_number" => $value['ExitPermissionNumber'],
                            "gross_weight" => $value['Weight'],
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
                    $bijac = collect($bijacs);
                }
            }
        }
        return $bijac;
    }
}
