<?php

namespace Modules\Traffic\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Traffic\Models\Traffic;
use Modules\Traffic\TrafficBuffer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Traffic\Requests\TrafficRequest;


class TrafficController extends Controller
{
    const MATCH_DAY = 3600 * 3;

    public function store(TrafficRequest $request)
    {
        $redis = app('redis');
        $key   = 'Traffic_queue';
        $all = $redis->lrange($key, 0, -1);
        // Log::error("stored id cache : " . json_encode($all));


        $plate = false;
        $container = false;
        if (isset($request->plate_number)) {
            $plate = $this->checkPlateIsDuplicate([
                $request->plate_number,
                $request->gate_number,
            ]);

            if ($plate) {
                if ($plate->ocr_accuracy >= $request->ocr_accuracy) {

                    Traffic::where('id', $plate->id)->update([
                        'plate_number_2' => $request->plate_number,
                    ]);

                    return ['id1' => $plate->id];
                }
            }
        } elseif (isset($request->container_code)) {
            $container = $this->checkDuplicateIsContainer([
                $request->container_code,
                $request->gate_number,
            ]);

            if ($container) {
                if ($container->ocr_accuracy >= $request->ocr_accuracy) {
                    Traffic::where('id', $container->id)->update([
                        'container_code_2' => $request->container_code,
                    ]);

                    return ['id2' => $container->id];
                }
            }
            // Log::error("request from AI : " . json_encode($request->vehicle_image_back));
        }

        return;
        $plate = false;
        if (isset($request->plate_number)) {
            $plate = $this->checkPlateIsDuplicate([
                $request->plate_number,
                $request->gate_number,
            ]);

            if ($plate) {
                if ($plate->ocr_accuracy >= $request->ocr_accuracy) {

                    Traffic::where('id', $plate->id)->update([
                        'plate_number_2' => $request->plate_number,
                    ]);

                    return ['id1' => $plate->id];
                }
            }
        }

        $container = false;
        if (isset($request->container_code)) {


            $container = $this->checkDuplicateIsContainer([
                $request->container_code,
                $request->gate_number,
            ]);

            if ($container) {
                if ($container->ocr_accuracy >= $request->ocr_accuracy) {
                    Traffic::where('id', $container->id)->update([
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
            Traffic::where('id', $plate->id)
                ->update([
                    ...$data,
                    'plate_number_2' => $plate->plate_number
                ]);

            return ['id3' => $plate->id];
        } elseif ($container) {
            Traffic::where('id', $container->id)
                ->update([
                    ...$data,
                    'container_code_2' => $container->container_code
                ]);

            return ['id4' => $container->id];
        } else {
            $traffic = Traffic::create($data);

            TrafficBuffer::addToBuffer($traffic, $request->gate_number, isset($request->plate_number) ? 'plate' : 'container');

            ProcessTraffic::dispatch(
                $traffic->id
            );
            return ['id5' => $traffic->id];
        }


        // reqlog::create([
        //     "table_name" => "ocr_logs",
        //     "table_id" => $traffic->id,
        //     "log_type" => "ai",
        //     "data" => $request,
        //     "log_date" => now(),
        // ]);

    }


    private function checkPlateIsDuplicate($data)
    {
        [$input, $gate] = $data;
        $threshold = config('ocr.field_thresholds.plate_number', config('ocr.levenshtein_threshold'));

        $lastSixPlate = TrafficBuffer::getBuffer($gate);

        $closest = false;

        if ($input) {
            foreach ($lastSixPlate as $key => $plate) {
                if ($plate->plate_number) {

                    $lev = levenshtein($input, $plate->plate_number);

                    if ($lev == 0) return $plate;

                    if ($lev < $threshold) {
                        $closest = $plate;
                    }

                    if (
                        $key < 3 &&
                        strlen($input) > $threshold &&
                        strlen($plate->plate_number) > $threshold &&
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

    private function checkDuplicateIsContainer($data)
    {
        function extractDigits($string)
        {
            preg_match_all('/\d+/', $string, $matches);
            return implode('', $matches[0]);
        }
        [$input, $gate] = $data;
        $lastSix = TrafficBuffer::getBuffer($gate, 'container');
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

    public function storeFile($file, $type = "img")
    {
        if (!isset($file)) {
            return ['message' => 'فایل ارسال نشده. مجدد تلاش کنید', "statuscode" => Response::HTTP_BAD_REQUEST, 'link' => null];
        }

        $fileName = uniqid() . '.' . $file->extension();

        $savePath = 'uploaded/' . $type . '/' . $fileName;

        $file->move(public_path('uploaded/' . $type . '/'), $fileName);

        return ['message' => 'ذخیره شد', "statuscode" => Response::HTTP_OK, 'link' => $savePath];
    }
}
