<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Services\BijacSearchService;
use Modules\Ocr\Jobs\ProcessOcrLog;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\OcrBuffer;
use Modules\Ocr\Controller\OcrMatchController;
use Modules\Ocr\TruckMatcher;
use modules\Auth\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\Sse\Models\SSE;
use Modules\Ocr\Controller\LogRepController;
use Illuminate\Support\Facades\Redis;

//حذف
Route::get('/', function () {
    return env('DB_USERNAME');
    return now();
    // if (!auth()->check()) {
    //     $user = User::first();
    //     // $user->password = bcrypt("0012300123");
    //     // $user->save(); 
    //     auth("api")->login($user);
    // }
});
Route::get('/dump-redis-data', function () {
    $redisData = [];

    // Get all hash maps
    $hashMaps = [
        'plate_map',
        // 'plate_numbers_map',
        // 'plate_first7_set',
        // 'plate_first5_set',
        // 'container_map',
        // 'container_numbers_map',
        // 'receipt_number_map',
        // 'bijac_date_map',
        // 'bijac_number_map',
    ];

    foreach ($hashMaps as $hashName) {
        $redisData[$hashName] = Redis::hgetall($hashName);
    }
    return response()->json($redisData);

    // Get all set indexes and their members
    $setIndexes = [
        'bijac:indexes:plate_first7',
        'bijac:indexes:plate_first5',
        'bijac:indexes:container_first7',
        'bijac:indexes:container_first6',
    ];

    foreach ($setIndexes as $indexName) {
        $redisData[$indexName] = [];
        $members = Redis::smembers($indexName);
        foreach ($members as $memberKey) {
            // Each memberKey is itself a set key, e.g., "plate_first7_set:6961111"
            $redisData[$indexName][$memberKey] = Redis::smembers($memberKey);
        }
    }
    return response()->json($redisData);
});

Route::get('/test-redis-plate/{plate_number}', function ($plate_number) {
    $tim = microtime(true);
    // $plateService = new PlateService();
    $normalizedPlate = $plate_number; //$plateService->normalizePlate($plate_number);

    echo $cleanNumber = preg_replace('/[^a-zA-Z0-9]/', '', $normalizedPlate);
    echo " - ";
    echo $numbersOnly = preg_replace('/[^0-9]/', '', $normalizedPlate);
    echo " - ";
    echo $numbersOnly7 = strlen($numbersOnly) >= 7 ? substr($numbersOnly, 0, 7) : null;
    echo " - ";
    echo $numbersOnly5 = strlen($numbersOnly) >= 5 ? substr($numbersOnly, 0, 5) : null;
    echo " - ";
    $results = collect();

    $alphaNumericMatch = Redis::hget('plate_map', $cleanNumber);
    if ($alphaNumericMatch) {
        $results->push(json_decode($alphaNumericMatch, true));
        echo " 1 - ($normalizedPlate , $cleanNumber) ";
    }
    if ($results->isEmpty()) {
        $numbersOnlyMatch = Redis::hget('plate_numbers_map', $numbersOnly);
        if ($numbersOnlyMatch) {
            $results->push(json_decode($numbersOnlyMatch, true));
            echo " 2 - ($normalizedPlate , $numbersOnly) ";
        }
    }
    if ($results->isEmpty() && strlen($numbersOnly) >= 7) {
        echo  $plate_first7 = substr($numbersOnly, 0, 7);
        echo " - ";

        $plateSetKey = "plate_first7_set:$plate_first7";
        $setMembers = Redis::smembers($plateSetKey);
        foreach ($setMembers as $member) {
            $decodedMember = json_decode($member, true);
            $decodedPlateNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['plate_normal'] ?? '');

            if (levenshtein($numbersOnly, $decodedPlateNumbersOnly) <= 1) {
                $results->push($decodedMember);
                echo " 3 - ($normalizedPlate , $numbersOnly, $decodedPlateNumbersOnly) ";
            }
        }
    }

    if ($results->isEmpty() && strlen($numbersOnly) >= 5) {
        echo $plate_first5 = substr($numbersOnly, 0, 5);
        echo " - ";

        $plateSetKey = "plate_first5_set:$plate_first5";
        $setMembers = Redis::smembers($plateSetKey);
        foreach ($setMembers as $member) {
            $decodedMember = json_decode($member, true);
            $decodedPlateNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['plate_normal'] ?? '');

            if (levenshtein($numbersOnly, $decodedPlateNumbersOnly) <= 1) {
                $results->push($decodedMember);
                echo " 4 - ($normalizedPlate , $numbersOnly, $decodedPlateNumbersOnly) ";
            }
        }
    }
    if ($results->isEmpty()) {
        $numbersOnly7Match = Redis::hget('plate_numbers_map', $numbersOnly7);
        if ($numbersOnly7Match) {
            $results->push(json_decode($numbersOnly7Match, true));
            echo " 5 - ($normalizedPlate , $numbersOnly7) ";
        }
    }
    if ($results->isEmpty()) {
        $numbersOnly5Match = Redis::hget('plate_numbers_map', $numbersOnly5);
        if ($numbersOnly5Match) {
            $results->push(json_decode($numbersOnly5Match, true));
            echo " 6 - ($normalizedPlate , $numbersOnly5) ";
        }
    }

    // Remove duplicates and return unique results
    $uniqueResults = $results->unique('id')->values()->all();

    echo microtime(true) - $tim;

    return response()->json($uniqueResults);
});
// $user = User::first();
// auth("api")->login($user);


