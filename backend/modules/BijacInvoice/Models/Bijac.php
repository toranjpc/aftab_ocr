<?php

namespace Modules\BijacInvoice\Models;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Modules\Ocr\Models\OcrMatch;
use Illuminate\Support\Facades\DB;
use Modules\BijacInvoice\Services\PlateService;
use Illuminate\Support\Facades\Log;

class Bijac extends Base
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    const SEARCH_DAY = [3]; //, 7, 30

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
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $item->gate_number . ".log"),])
                ->info("scopeForPlateRun for plate_number : {$item->plate_number}  ");
        } catch (\Throwable $th) {
        }

        if ($isEdited && !$item->plate_number_edit) return $query->whereRaw('1=0');
        if (!$isEdited && !$item->plate_number) return $query->whereRaw('1=0');

        $plate_number = $isEdited ? $item->plate_number_edit : $item->plate_number;
        $PlateService = new  PlateService();
        $plate_number = $PlateService->normalizePlate($plate_number);

        $cleanNumber = preg_replace('/\D/', '', $plate_number);

        if (strlen($cleanNumber) < 3) {
            return $query->whereRaw('1=0');
        }

        foreach (self::SEARCH_DAY as $day) {

            $dateRange = [
                (clone $item->created_at)->subDays($day),
                $item->created_at,
            ];

            $result = Bijac::when($day > 3, function ($query) {
                return $query->doesntHave('ocrMatches');
            })
                ->whereBetween('bijac_date', $dateRange)
                // ->where('plate_normal', $plate_number)
                ->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", [$plate_number])
                ->latestBetween($dateRange)
                ->get();

            try {
                log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $item->gate_number . ".log"),])
                    ->info("scopeForPlateRun : REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE {$plate_number} ");
            } catch (\Throwable $th) {
            }

            if (!$result->isEmpty()) continue;

            $result = Bijac::when($day > 3, function ($query) {
                return $query->doesntHave('ocrMatches');
            })
                ->whereBetween('bijac_date', $dateRange)
                // ->where('plate_normal', $plate_number)
                ->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", [$plate_number . "%"])
                ->latestBetween($dateRange)
                ->get();
            if (!$result->isEmpty()) continue;


            if (strlen($cleanNumber) <= 4) {
                $result = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->whereBetween('bijac_date', $dateRange)
                    ->where(function ($q) use ($cleanNumber) {
                        $q->whereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", ['___' . $cleanNumber . '_']);
                        $q->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^a-zA-Z0-9]', '') LIKE ?", ['___' . $cleanNumber]);
                    })
                    ->latestBetween($dateRange)
                    ->get();
                if (!$result->isEmpty()) continue;
            }

            // if (strlen($cleanNumber) == 7) {
            $result = Bijac::when($day > 3, function ($query) {
                return $query->doesntHave('ocrMatches');
            })
                ->whereBetween('bijac_date', $dateRange)
                ->where(function ($q) use ($cleanNumber) {
                    // $wildcardPattern = substr($cleanNumber, 0, 5);
                    $q->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$cleanNumber]);
                    // ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$wildcardPattern . '__']);

                })
                ->latestBetween($dateRange)
                ->get();

            if (!$result->isEmpty()) continue;
            // }

            $wildcardPattern = substr($cleanNumber, 0, 5);
            $result = Bijac::when($day > 3, function ($query) {
                return $query->doesntHave('ocrMatches');
            })
                ->whereBetween('bijac_date', $dateRange)
                ->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", [$wildcardPattern])
                ->latestBetween($dateRange)
                ->get();
            // if (!$result->isEmpty()) continue;


            // $result = Bijac::when($day > 3, function ($query) {
            //     return $query->doesntHave('ocrMatches');
            // })
            //     ->whereBetween('bijac_date', $dateRange)
            //     ->whereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", ['%' . $cleanNumber . '%'])
            //     ->latestBetween($dateRange)
            //     ->get();


            if ($result->isNotEmpty()) break; // اگر چیزی پیدا شد دیگر ادامه ندهیم
        }

        return $result->isNotEmpty()
            ? Bijac::whereIn('id', $result->pluck('id'))
            : $query->whereRaw('1=0');
    }

    public function scopeForContainer($query, $item, $isEdited)
    {
        try {
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $item->gate_number . ".log"),])
                ->info("scopeForContainerRun for container_code : {$item->container_code}  ");
        } catch (\Throwable $th) {
        }

        if ($isEdited && !$item->container_code_edit_standard)
            return $query->whereRaw('1=0');

        if (!$isEdited && !$item->container_code_standard)
            return $query->whereRaw('1=0');

        $container_code = $isEdited ?
            $item->container_code_edit_standard :
            $item->container_code_standard;

        foreach (self::SEARCH_DAY as $day) {

            $dateRange = [
                (clone $item->created_at)->subDays($day),
                $item->created_at->addDay()
            ];

            $codesToTry = [$container_code];

            if (!$isEdited && !empty($item->container_code_2)) {
                $codesToTry[] = $item->container_code_standard2;
            }

            foreach ($codesToTry as $code) {
                $digits = substr(preg_replace('/\D/', '', $code), 0, 6);

                if (strlen($digits) >= 6) {
                    $codesToTry[] = $digits;
                }
            }

            $result = collect();

            foreach ($codesToTry as $code) {
                $queryTry = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->where('container_number', 'LIKE', "%{$code}%")
                    ->whereBetween('bijac_date', $dateRange)
                    ->latestBetween($dateRange);

                $result = $queryTry->get();

                if ($result->isNotEmpty()) {
                    break;
                }
            }

            if ($result->isNotEmpty()) {
                break;
            }
        }

        return $result;
    }
}
