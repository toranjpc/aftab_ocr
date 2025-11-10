<?php

namespace Modules\Ocr\Controller;

// use App\Models\Log;
use Illuminate\Support\Facades\Log;

use Modules\BijacInvoice\Models\Bijac;
use Modules\Collector\Services\GcomsService;
use Modules\Ocr\Jobs\TruckStatusJob;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\Models\TruckLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ocr\TruckMatcher;
use Modules\BijacInvoice\BijacMatcher;

class GateController extends Controller
{
    public function checkBy()
    {
        $params = request('params');

        $ocrMatch = OcrMatch::find(request('id'));

        if (isset($params['receipt_number'])) {
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
