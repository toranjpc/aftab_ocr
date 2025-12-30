<?php

namespace Modules\BijacInvoice\Models;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Modules\Ocr\Models\OcrMatch;
use Illuminate\Support\Facades\DB;
use Modules\BijacInvoice\Services\PlateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Bijac extends Base
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    const SEARCH_DAY = [12, 24, 48, 72]; //, 7, 30

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'receipt_number', 'receipt_number');
    }
    public function invoiceBase(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'receipt_number', 'receipt_number')->where('base', 1);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'receipt_number', 'receipt_number');
    }
    public function allbijacs()
    {
        return $this->hasMany(Bijac::class, 'receipt_number', 'receipt_number')->select("id", "receipt_number", "plate_normal");
    }

    // public function ocrMatches(): BelongsToMany
    // {
    //     $databaseName = DB::connection()->getDatabaseName();
    //     return $this->belongsToMany(
    //         OcrMatch::class,
    //         "$databaseName.bijac_ocr_match",
    //     );
    // }

    public function ocrMatches()
    {
        return $this->morphedByMany(OcrMatch::class, 'bijacable');
    }


    public function scopeLatestBetween($query, $dateRange)
    {
        $maxDate = (clone $query)->max('bijac_date');

        if (!$maxDate) {
            return $query->whereRaw('1=0'); // عمداً خالی
        }

        return $query->where('bijac_date', $maxDate);
    }

    /**
     * Scope: پیدا کردن بیجک‌های مربوط به یک پلاک 
     */
    public function scopeForPlate($query, $item, $isEdited)
    {
        try {
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                ->info("scopeForPlateRun for plate_number : {$item->plate_number}  ");
        } catch (\Throwable $th) {
        }

        if ($isEdited && !$item->plate_number_edit)
            return $query->whereRaw('1=0');
        if (!$isEdited && !$item->plate_number)
            return $query->whereRaw('1=0');

        $plate_number = $isEdited ? $item->plate_number_edit : $item->plate_number;
        $PlateService = new PlateService();
        $plate_number = $PlateService->normalizePlate($plate_number);
        if ($isEdited)
            log::info("newcode : " . json_encode($plate_number));


        $cleanNumber = preg_replace('/\D/', '', $plate_number);
        $threshold = config('ocr.field_thresholds.plate_number', 1);

        if (strlen($cleanNumber) < 3) {
            return $query->whereRaw('1=0');
        }

        try {
            // Cache logic - check Redis first
            $cachedResults = collect();
            // Check alpha-numeric match first
            $alphaNumericMatch = Redis::hget('plate_map', $plate_number);
            if ($alphaNumericMatch) {
                $cachedResults->push(json_decode($alphaNumericMatch, true));
            }
            // If no results, check numbers-only match
            if ($cachedResults->isEmpty()) {
                $numbersOnlyMatch = Redis::hget('plate_numbers_map', $cleanNumber);
                if ($numbersOnlyMatch) {
                    $cachedResults->push(json_decode($numbersOnlyMatch, true));
                }
            }
            // Check first 7 digits with Levenshtein distance if >= 7 digits
            if ($cachedResults->isEmpty() && strlen($cleanNumber) >= 7) {
                $plate_first7 = substr($cleanNumber, 0, 7);
                $plateSetKey = "plate_first7_set:$plate_first7";
                $setMembers = Redis::smembers($plateSetKey);
                foreach ($setMembers as $member) {
                    $decodedMember = json_decode($member, true);
                    $decodedPlateNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['plate_normal'] ?? '');
                    if (levenshtein($cleanNumber, $decodedPlateNumbersOnly) <= 1) {
                        $cachedResults->push($decodedMember);
                    }
                }
            }
            // Check first 5 digits with Levenshtein distance if >= 5 digits
            if ($cachedResults->isEmpty() && strlen($cleanNumber) >= 5) {
                $plate_first5 = substr($cleanNumber, 0, 5);
                $plateSetKey = "plate_first5_set:$plate_first5";
                $setMembers = Redis::smembers($plateSetKey);
                foreach ($setMembers as $member) {
                    $decodedMember = json_decode($member, true);
                    $decodedPlateNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['plate_normal'] ?? '');
                    if (levenshtein($cleanNumber, $decodedPlateNumbersOnly) <= 1) {
                        $cachedResults->push($decodedMember);
                    }
                }
            }
            // Check 7-digit numbers-only match
            if ($cachedResults->isEmpty()) {
                $numbersOnly7 = strlen($cleanNumber) >= 7 ? substr($cleanNumber, 0, 7) : null;
                if ($numbersOnly7) {
                    $numbersOnly7Match = Redis::hget('plate_numbers_map', $numbersOnly7);
                    if ($numbersOnly7Match) {
                        $cachedResults->push(json_decode($numbersOnly7Match, true));
                    }
                }
            }
            // Check 5-digit numbers-only match
            if ($cachedResults->isEmpty()) {
                $numbersOnly5 = strlen($cleanNumber) >= 5 ? substr($cleanNumber, 0, 5) : null;
                if ($numbersOnly5) {
                    $numbersOnly5Match = Redis::hget('plate_numbers_map', $numbersOnly5);
                    if ($numbersOnly5Match) {
                        $cachedResults->push(json_decode($numbersOnly5Match, true));
                    }
                }
            }
            // If cached results found, return them as Bijac IDs
            if ($cachedResults->isNotEmpty()) {
                $uniqueCachedResults = $cachedResults->unique('id')->pluck('id')->toArray();

                try {
                    log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                        ->info("find place in redis : {$plate_number} id : ({$item->id})");
                } catch (\Throwable $th) {
                }

                return Bijac::whereIn('id', $uniqueCachedResults);
            }
        } catch (\Throwable $th) {
            //throw $th;
            log::info("redis search bijac not work : ");
        }


        // Continue with existing database search logic if no cache hit
        foreach (self::SEARCH_DAY as $day) {

            $dateRange = [
                (clone $item->created_at)->subHours($day),
                $item->created_at,
            ];
            $dateRange_ = (clone $item->created_at)->subHours($day);

            $resultBase = Bijac::when($day > 12, function ($q) {
                return $q->whereDoesntHave('ocrMatches', function ($query) {
                    $query->whereNotNull('plate_number')->where('plate_number', '!=', '');
                });
                // return $q->doesntHave('ocrMatches');
            })
                // اولین در 24 ساعت قبل و اخرین در بیش از یک روز (پیشنهاد اقای ولیپور)
                // ->when(
                //     $day <= 24,
                //     fn($q) => $q->orderBy('bijac_date', 'asc'),
                //     fn($q) => $q->orderBy('bijac_date', 'desc')
                // )
                ->orderBy('bijac_date', 'desc')
                ->where('bijac_date', '>', $dateRange_);
            // ->whereBetween('bijac_date', $dateRange)
            // ->orderBy('bijac_date', 'desc');
            // ->where(function ($query) {
            //     $query->where('bijac_date', '>=', now()->subHours(12))
            //         ->orWhere(function ($q) {
            //             $q->where('bijac_date', '<', now()->subHours(12))
            //                 ->doesntHave('ocrMatches');
            //         });
            // });
            // ->latestBetween($dateRange);


            $result = clone $resultBase;
            $result = $result->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", [$plate_number])
                ->get();

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                    ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE {$plate_number} date ({$item->id}) : " . json_encode($dateRange_));
            } catch (\Throwable $th) {
            }

            if ($result->isNotEmpty())
                break;

            $result = clone $resultBase;
            $result = $result->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", [$plate_number . "%"])
                ->get();

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                    ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE {$plate_number}% date : " . json_encode($dateRange_));
            } catch (\Throwable $th) {
            }

            $result = $this->levenPlace($result, $cleanNumber, $threshold);
            if ($result->isNotEmpty())
                break;


            if (strlen($cleanNumber) <= 4) {
                $result = clone $resultBase;
                $result = $result->where(function ($q) use ($cleanNumber) {
                    $q->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", ['___' . $cleanNumber . '_']);
                    $q->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", ['___' . $cleanNumber]);
                })
                    ->get();

                try {
                    log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                        ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE {__{$cleanNumber}_ date : " . json_encode($dateRange_));
                    log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                        ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE {__{$cleanNumber} date : " . json_encode($dateRange_));
                } catch (\Throwable $th) {
                }


                if ($result->isNotEmpty())
                    break;
            }

            // if (strlen($cleanNumber) == 7) {
            $result = clone $resultBase;
            $result = $result->where(function ($q) use ($cleanNumber) {
                // $wildcardPattern = substr($cleanNumber, 0, 5);
                $q->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$cleanNumber]);
                // ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$wildcardPattern . '__']);
            })
                ->get();

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                    ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE {$cleanNumber} date : " . json_encode($dateRange_));
            } catch (\Throwable $th) {
            }

            if ($result->isNotEmpty())
                break;
            // }

            $wildcardPattern = substr($cleanNumber, 0, 5);
            $result = clone $resultBase;
            $result = $result->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$wildcardPattern])
                ->get();


            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                    ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE {$wildcardPattern} date : " . json_encode($dateRange_));
            } catch (\Throwable $th) {
            }

            if ($result->isNotEmpty())
                break;


            /*
            $result = clone $resultBase;
            $result = $result->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$wildcardPattern . "%"])
                ->get();

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(".jdate()->format('ymd').")_" . $item->gate_number . ".log"),])
                    ->info("scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE {$wildcardPattern}% ");
            } catch (\Throwable $th) {
            }


            $result = $this->levenPlace($result, $cleanNumber, $threshold);
            */
            // if ($result->isNotEmpty())
            //     break; // اگر چیزی پیدا شد دیگر ادامه ندهیم
        }

        if ($result->isEmpty()) {
            return $query->whereRaw('1=0');
        }
        return Bijac::whereIn('id', $result->pluck('id'));
    }

    public function scopeForContainer($query, $item, $isEdited)
    {
        try {
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                ->info("scopeForContainerRun for container_code : {$item->container_code}  ");
        } catch (\Throwable $th) {
        }
        $threshold = 1; //config('ocr.field_thresholds.container_code', 1); چون فقط روی اعداد انجام میشود

        // if ($isEdited && !$item->container_code_edit_standard)
        if ($isEdited && !$item->container_code_edit)
            return $query->whereRaw('1=0');

        // if (!$isEdited && !$item->container_code_standard)
        if (!$isEdited && !$item->container_code)
            return $query->whereRaw('1=0');

        $container_code = $isEdited ?
            // $item->container_code_edit_standard :
            $item->container_code_edit :
            // $item->container_code_standard;
            $item->container_code;
        if ($isEdited)
            log::info("newcode : " . json_encode($container_code));

        try {
            // Cache logic - check Redis first for container
            $cachedResults = collect();

            // Clean container code for cache search
            $cleanContainerCode = substr(str_replace([' ', '_', '-'], '', $container_code), 0, 11);
            $containerAlphaNumeric = preg_replace('/[^a-zA-Z0-9]/', '', $cleanContainerCode);
            $containerNumbersOnly = preg_replace('/[^0-9]/', '', $cleanContainerCode);

            // Check alpha-numeric match first
            $alphaNumericMatch = Redis::hget('container_map', $containerAlphaNumeric);
            if ($alphaNumericMatch) {
                $cachedResults->push(json_decode($alphaNumericMatch, true));
            }

            // If no results, check numbers-only match
            if ($cachedResults->isEmpty()) {
                $numbersOnlyMatch = Redis::hget('container_numbers_map', $containerNumbersOnly);
                if ($numbersOnlyMatch) {
                    $cachedResults->push(json_decode($numbersOnlyMatch, true));
                }
            }

            // Check first 7 digits with Levenshtein distance if >= 7 digits
            if ($cachedResults->isEmpty() && strlen($containerNumbersOnly) >= 7) {
                $container_first7 = substr($containerNumbersOnly, 0, 7);
                $containerSetKey = "container_first7_set:$container_first7";
                $setMembers = Redis::smembers($containerSetKey);
                foreach ($setMembers as $member) {
                    $decodedMember = json_decode($member, true);
                    $decodedContainerNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['container_number'] ?? '');
                    if (levenshtein($containerNumbersOnly, $decodedContainerNumbersOnly) <= 1) {
                        $cachedResults->push($decodedMember);
                    }
                }
            }

            // Check first 6 digits with Levenshtein distance if >= 6 digits
            if ($cachedResults->isEmpty() && strlen($containerNumbersOnly) >= 6) {
                $container_first6 = substr($containerNumbersOnly, 0, 6);
                $containerSetKey = "container_first6_set:$container_first6";
                $setMembers = Redis::smembers($containerSetKey);
                foreach ($setMembers as $member) {
                    $decodedMember = json_decode($member, true);
                    $decodedContainerNumbersOnly = preg_replace('/[^0-9]/', '', $decodedMember['container_number'] ?? '');
                    if (levenshtein($containerNumbersOnly, $decodedContainerNumbersOnly) <= 1) {
                        $cachedResults->push($decodedMember);
                    }
                }
            }

            // Check 7-digit numbers-only match
            if ($cachedResults->isEmpty()) {
                $containerNumbersOnly7 = strlen($containerNumbersOnly) >= 7 ? substr($containerNumbersOnly, 0, 7) : null;
                if ($containerNumbersOnly7) {
                    $containerNumbersOnly7Match = Redis::hget('container_numbers_map', $containerNumbersOnly7);
                    if ($containerNumbersOnly7Match) {
                        $cachedResults->push(json_decode($containerNumbersOnly7Match, true));
                    }
                }
            }

            // Check 6-digit numbers-only match
            if ($cachedResults->isEmpty()) {
                $containerNumbersOnly6 = strlen($containerNumbersOnly) >= 6 ? substr($containerNumbersOnly, 0, 6) : null;
                if ($containerNumbersOnly6) {
                    $containerNumbersOnly6Match = Redis::hget('container_numbers_map', $containerNumbersOnly6);
                    if ($containerNumbersOnly6Match) {
                        $cachedResults->push(json_decode($containerNumbersOnly6Match, true));
                    }
                }
            }

            // If cached results found, return them as Bijac IDs
            if ($cachedResults->isNotEmpty()) {
                $uniqueCachedResults = $cachedResults->unique('id')->pluck('id')->toArray();

                try {
                    log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                        ->info("find place in redis : {$container_code} id : ({$item->id})");
                } catch (\Throwable $th) {
                }

                return Bijac::whereIn('id', $uniqueCachedResults);
            }
        } catch (\Throwable $th) {
            //throw $th;
            log::info("redis search container not work : ");
        }

        // Continue with existing database search logic if no cache hit
        foreach (self::SEARCH_DAY as $day) {

            $dateRange = [
                (clone $item->created_at)->subHours($day),
                $item->created_at,
            ];
            $dateRange_ = (clone $item->created_at)->subHours($day);

            $container_code = substr(str_replace([' ', '_', '-'], '', $container_code), 0, 11); //BXAU2035845
            $codesToTry = [$container_code];

            if (!$isEdited && !empty($item->container_code_2)) {
                // $codesToTry[] = $item->container_code_standard2;
                $codesToTry[] = $item->container_code2;
            }

            foreach ($codesToTry as $code) {
                $digits = substr(preg_replace('/\D/', '', $code), 0, 7);
                if (strlen($digits) >= 7 && $digits != $code) {
                    $codesToTry[] = $digits;

                    $digits = substr($digits, 0, 6);
                    if ($digits != $code) {
                        $codesToTry[] = $digits;
                    }
                }
            }

            $result = collect();

            foreach ($codesToTry as $code) {
                $queryTry = Bijac::when($day > 12, function ($q) {
                    return $q->whereDoesntHave('ocrMatches', function ($query) {
                        $query->whereNotNull('container_code')->where('container_code', '!=', '');
                    });
                    // return $q->doesntHave('ocrMatches');
                })
                    // اولین در 24 ساعت قبل و اخرین در بیش از یک روز (پیشنهاد اقای ولیپور)
                    // ->when(
                    //     $day <= 24,
                    //     fn($q) => $q->orderBy('bijac_date', 'asc'),
                    //     fn($q) => $q->orderBy('bijac_date', 'desc')
                    // )
                    ->orderBy('bijac_date', 'desc')
                    ->where('bijac_date', '>', $dateRange_)
                    // ->whereBetween('bijac_date', $dateRange)
                    // ->orderBy('bijac_date', 'desc')
                    // $queryTry = Bijac::whereBetween('bijac_date', $dateRange)
                    //     // when($day >= 3, function ($query) {
                    //     //     return $query->doesntHave('ocrMatches');
                    //     // })
                    //     ->where(function ($query) {
                    //         $query->where('bijac_date', '>=', now()->subHours(12))
                    //             ->orWhere(function ($q) {
                    //                 $q->where('bijac_date', '<', now()->subHours(12))
                    //                     ->doesntHave('ocrMatches');
                    //             });
                    //     })
                    // ->where('container_number', 'LIKE', "%{$code}%")
                    ->whereRaw("REGEXP_REPLACE(container_number, '[^a-zA-Z0-9]', '') LIKE ?", ["%" . $code . "%"])
                    ->latestBetween($dateRange);
                try {
                    log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_(" . jdate()->format('ymd') . ")_" . $item->gate_number . ".log"),])
                        ->info($day . "_scopeForPlateRun : REGEXP_REPLACE(container_number, '[^a-zA-Z0-9]', '') LIKE %{$code}% ");
                } catch (\Throwable $th) {
                }

                $result_ = $queryTry->get();
                $result = [];
                foreach ($result_ as $key => $value) {
                    $cleanNumber = str_replace([' ', '_', '-'], '', $value->container_number);
                    $code = str_replace([' ', '_', '-'], '', $code);
                    $lev = levenshtein($code, $cleanNumber);
                    if ($lev <= 2) { //با حروف لون 2 باشد
                        $result[] = $value;
                        break;
                    }
                    $cleanNumber = preg_replace('/\D/', '', $value->container_number);
                    $code = preg_replace('/\D/', '', $code);
                    $lev = levenshtein($code, $cleanNumber);
                    if ($lev <= 1) { // عدد خالی لون 1 باشد
                        $result[] = $value;
                        break;
                    }
                }
                $result = collect($result);

                // $result = $result_->filter(function ($res) use ($code, $threshold) {
                //     $cleanNumber = preg_replace('/\D/', '', $res->container_number);
                //     $code = preg_replace('/\D/', '', $code);
                //     $lev = levenshtein($code, $cleanNumber);
                //     if ($lev <= $threshold) return true;
                // });

                if ($result->isNotEmpty()) {
                    break;
                }
            }

            if ($result->isNotEmpty()) {
                break;
            }
        }

        if ($result->isEmpty()) {
            return $query->whereRaw('1=0');
        }
        return Bijac::whereIn('id', $result->pluck('id'));
    }


    private function levenPlace($result, $place, $threshold = 1)
    {
        $result = $result->filter(function ($res) use ($place, $threshold) {
            $lev = levenshtein($place, $res->plate_normal);
            if ($lev <= $threshold)
                return true;
        });
        return $result;
    }
}
