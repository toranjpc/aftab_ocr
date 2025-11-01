<?php

namespace Modules\Traffic\Strategy;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Traffic\Services\PlateService;

class PlateFindBijackStrategy
{
    const SEARCH_DAY = [3]; //, 7, 30

    public function match($Traffic)
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
            $threshold = config('ocr.field_thresholds.plate_number', config('ocr.levenshtein_threshold'));

            foreach (self::SEARCH_DAY as $day) {
                $dateRange = [
                    (clone $Traffic->created_at)->subDays($day),
                    $Traffic->created_at,
                ];
                $baseQuery = Bijac::select('id', 'plate_normal', 'bijac_date')
                    ->doesntHave('PlateTraffics')
                    ->whereBetween('bijac_date', $dateRange);

                foreach ($checklist as $field) {
                    $plate = $Traffic->$field;
                    if (!$plate) continue;

                    $result = (clone $baseQuery)
                        ->where('plate_normal', $plate)
                        ->get();
                    if (!$result->isEmpty()) break;

                    $normalized = $plateService->normalizePlate($plate);
                    $result = (clone $baseQuery)
                        ->where('plate_normal', $normalized)
                        ->get();
                    if (!$result->isEmpty()) break;

                    $cleanNumber = $plateService->extractDigits($plate);
                    if (strlen($cleanNumber) === 7) {
                        $wild = substr($normalized, 0, -2) . '__';
                        $result = (clone $baseQuery)
                            ->where('plate_normal', 'like', $wild)
                            ->get();
                        if (!$result->isEmpty()) break;
                    }

                    if (strlen($cleanNumber) > 5) {
                        $partial = substr($cleanNumber, 0, 5);
                        $result = (clone $baseQuery)
                            ->where(function ($q) use ($partial) {
                                $q->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') LIKE ?", ["{$partial}%"])
                                    ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", ["{$partial}%"]);
                            })
                            ->get();
                        if (!$result->isEmpty()) break;
                    }

                    if (str_contains($normalized, 'L')) {
                        $result = (clone $baseQuery)
                            ->Where('plate_normal', '___,' . $cleanNumber . ',L')
                            ->get();
                        if (!$result->isEmpty()) break;
                    }

                    $result = (clone $baseQuery)
                        ->where(function ($q) use ($cleanNumber) {
                            $q->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') = ?", [$cleanNumber])
                                ->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') = ?", [$cleanNumber]);
                        })
                        ->get();
                    if (!$result->isEmpty()) break;
                }

                if (!$result->isEmpty()) {
                    $trafficData = $result->mapWithKeys(function ($bijac) {
                        return [$bijac->id => ['type' => 'Plate']];
                    });
                    $Traffic->bijacs()->sync($trafficData);
                    return $result;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }
}
