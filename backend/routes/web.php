<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Services\BijacSearchService;
use Modules\Ocr\Jobs\ProcessOcrLog;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\OcrBuffer;
use Modules\Ocr\TruckMatcher;
use modules\Auth\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

//حذف
Route::get('/', function () {
    return url()->current();
    // if (!auth()->check()) {
    //     $user = User::first();
    //     // $user->password = bcrypt("0012300123");
    //     // $user->save(); 
    //     auth("api")->login($user);
    // }
});

// $user = User::first();
// auth("api")->login($user);


// php artisan schedule:run
// php artisan queue:listen
// php artisan queue:work --daemon --sleep=1 --tries=3
// php artisan optimize:clear

// Route::post('/test', function () {
//     try {
//         Log::error('Simulation failed: ' . json_encode(request()->all()));
//     } catch (\Throwable $th) {
//         throw $th;
//     }
// });
Route::get('/test', function () {
    return;

    if (isset(request()->bij)) {
        $command = new Modules\Collector\Services\CcsService();
        $data = $command->makeRequest();
        dd($data);
        return response()->json($data);
    }


    if (isset(request()->rec)) {
        $InvoiceService = new Modules\BijacInvoice\Services\InvoiceService();
        return  $InvoiceService->getWithReceiptNumber(request()->rec);
    }
    // return  $InvoiceService->getWithReceiptNumber('BSRCC14040163669');




    return;
    // $response = Http::withoutVerifying()->post("http://172.16.13.10:8083/test", ['since_updated' => 0]);
    // return;
    try {

        set_time_limit(0);

        $response = Http::withoutVerifying()->get("https://api.hssai.ir/test", ['since_updated' => 0]);
        $records = collect($response->json());

        $plateNumbers = $records->pluck('plate_number');
        $containerNumbers = $records->pluck('container_code');

        $existingPlates = Modules\Ocr\Models\OcrLog::whereIn('plate_number', $plateNumbers)->pluck('plate_number');
        $existingContainers = Modules\Ocr\Models\OcrLog::whereIn('container_code', $containerNumbers)->pluck('container_code');

        $records = $records->reject(
            fn($r) =>
            $existingPlates->contains($r['plate_number']) ||
                $existingContainers->contains($r['container_code'])
        );

        foreach ($records as $record) {
            echo 'entiring : ' . $record['id'] . "<br />";

            $ocrLog = OcrLog::create(
                collect($record)->except(['match_status', 'camera_number'])->toArray()
            );
            OcrBuffer::addToBuffer($ocrLog, 1, isset($record['plate_number']) ? 'plate' : 'container');
            ProcessOcrLog::dispatch($ocrLog->id);
            sleep(5);

            /*

            $plateData = [
                'ocr_accuracy' => 0.95,
                'plate_type' => 'ABC',
                'log_time' => now()->toDateTimeString(),
                'plate_number' => $record['plate_number'],
                'direction' => 'entry',
                'container_code' => '',
                'container_code_validation' => 0,
                'frequency' => 1.0,
                'plate_reading_status' => 'read',
                'camera_number' => 1,
                'gate_number' => 1,
                'image_time' => now()->toDateTimeString(),
            ];

            try {
                Http::withoutVerifying()
                    ->attach('plate_image', file_get_contents(public_path('کراپ پلاک.png')), 'dummy_plate.jpg')
                    ->attach('vehicle_image_front', file_get_contents(public_path('جلو کامیون.png')), 'dummy_front.jpg')
                    ->attach('vehicle_image_back', file_get_contents(public_path('پشت کامیون.png')), 'dummy_back.jpg')
                    ->attach('vehicle_image_left', file_get_contents(public_path('جلو کامیون.png')), 'dummy_left.jpg')
                    ->attach('vehicle_image_right', file_get_contents(public_path('جلو کامیون.png')), 'dummy_right.jpg')
                    ->post('http://172.16.13.10:8083/api/ocr-log', $plateData);
            } catch (\Exception $ex) {
                return $ex;
                Log::error("Plate upload failed for {$record['plate_number']}: " . $ex->getMessage());
            }

            sleep(5);

            $containerData = $plateData;
            $containerData['plate_number'] = '';
            $containerData['container_code'] = $record['container_code'];

            try {
                Http::withoutVerifying()
                    ->attach('container_code_image', file_get_contents(public_path('کراپ کانتینر.png')), 'dummy_container.jpg')
                    ->post('http://172.16.13.10:8083/api/ocr-log', $containerData);
            } catch (\Exception $ex) {
                return $ex;
                Log::error("Container upload failed for {$record['container_code']}: " . $ex->getMessage());
            }

            sleep(5);
            */
        }

        return response()->json(['status' => 'completed', 'count' => count($records)]);
    } catch (\Exception $ex) {
        return $ex;
        Log::error('Simulation failed: ' . $ex->getMessage());
        return ['error' => $ex->getMessage()];
    }
    return;



    // try {
    //     $response = Http::withoutVerifying()->get("https://api.hssai.ir/test", ['since_updated' => 0]);
    //     return   $response = $response->json();




    // } catch (\Exception $e) {
    //     return $e->getMessage();
    // }








    return $text = [
        "@attributes" =>  [
            "xmlnxsd" => "http://www.w3.org/2001/XMLSchema",
            "xmlnxsi" => "http://www.w3.org/2001/XMLSchema-instance"
        ],
        "Body" =>  [
            "InvokeResponse" =>  [
                "InvokeResult" =>  [
                    "@attributes" =>  [
                        "xmlnd4p1" => "http://schemas.datacontract.org/2004/07/ApiGateway.Models.Core",
                        "xmlni" => "http://www.w3.org/2001/XMLSchema-instance"
                    ],
                    "Cookies" =>  [
                        "@attributes" =>  [
                            "xmlnd5p1" => "http://schemas.datacontract.org/2004/07/System.Collections.Generic"
                        ]
                    ],
                    "Description" => [],
                    "DigitalSignature" => "G/dClMvd4GMSqPgmwcXqIaRbTwcP8difRPCLXxa1/tMWeKHKszJRwBaNi/DGxJthxBYQw67PPgN6JCSK7o6OpQxx0/hgEZ8W8Uqwwrq4dsBawvS3cPBljGxKIA8/ZwtTA+1sfOtlnXmFApvJl5mtVaL7S5OugpGUm/JN0PTDLZCZXxwhlBidUsZrp8gH7KVBY8ijmly2X1WXqgGn2L7Ab77NpkE26Uvi7JcGASELdldxala8evEm0RajC8MjroU79VtlakVFe/Oc+MjWO+DSc4r+BSXzgfb7gY4DmdR/FWhfigRGofl8qqox1048yv/qpwzuyt/sucDzBhrFmeYpdA==",
                    "Error" => [],
                    "ErrorDescription" => [],
                    "IsSuccessful" => "true",
                    "RequestDate" => "2025-09-15T12:09:13+03:30",
                    "RequestId" => "9137b388-d007-46be-bded-7ef744d02d19",
                    "ResponseDate" => "2025-09-15T12:09:14+03:30",
                    "ResponseHeaders" =>  [
                        "@attributes" =>  [
                            "xmlnd5p1" => "http://schemas.datacontract.org/2004/07/System.Collections.Generic"
                        ],
                        "KeyValueOfstringstring" =>  [
                            "Key" => "Content-Type",
                            "Value" => "text/xml; charset=utf-8"
                        ]
                    ],
                    "ResponseStatusCode" => "200",
                    "ResponseText" =>  [
                        "Envelope" =>  [
                            "Body" =>  [
                                "GetExitPermissionByNumberLimitedResponse" =>  [
                                    "GetExitPermissionByNumberLimitedResult" =>  [
                                        "@attributes" =>  [
                                            "xmlna" => "http://schemas.datacontract.org/2004/07/CCS.Common.DTO",
                                            "xmlni" => "http://www.w3.org/2001/XMLSchema-instance"
                                        ],
                                        "ExitPermissionInfoList" =>  [
                                            "ExitPermissionLimitedInfo" =>  [
                                                "ExitPermissionID" => "6248496",
                                                "ExitPermissionNumber" => "1404022018",
                                                "ContainerNumber" => "MIOU 2223727",
                                                "VehicleNumber" => "52798/73",
                                                "ReceiptNumber" => "BSRCC14040137554",
                                                "Weight" => "0",
                                                "ContainerSize" => "_20Feet",
                                                "HazardousCode" => "0",
                                                "IsSingleCarry" => "false"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "UsedQuotaStats" =>  [
                        "HourlyUsed" => "1",
                        "DailyUsed" => "1",
                        "MonthlyUsed" => "1"
                    ]
                ]
            ]
        ]
    ];

    $data = $text['Body']['InvokeResponse']['InvokeResult']['ResponseText']['Envelope']['Body']['GetExitPermissionByNumberLimitedResponse']['GetExitPermissionByNumberLimitedResult']['ExitPermissionInfoList']['ExitPermissionLimitedInfo'] ?? [];

    dd($data);

    $code = '$array = ' . $text . ';';

    // اجرای eval (مراقب باشید اگر متن از منبع ناشناس است)
    eval($code);

    dd($array);

    print_r($array);
    $stack = range(1, 100);
    array_unshift($stack, 200);
    if (count($stack) > 9) {
        array_splice($stack, 9);
        // array_pop($stack);
    }

    array_unshift($stack, 300);
    return $stack;

    return OcrBuffer::getBuffer(1);
    return cache('container_ocr__stack_1');
    similar_text('8423432', '8433422', $p);
    dd($p);
    // $ocr = OcrMatch::find(223359);
    $ocr = OcrLog::find(282225);
    // dd($ocr);

    $bijacService = new BijacSearchService();

    dd($bijacService::getPlateBijacs($ocr));

    $bijac = $ocr->bijacs
        ->sortByDesc('bijac_date')
        ->first();
    $bijacs = Bijac::where('receipt_number', $bijac->receipt_number)
        ->get();
    // dd($bijacs);

    dd(
        DB::table('bijacables')
            ->select(DB::raw('MIN(bijacable_id) as bijacable_id'))
            ->where('bijacable_type', OcrMatch::class)
            ->whereIn('bijac_id', $bijacs
                ->pluck('id'))
            ->whereExists(function ($query) use ($ocr) {
                $query->select(DB::raw(1))
                    ->from('ocr_matches')
                    ->whereColumn('ocr_matches.id', 'bijacables.bijacable_id')
                    ->where('ocr_matches.log_time', '<=', $ocr->log_time);
            })

            ->groupBy('bijac_id') // گروپ‌بای روی بیجک
            ->distinct()
            ->get()
            ->count()
    );

    DB::table('bijacables')
        ->where('bijacable_type', OcrMatch::class)
        ->whereExists(function ($query) use ($ocr) {
            $query->select(DB::raw(1))
                ->from('ocr_matches')
                ->whereColumn('ocr_matches.id', 'bijacables.bijacable_id')
                ->where('ocr_matches.log_time', '<=', $ocr->log_time);
        })
        ->whereIn('bijac_id', $bijacs
            ->pluck('id'))
        ->distinct('bijac_id')
        ->pluck('bijacable_id')
        ->count();

    $latestIds = OcrMatch::orderBy('id', 'desc')
        ->limit(300)
        ->get();

    $ocr_id = $latestIds->min('ocr_log_id');
    $ocr = OcrLog::where('id', '>', $ocr_id)->get();

    dd($ocr);

    OcrMatch::whereIn('id', $latestIds->pluck('id'))->delete();

    DB::table('bijacable')
        ->whereIn('bijacable_id', $latestIds->pluck('id'))
        ->delete();

    // بازنشانی مقدار آیدی به آخرین مقدار موجود
    DB::statement('ALTER TABLE ocr_mtches AUTO_INCREMENT = 1');
    foreach ($ocr as $oc) {
        ProcessOcrLog::dispatch(
            $oc->id
        );
    }

    // foreach ($ocr as $o)
    //     TruckMatcher::makeSendJobs($o->id);
});
