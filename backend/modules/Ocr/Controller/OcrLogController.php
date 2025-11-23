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
use Modules\Auth\Controllers\AuthController;

class OcrLogController extends Controller
{
    public function store(OcrLogRequest $request)
    {
        // if ($request->gate_number != 3) return;
        try {
            if (isset($request->plate_number)) {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                    ->info("OcrLogController (data recived) by palte : {$request->plate_number}  ");
            } elseif (isset($request->container_code)) {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                    ->info("OcrLogController (data recived) by palte : {$request->container_code}  ");
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

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
            $plate = $this->checkPlateIsDuplicate(
                $request->plate_number,
                $request->gate_number,
            );

            if ($plate) {
                if ($plate->ocr_accuracy >= $request->ocr_accuracy) {

                    OcrLog::where('id', $plate->id)->where('gate_number', $request->gate_number)->update([
                        'plate_number_2' => $request->plate_number,
                    ]);

                    try {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                            ->info("OcrLogController (DUPLICATE DATA) by palte : {$request->plate_number}  ");
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    return ['id1' => $plate->id ?? null, "status" => "duplic", "type" => "plate"];
                }
            }
        }

        $container = false;
        if (isset($request->container_code)) {

            $container = $this->checkIsDuplicateContainer(
                $request->container_code,
                $request->gate_number,
            );

            if ($container) {
                if ($container->ocr_accuracy >= $request->ocr_accuracy) {
                    OcrLog::where('id', $container->id)->where('gate_number', $request->gate_number)->update([
                        'container_code_2' => $request->container_code,
                    ]);

                    try {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                            ->info("OcrLogController (DUPLICATE DATA) by container : {$request->container_code}  ");
                    } catch (\Throwable $th) {
                        //throw $th;
                    }


                    return ['id2' => $container->id ?? null, "status" => "duplic", "type" => "container"];
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
        if (empty($validated->log_time)) {
            $validated['log_time'] = now();
        }
        // return $validated;


        $imageFields = ['vehicle_image_front', 'vehicle_image_back', 'vehicle_image_left', 'vehicle_image_right', 'plate_image', 'container_code_image'];

        $imageUrls = [];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $imageUrls[$field . '_url'] = $this->storeFile($request->file($field), $request->gate_number)["link"];
            }
        }

        $data = array_merge($validated, $imageUrls);

        if ($plate) {
            OcrLog::where('id', $plate->id)
                ->where('gate_number', $request->gate_number)
                ->update([
                    ...$data,
                    'plate_number_2' => $plate->plate_number
                ]);

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                    ->info("OcrLogController (DUPLICATE DATA by update traffic) by palte : {$request->plate_number}  ");
            } catch (\Throwable $th) {
                //throw $th;
            }

            return ['id3' => $plate->id ?? null, "status" => "duplic", "type" => "plate"];
        } elseif ($container) {
            OcrLog::where('id', $container->id)
                ->where('gate_number', $request->gate_number)
                ->update([
                    ...$data,
                    'container_code_2' => $container->container_code
                ]);

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                    ->info("OcrLogController (DUPLICATE DATA by update traffic) by container : {$request->container_code}  ");
            } catch (\Throwable $th) {
                //throw $th;
            }

            return ['id4' => $container->id ?? null, "status" => "duplic", "type" => "container"];
        } else {
            $ocrLog = OcrLog::create($data);

            OcrBuffer::addToBuffer($ocrLog, $request->gate_number, isset($request->plate_number) ? 'plate' : 'container');

            ProcessOcrLog::dispatch(
                $ocrLog->id
            );

            if (!empty($request->fromFront)) {
                $AuthController = new AuthController();
                $AuthController->savelog($ocrLog, "OcrLog", "ثبت پلاک از نگهبانی");
            }

            try {
                // if ($ocrLog->gate_number == 3) {
                try {
                    if (isset($request->plate_number)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                            ->info("OcrLogController ({$ocrLog->id}) created from palte : {$request->plate_number}  ");
                    } elseif (isset($request->container_code)) {
                        log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $request->gate_number . ".log"),])
                            ->info("OcrLogController ({$ocrLog->id}) created from continer : {$request->container_code}  ");
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                // }
            } catch (\Throwable $th) {
                //throw $th;
            }

            return ['id5' => $ocrLog->id ?? null, "status" => "success"];
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

    public function checkPlateIsDuplicate($input, $gate, $lastSixPlate = null)
    {
        // [$input, $gate] = $data;
        $threshold = config('ocr.field_thresholds.plate_number', 1);

        if (!$lastSixPlate) $lastSixPlate = OcrBuffer::getBuffer($gate);

        $closest = false;
        if ($input) {
            foreach ($lastSixPlate as $key => $plate) {
                if ($plate->plate_number) {
                    $lev = levenshtein($input, $plate->plate_number);
                    // if ($lev == 0) return $plate;
                    if ($lev < $threshold) {

                        try {
                            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $gate . ".log"),])
                                ->info("checkPlateIsDuplicate : {$input} - {$plate->plate_number}  ");
                        } catch (\Throwable $th) {
                            //throw $th;
                        }


                        return $plate;
                    }

                    if (
                        $key < 4
                        //  &&
                        // strlen($input) >= 4 &&
                        // strlen($plate->plate_number) >= 4
                    ) {
                        $input_substr = $input;
                        $plate_number_substr = $plate->plate_number;
                        // $input_substr = substr($input, 0, 4);
                        // $plate_number_substr = substr($plate->plate_number, 0, 4);
                        if (
                            str_contains($input_substr, $plate_number_substr) ||
                            str_contains($plate_number_substr, $input_substr)
                        ) {

                            try {
                                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $gate . ".log"),])
                                    ->info("checkPlateIsDuplicate : {$input} - {$plate->plate_number}  ");
                            } catch (\Throwable $th) {
                                //throw $th;
                            }


                            return $plate;
                            $closest = $plate;
                        }

                        $input_substr = preg_replace('/\D/', '', $input);
                        $plate_number_substr = preg_replace('/\D/', '', $plate->plate_number);
                        // $input_substr = substr(preg_replace('/\D/', '', $input), 0, 4);
                        // $plate_number_substr = substr(preg_replace('/\D/', '', $plate->plate_number), 0, 4);
                        if (
                            str_contains($input_substr, $plate_number_substr) ||
                            str_contains($plate_number_substr, $input_substr)
                        ) {

                            try {
                                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $gate . ".log"),])
                                    ->info("checkPlateIsDuplicate : {$input} - {$plate->plate_number}  ");
                            } catch (\Throwable $th) {
                                //throw $th;
                            }


                            return $plate;
                            $closest = $plate;
                        }

                        // $lev = levenshtein($input_substr, $plate_number_substr);
                        // if ($lev < $threshold) {
                        //     return $plate;
                        // }
                    }
                }
            }
        }
        return $closest;
    }

    public function checkIsDuplicateContainer($input, $gate, $lastSix = null)
    {
        function extractDigits($string)
        {
            preg_match_all('/\d+/', $string, $matches);
            return implode('', $matches[0]);
        }
        // [$input, $gate] = $data;
        if (!$lastSix) $lastSix = OcrBuffer::getBuffer($gate, 'container');
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

    public function storeFile($file, $gate = '', $type = "img")
    {
        if (!isset($file)) {
            return ['message' => 'فایل ارسال نشده. مجدد تلاش کنید', "statuscode" => Response::HTTP_BAD_REQUEST, 'link' => null];
        }

        $fileName = uniqid() . '.' . $file->extension();

        $savePath = 'uploaded/' . $type . '/' . $gate . "_" . $fileName;

        $file->move(public_path('uploaded/' . $type . '/'), $gate . "_" . $fileName);

        return ['message' => 'ذخیره شد', "statuscode" => Response::HTTP_OK, 'link' => $savePath];
    }
}
