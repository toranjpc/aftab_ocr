<?php

namespace Modules\Ocr\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ocr\Models\OcrLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Ocr\Models\TruckLog;
use Modules\Ocr\OcrBuffer;
use Modules\Ocr\Jobs\ProcessOcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\Requests\OcrLogRequest;
use App\Models\Log as reqlog;

use Modules\Traffic\Controller\TrafficController;
use Modules\Traffic\Requests\TrafficRequest;

class OcrLogController extends Controller
{
    public function store(OcrLogRequest $request)
    {
        // try {
        //     $trafficController = app(TrafficController::class);
        //     $base = Request::create('/', 'POST', $request->all());
        //     $trafficRequest = TrafficRequest::createFromBase($base);
        //     $trafficRequest->setContainer(app())->validateResolved();
        //     $trafficController->store($trafficRequest);
        // } catch (\Throwable $th) {
        //     log::build(['driver' => 'single', 'path' => storage_path("logs/TrafficMatch"),])
        //         ->info("TrafficController error ");
        // }

        /**********************/



        $plate = false;
        if (isset($request->plate_number)) {
            $plate = $this->checkPlateIsDuplicate([
                $request->plate_number,
                $request->gate_number,
            ]);

            if ($plate) {
                if ($plate->ocr_accuracy >= $request->ocr_accuracy) {

                    OcrLog::where('id', $plate->id)->update([
                        'plate_number_2' => $request->plate_number,
                    ]);

                    return ['id1' => $plate->id];
                }
            }
        }

        $container = false;
        if (isset($request->container_code)) {


            $container = $this->checkIsDuplicateContainer([
                $request->container_code,
                $request->gate_number,
            ]);

            if ($container) {
                if ($container->ocr_accuracy >= $request->ocr_accuracy) {
                    OcrLog::where('id', $container->id)->update([
                        'container_code_2' => $request->container_code,
                    ]);

                    return ['id2' => $container->id];
                }
            }
            // Log::error("request from AI : " . json_encode($request->vehicle_image_back));
        }

        $validated = $request->safe()
            ->only([
                'direction',
                'plate_type',
                'plate_number',
                'plate_number_2',
                'camera_number',
                'gate_number',
                'log_time',
                'container_code',
                'container_code_2',
                'ocr_accuracy',
                'IMDG',
                'seal',
            ]);

        $imageFields = ['vehicle_image_front', 'vehicle_image_back', 'vehicle_image_left', 'vehicle_image_right', 'plate_image', 'container_code_image'];

        $imageUrls = [];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $imageUrls[$field . '_url'] = $this->storeFile($request->file($field))["link"];
            }
        }

        $data = array_merge($validated, $imageUrls);

        if ($plate) {
            OcrLog::where('id', $plate->id)
                ->update([
                    ...$data,
                    'plate_number_2' => $plate->plate_number
                ]);

            return ['id3' => $plate->id];
        } elseif ($container) {
            OcrLog::where('id', $container->id)
                ->update([
                    ...$data,
                    'container_code_2' => $container->container_code
                ]);

            return ['id4' => $container->id];
        } else {
            $ocrLog = OcrLog::create($data);

            OcrBuffer::addToBuffer($ocrLog, $request->gate_number, isset($request->plate_number) ? 'plate' : 'container');

            ProcessOcrLog::dispatch(
                $ocrLog->id
            );
            return ['id5' => $ocrLog->id];
        }


        // reqlog::create([
        //     "table_name" => "ocr_logs",
        //     "table_id" => $ocrLog->id,
        //     "log_type" => "ai",
        //     "data" => $request,
        //     "log_date" => now(),
        // ]);

    }

    public function store2(Request $request)
    {
        return TruckLog::create($request->all());
    }

    private function checkPlateIsDuplicate($data)
    {
        [$input, $gate] = $data;
        $threshold = config('ocr.field_thresholds.plate_number', 1);

        $lastSixPlate = OcrBuffer::getBuffer($gate);

        $closest = false;

        if ($input) {
            foreach ($lastSixPlate as $key => $plate) {
                if ($plate->plate_number) {

                    $lev = levenshtein($input, $plate->plate_number);

                    // if ($lev == 0) return $plate;

                    if ($lev < $threshold) {
                        return $plate;
                    }

                    if (
                        $key < 3 &&
                        strlen($input) > 5 &&
                        strlen($plate->plate_number) > 5 &&
                        (
                            str_contains($input, $plate->plate_number) ||
                            str_contains($plate->plate_number, $input)
                        )
                    ) {
                        $closest = $plate;
                    }
                }
            }
        }
        return $closest;
    }

    private function checkIsDuplicateContainer($data)
    {
        function extractDigits($string)
        {
            preg_match_all('/\d+/', $string, $matches);
            return implode('', $matches[0]);
        }
        [$input, $gate] = $data;
        $lastSix = OcrBuffer::getBuffer($gate, 'container');
        $threshold = config('ocr.field_thresholds.container_code', config('ocr.levenshtein_threshold'));

        $closest = false;

        if ($input)
            foreach ($lastSix as $container) {
                if ($container->container_code) {
                    $lev = levenshtein(
                        substr(extractDigits($input), 0, 6),
                        substr(extractDigits($container->container_code), 0, 6)
                    );

                    if ($lev == 0)
                        return $container;

                    if ($lev < $threshold) {
                        $closest = $container;
                    }
                }
            }

        return $closest;
    }

    public function storeFile($file, $type = "img", $gate = '')
    {
        if (!isset($file)) {
            return ['message' => 'فایل ارسال نشده. مجدد تلاش کنید', "statuscode" => Response::HTTP_BAD_REQUEST, 'link' => null];
        }

        $fileName = uniqid() . '.' . $file->extension();

        $savePath = 'uploaded/' . $type . '/' . $gate . $fileName;

        $file->move(public_path('uploaded/' . $type . '/'), $fileName);

        return ['message' => 'ذخیره شد', "statuscode" => Response::HTTP_OK, 'link' => $savePath];
    }
}
