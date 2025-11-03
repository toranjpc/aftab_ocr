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
    const CACHE_TIME = 3600 * 3;

    public function store(TrafficRequest $request)
    {
        $plate = false;
        $container = false;

        if (isset($request->plate_number)) {
            $plate = $this->checkPlateDuplicate($request->plate_number, $request->gate_number);
            if ($plate) Log::info("Traffic : duplicate plate finded : " . json_encode($plate));
            if ($plate && $plate->ocr_accuracy >= $request->ocr_accuracy) {
                Traffic::where('id', $plate->id)->update([
                    'plate_number_2' => $request->plate_number,
                ]);
                return ['id1' => $plate->id];
            }
        } elseif (isset($request->container_code)) {
            $container = $this->checkDuplicateContainer($request->container_code, $request->gate_number);
            if ($container) Log::info("Traffic : duplicate container finded : " . json_encode($container));
            if ($container && $container->ocr_accuracy >= $request->ocr_accuracy) {
                Traffic::where('id', $container->id)->update([
                    'container_code_2' => $request->container_code,
                ]);
                return ['id2' => $container->id];
            }
        }

        $validated = $request->safe()->only([
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
            return ['id5' => $traffic->id];
        }
    }
    private function checkPlateDuplicate($input, $gate, $noSubstring = 3)
    {
        if (!$input) return false;
        $threshold = config('ocr.field_thresholds.plate_number', config('ocr.levenshtein_threshold'));

        $redis = app('redis');
        $key   = 'Traffic_queue_' . $gate;
        $lastTraffic = $redis->lrange($key, 0, -1);
        $checklist = [
            "plate_number",
            "plate_number_edit",
            "plate_number_2",
            "plate_number_by_bijac"
        ];
        foreach ($lastTraffic as $k => $raw) {
            $row = json_decode($raw);
            if (!isset($row->data)) continue;
            $plate = $row->data;
            // if ($plate->plate_type !== $plate_type) continue;

            foreach ($checklist as $field) {
                $plate_number = $plate->{$field} ?? null;
                if (!$plate_number) continue;
                $lev = levenshtein($input, $plate_number);
                if ($lev <= $threshold) return $plate;
                elseif ($k < $noSubstring) {
                    if (str_contains($input, $plate_number) || str_contains($plate_number, $input)) {
                        return $plate;
                    }

                    if (strlen($input) >= 7 || strlen($plate_number) >= 7) {
                        $input_substr = substr($input, 0, 5);
                        $plate_number_substr = substr($plate_number, 0, 5);
                        if (str_contains($input_substr, $plate_number_substr) || str_contains($plate_number_substr, $input_substr)) {
                            return $plate;
                        }
                    }

                    $input_clean = $this->extractDigits($input);
                    $plate_number_clean = $this->extractDigits($plate_number);
                    if (str_contains($input_clean, $plate_number_clean) || str_contains($plate_number_clean, $input_clean)) {
                        return $plate;
                    }

                    $input_clean = substr($input_clean, 0, 5);
                    $plate_number_clean = substr($plate_number_clean, 0, 5);
                    if (str_contains($input_clean, $plate_number_clean) || str_contains($plate_number_clean, $input_clean)) {
                    }
                }
            }
        }
    }
    function extractDigits($string)
    {
        preg_match_all('/\d+/', $string, $matches);
        return implode('', $matches[0]);
    }
    private function checkDuplicateContainer($input, $gate, $noSubstring = 3)
    {
        if (!$input) return false;
        $threshold = config('ocr.field_thresholds.container_code', config('ocr.levenshtein_threshold'));

        $redis = app('redis');
        $key   = 'Traffic_queue_' . $gate;
        $lastTraffic = $redis->lrange($key, 0, -1);
        $checklist = [
            "container_code",
            "container_code_edit",
            "container_code_2",
            "container_code_3",
            "container_code_by_bijac",
        ];
        foreach ($lastTraffic as $k => $raw) {
            $row = json_decode($raw);
            if (!isset($row->data)) continue;
            $container = $row->data;

            foreach ($checklist as $field) {
                $container_code = $container->{$field} ?? null;
                if (!$container_code) continue;
                $lev = levenshtein(
                    substr($this->extractDigits($input), 0, 5),
                    substr($this->extractDigits($container_code), 0, 5)
                );
                if ($lev <= $threshold) return $container;
            }
        }
    }
    public function storeFile($file)
    {
        if (!isset($file)) {
            return ['message' => 'فایل ارسال نشده. مجدد تلاش کنید', "statuscode" => Response::HTTP_BAD_REQUEST, 'link' => null];
        }

        $fileName = uniqid() . '.' . $file->extension();

        $savePath = 'traffic/' . $fileName;

        $file->move(public_path('traffic/'), $fileName);

        return ['message' => 'ذخیره شد', "statuscode" => Response::HTTP_OK, 'link' => $savePath];
    }



    public function getList(Request $request)
    {
        $Traffics = Traffic::with([
            'bijacs' => function ($query) {
                $query
                    ->with('invoices')
                    ->with('allbijacs')
                ;
            },
            "isCustomCheck"
        ]);
        if (!empty($request->gate)) {
            $Traffics->where('gate_number', $request->gate);
        } else {
            // $Traffics->where('gate_number', 0);
        }
        return   $Traffics = $Traffics
            ->filter()
            ->sort()
            ->orderBy('id', 'DESC')
            ->paginate($request->itemPerPage ?? 15);

        return response(
            [
                'message' => 'ok',
                'event_index' => event('Traffic.index', Traffic::query()),
                "Traffic" => $Traffics
            ],
            Response::HTTP_OK
        );
    }

    public function update_customCheck(Request $request, Traffic $Traffic)
    {
        try {
            $user = auth('api')->user();
            $data = [
                "user_id"    => $user->id,
                "table_name" => 'Traffic',
                "table_id"   => $Traffic->id,
                "log_type"   => "checked",
            ];
            Log::updateOrCreate(
                $data,
                [
                    ...$data,
                    "log_date" => now(),
                    "data"     => json_encode([
                        "ip" => $request->ip()
                    ]),
                ]
            );

            return response()->json([
                'message' => 'با موفقیت تایید شد!',
                'data'    => $Traffic->fresh()
                    ? $Traffic->fresh()->load('bijacs')->append('invoice')
                    : null,
            ], 200);
        } catch (\Throwable $e) {
            return $e;
            \Log::error('update_customCheck error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'خطایی در پردازش درخواست رخ داد.',
            ], 500);
        }
    }
}
