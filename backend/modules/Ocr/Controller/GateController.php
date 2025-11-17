<?php

namespace Modules\Ocr\Controller;

// use App\Models\Log;
use Illuminate\Support\Facades\Log;

use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\Invoice;
use Modules\Collector\Services\GcomsService;
use Modules\Ocr\Jobs\TruckStatusJob;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\Models\TruckLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ocr\TruckMatcher;
use Modules\BijacInvoice\BijacMatcher;
use Modules\BijacInvoice\Services\InvoiceService;
use SimpleXMLElement;

class GateController extends Controller
{
    public function checkBy()
    {
        $params = request('params');
        $ocrMatch = OcrMatch::with('bijacs')->find(request('id'));

        if (isset($params['receipt_number'])) {
            $receipt_number = $params['receipt_number'];

            if (!$ocrMatch->bijac_has_invoice) {
                if ($ocrMatch->bijacs->isEmpty()) {

                    $GcomsService = new GcomsService();
                    return $DBBijac = $GcomsService->getBijacTaki($receipt_number);


                    // $Bijac = Bijac::create([
                    //     "source_bijac_id" => time(),
                    //     "plate" => $ocrMatch->plate_number,
                    //     "plate_normal" => $ocrMatch->plate_number,
                    //     "receipt_number" => $receipt_number,
                    //     "gross_weight" => '',
                    //     "pack_number" => '',
                    //     "container_number" => $ocrMatch->container_code,
                    //     "container_size" => '',
                    //     "bijac_number" => time(),
                    //     "bijac_date" => now(),
                    // ]);
                    // $ocrMatch->bijacs()->sync($Bijac->id);
                    // $ocrMatch->load('bijacs.invoice');
                }

                return $ocrMatch->bijacs;
            }



            return "no";

            $DBfactor = Invoice::select("*")
                ->where('receipt_number', $receipt_number)
                ->with(["bijacs" => function ($q) {
                    $q->with("ocrMatches");
                }])
                ->get();
            if ($DBfactor->isEmpty()) {
                $InvoiceService = new InvoiceService();
                return  $InvoiceService->getWithReceiptNumber($receipt_number);
            }
            return  $DBfactor;
            foreach ($DBfactor as $value) {
            }


            $data = GcomsService::getBijacTaki($params['receipt_number']);
            log::info('GcomsService : ' . json_encode($data));
            return;
            if (!!$data) {
                TruckMatcher::bijacMatching($ocrMatch);
                return [$data];
            }
        }

        if (isset($params['bijac_number'])) {
            $bijac = Bijac::where($params)->first();

            if ($bijac) {
                $ocrMatch->bijacs()->sync([$bijac->id]);
                $ocrMatch->plate_number = $bijac->plate_normal;
                $ocrMatch->container_code = $bijac->container_number;
                $ocrMatch->saveQuietly();
                return [$bijac];
            }
        }

        // BijacMatcher::bijacMatching($ocrMatch);
        TruckStatusJob::dispatch($ocrMatch, 'plate');
        if (!!$ocrMatch->container_code_image_url) {
            TruckStatusJob::dispatch($ocrMatch, 'container');
        }

        return ['message' => 'not found'];
    }
}