// php artisan schedule:run
// php artisan queue:listen
// php artisan queue:work --daemon --sleep=1 --tries=3
// php artisan optimize:clear

// php artisan config:clear
// php artisan cache:clear
// php artisan config:cache


// Route::post('/test', function () {
//     try {
//         Log::error('Simulation failed: ' . json_encode(request()->all()));
//     } catch (\Throwable $th) {
//         throw $th;
//     }
// });

Route::get('/test', function () {
    // cache()->set('Invoice_last_sync_id', 0);
    // return cache('Invoice_last_sync_id', 0);
    try {

        if (isset(request()->exportfiles)) {
            $item = OcrMatch::select("id", "vehicle_image_back_url")
                ->whereHas('bijacs', function ($q) {
                    $q->whereNotNull('dangerous_code')
                        ->where('dangerous_code', '!=', 0);
                })
                ->with([
                    'bijacs' => function ($q) {
                        $q->select("id", "dangerous_code")
                            ->whereNotNull('dangerous_code')
                            ->where('dangerous_code', '!=', 0);
                    }
                ])
                ->whereNotNull("vehicle_image_back_url")
                ->whereDate("created_at", ">=", now()->subDays(30))
                // ->limit(10)
                ->get();
            $images = [];
            foreach ($item as $value) {
                foreach ($value->bijacs as $bajac) {
                    if (!isset($images[$bajac->dangerous_code]))
                        $images[$bajac->dangerous_code] = [];
                    $images[$bajac->dangerous_code][] = str_replace("uploaded/img/", "", $value->vehicle_image_back_url);
                }
            }


            $sourceFolder = public_path('uploaded');
            $destinationFolder = public_path('uploaded/danger');

            if (!is_dir($destinationFolder)) {
                mkdir($destinationFolder, 0777, true);
            }

            foreach ($images as $key => $fileList) {
                $keyFolder = $destinationFolder . DIRECTORY_SEPARATOR . $key;
                if (!is_dir($keyFolder)) {
                    mkdir($keyFolder, 0777, true);
                }

                foreach ($fileList as $fileName) {
                    $srcFile = $sourceFolder . DIRECTORY_SEPARATOR . $fileName;
                    $dstFile = $keyFolder . DIRECTORY_SEPARATOR . $fileName;

                    if (file_exists($srcFile)) {
                        copy($srcFile, $dstFile);
                        echo "Copied $fileName to $keyFolder\n";
                    } else {
                        echo "File not found: $fileName\n";
                    }
                }
            }

            return "Done!\n";
        }


        if (isset(request()->bij)) {
            if (!empty(request()->ccs)) {
                $CcsService = new Modules\Collector\Services\CcsService();
                $DBBijac = $CcsService->getByReceipt(request()->bij);
                if ($DBBijac)
                    return $DBBijac;
            }


            $GcomsService = new Modules\Collector\Services\GcomsService();
            $DBBijac = $GcomsService->getBijacTaki(request()->bij);

            return $DBBijac;



            $bijac_number = request()->bij;
            $DBBijac = Bijac::where("bijac_number", $bijac_number . "G")->get();
            if ($DBBijac->isEmpty()) {
                $GcomsService = new Modules\Collector\Services\GcomsService();
                $DBBijac = $GcomsService->getBijacTaki(request()->bij);
            }
            return $DBBijac;
        }


        if (isset(request()->rec)) {
            $InvoiceService = new Modules\BijacInvoice\Services\InvoiceService();
            return $InvoiceService->getWithReceiptNumber(request()->rec);
        }
        // return  $InvoiceService->getWithReceiptNumber('BSRCC14040163669');

        return;

        return $text = [
            "@attributes" => [
                "xmlnxsd" => "http://www.w3.org/2001/XMLSchema",
                "xmlnxsi" => "http://www.w3.org/2001/XMLSchema-instance"
            ],
            "Body" => [
                "InvokeResponse" => [
                    "InvokeResult" => [
                        "@attributes" => [
                            "xmlnd4p1" => "http://schemas.datacontract.org/2004/07/ApiGateway.Models.Core",
                            "xmlni" => "http://www.w3.org/2001/XMLSchema-instance"
                        ],
                        "Cookies" => [
                            "@attributes" => [
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
                        "ResponseHeaders" => [
                            "@attributes" => [
                                "xmlnd5p1" => "http://schemas.datacontract.org/2004/07/System.Collections.Generic"
                            ],
                            "KeyValueOfstringstring" => [
                                "Key" => "Content-Type",
                                "Value" => "text/xml; charset=utf-8"
                            ]
                        ],
                        "ResponseStatusCode" => "200",
                        "ResponseText" => [
                            "Envelope" => [
                                "Body" => [
                                    "GetExitPermissionByNumberLimitedResponse" => [
                                        "GetExitPermissionByNumberLimitedResult" => [
                                            "@attributes" => [
                                                "xmlna" => "http://schemas.datacontract.org/2004/07/CCS.Common.DTO",
                                                "xmlni" => "http://www.w3.org/2001/XMLSchema-instance"
                                            ],
                                            "ExitPermissionInfoList" => [
                                                "ExitPermissionLimitedInfo" => [
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
                        "UsedQuotaStats" => [
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

    } catch (\Throwable $th) {
        throw $th;
    }
});
