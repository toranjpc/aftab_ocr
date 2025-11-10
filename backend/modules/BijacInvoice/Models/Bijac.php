<?php

namespace Modules\BijacInvoice\Models;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Modules\Ocr\Models\OcrMatch;
use Illuminate\Support\Facades\DB;

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
     * Scope: پیدا کردن بیجک‌های مربوط به یک پلاک (اصلی‌ترین منطق شما)
     */
    public function scopeForPlate($query, $item, $isEdited)
    {
        if ($isEdited && !$item->plate_number_edit) return $query->whereRaw('1=0');
        if (!$isEdited && !$item->plate_number) return $query->whereRaw('1=0');

        $plate_number = $isEdited ? $item->plate_number_edit : $item->plate_number;
        $cleanNumber = preg_replace('/\D/', '', $plate_number);

        if (strlen($cleanNumber) < 3) {
            return $query->whereRaw('1=0');
        }

        foreach (self::SEARCH_DAY as $day) {

            $dateRange = [
                (clone $item->created_at)->subDays($day),
                $item->created_at,
            ];

            // حالت ۱: تطابق مستقیم
            $result = Bijac::when($day > 3, function ($query) {
                return $query->doesntHave('ocrMatches');
            })
                ->where('plate_normal', $plate_number)
                ->whereBetween('bijac_date', $dateRange)
                ->latestBetween($dateRange)
                ->get();

            // حالت ۲: wildcard
            if ($result->isEmpty() && strlen($cleanNumber) == 7) {
                $wildcardPattern = substr($plate_number, 0, -2) . '__';

                $result = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->where('plate_normal', $wildcardPattern)
                    ->whereBetween('bijac_date', $dateRange)
                    ->latestBetween($dateRange)
                    ->get();
            }

            // حالت ۳: شامل L
            if ($result->isEmpty() && str_contains($plate_number, 'L')) {
                $result = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->where('plate_normal', '___,' . $cleanNumber . ',L')
                    ->whereBetween('bijac_date', $dateRange)
                    ->latestBetween($dateRange)
                    ->get();
            }

            // حالت ۴: فقط اعداد
            if ($result->isEmpty()) {
                $result = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->whereRaw(
                        "REGEXP_REPLACE(plate_normal, '[^0-9]', '') = ?",
                        [$cleanNumber]
                    )
                    // ->whereNull('plate_normal')
                    ->whereBetween('bijac_date', $dateRange)
                    ->latestBetween($dateRange)
                    ->get();

                // $result = Bijac::where(
                //     'plate_normal',
                //     $cleanNumber
                // )
                //     ->whereBetween('bijac_date', $dateRange)
                //     ->latestBetween($dateRange)
                //     ->get();
            }

            if ($result->isEmpty() && strlen($cleanNumber) > 5) {
                $wildcardPattern = substr($cleanNumber, 0, 5);

                $result = Bijac::when($day > 3, function ($query) {
                    return $query->doesntHave('ocrMatches');
                })
                    ->whereRaw(
                        "REGEXP_REPLACE(plate_normal, '[^0-9]', '') = ?",
                        [$wildcardPattern]
                    )
                    ->whereBetween('bijac_date', $dateRange)
                    ->latestBetween($dateRange)
                    ->get();

                if ($result->isEmpty()) {
                    $result = Bijac::where(
                        'plate_normal',
                        "LIKE",
                        $cleanNumber
                    )
                        ->whereBetween('bijac_date', $dateRange)
                        ->latestBetween($dateRange)
                        ->get();
                }
            }

            if ($result->isNotEmpty()) {
                break; // اگر چیزی پیدا شد دیگر ادامه ندهیم
            }
        }

        return $result->isNotEmpty()
            ? Bijac::whereIn('id', $result->pluck('id'))
            : $query->whereRaw('1=0');
    }

    public function scopeForContainer($query, $item, $isEdited)
    {
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

            // اگر isEdited=false و کد دوم موجود است
            if (!$isEdited && !empty($item->container_code_2)) {
                $codesToTry[] = $item->container_code_standard2;
            }

            // اضافه کردن 6 رقم اول از کدها
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
                    break; // اگر چیزی پیدا شد دیگر ادامه ندهیم
                }
            }

            if ($result->isNotEmpty()) {
                break; // اگر چیزی پیدا شد دیگر ادامه ندهیم
            }
        }

        return $result;
    }
}
