<?php

namespace Modules\Traffic\Strategy;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Traffic\Services\PlateService;

class PlateFindBijackStrategy
{
    const SEARCH_DAY = [3]; //, 7, 30

    public function match($Traffic): bool
    {
        try {
            if (!$Traffic->plate_number) return false;
            $plateService = new PlateService();
            $checklist = [
                'plate_number',
                'plate_number_edit',
                'plate_number_2',
                'plate_number_by_bijac'
            ];

            foreach (self::SEARCH_DAY as $day) {
                $dateRange = [
                    (clone $Traffic->created_at)->subDays($day),
                    $Traffic->created_at,
                ];

                foreach ($checklist as $field) {
                    $plate = $Traffic->$field;
                    if (!$plate) continue;

                    $normalized = $plateService->normalizePlate($plate);
                    $cleanNumber = $plateService->extractDigits($plate);

                    // ۱. تطابق مستقیم
                    $result = $this->latestBetween(
                        Bijac::where('plate_normal', $normalized)
                            ->whereBetween('bijac_date', $dateRange),
                        $dateRange
                    )->first();
                    if ($result) return $result;

                    // ۲. وایلدکارد دو رقم آخر
                    if (strlen($cleanNumber) === 7) {
                        $wild = substr($normalized, 0, -2) . '??';
                        $result = $this->latestBetween(
                            Bijac::where('plate_normal', $wild)
                                ->whereBetween('bijac_date', $dateRange),
                            $dateRange
                        )->first();
                        if ($result) return $result;
                    }

                    // ۳. پلاک افغان (حاوی L)
                    if (str_contains($normalized, 'L')) {
                        $result = $this->latestBetween(
                            Bijac::where(function ($q) use ($normalized, $cleanNumber) {
                                $q->where('plate_normal', $normalized)
                                    ->orWhere('plate_normal', '???,' . $cleanNumber . ',L');
                            })->whereBetween('bijac_date', $dateRange),
                            $dateRange
                        )->first();
                        if ($result) return $result;
                    }

                    // ۴. فقط اعداد
                    $result = $this->latestBetween(
                        Bijac::where(function ($q) use ($cleanNumber) {
                            $q->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') = ?", [$cleanNumber])
                                ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') = ?", [$cleanNumber]);
                        })->whereBetween('bijac_date', $dateRange),
                        $dateRange
                    )->first();
                    if ($result) return $result;

                    // ۵. تطبیق جزئی
                    if (strlen($cleanNumber) > 5) {
                        $partial = substr($cleanNumber, 0, 5);
                        $result = $this->latestBetween(
                            Bijac::where(function ($q) use ($partial) {
                                $q->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') LIKE ?", ["{$partial}%"])
                                    ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", ["{$partial}%"]);
                            })->whereBetween('bijac_date', $dateRange),
                            $dateRange
                        )->first();
                        if ($result) return $result;
                    }
                }
            }

            $trafficData = $result->mapWithKeys(function ($bijac) {
                return [$bijac->id => ['type' => 'Plate']];
            });
            $Traffic->bijacs()->sync($trafficData);
            return false;
            // $plateType = $PlateService->determinePlateType($Traffic->plate_number);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    public function latestBetween($query, array $dateRange)
    {
        $maxDate = (clone $query)
            ->whereBetween('bijac_date', $dateRange)
            ->max('bijac_date');
        if (!$maxDate) return $query->whereRaw('1=0');
        return $query->where('bijac_date', $maxDate);
    }
}
