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
                $baseQuery = Bijac::select('id', 'plate', 'plate_normal', 'bijac_date')
                    ->doesntHave('PlateTraffics');
                // ->whereBetween('bijac_date', $dateRange);
                $dd = '';
                foreach ($checklist as $field) {
                    $plate = $Traffic->$field;
                    if (!$plate) continue;

                    $result = (clone $baseQuery)
                        ->where('plate_normal', $plate)
                        ->get();
                    $dd = 1;
                    if (!$result->isEmpty()) break;

                    // $normalized = $plateService->normalizePlate($plate);
                    // $result = (clone $baseQuery)
                    //     ->where('plate_normal', $normalized)
                    //     ->get();
                    // $dd = 2;
                    // if (!$result->isEmpty()) break;

                    $cleanNumber = $plateService->extractDigits($plate);

                    if (str_contains($plate, 'L')) {
                        $result = (clone $baseQuery)
                            ->Where('plate_normal', '___,' . $cleanNumber . ',L')
                            ->get();
                        $dd = 3;
                        if (!$result->isEmpty()) break;
                    }

                    // if (strlen($cleanNumber) === 7) {
                    //     $wild = substr($normalized, 0, -2) . '__';
                    //     $result = (clone $baseQuery)
                    //         ->where('plate_normal', 'like', $wild)
                    //         ->get();
                    //     if (!$result->isEmpty()) break;
                    // }

                    $result = (clone $baseQuery)
                        ->where(function ($q) use ($cleanNumber) {
                            $q->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') = ?", [$cleanNumber]);
                            // ->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') = ?", [$cleanNumber])
                        })
                        ->get();
                    $dd = 4;
                    if ($result->isEmpty()) {
                        if (strlen($cleanNumber) > 5) {
                            $partial = substr($cleanNumber, 0, 5);
                            $result = (clone $baseQuery)
                                ->where(function ($q) use ($partial) {
                                    $q->orWhereRaw("REGEXP_REPLACE(plate_normal, '[^0-9]', '') LIKE ?", ["{$partial}%"]);
                                    // ->whereRaw("REGEXP_REPLACE(plate, '[^0-9]', '') LIKE ?", ["{$partial}%"])
                                })
                                ->get();
                            $dd = 5;
                        }

                        if ($result->count() > 1) {
                            $result = $result->filter(function ($value) use ($plate, $cleanNumber, $threshold) {
                                $plate_number = $value->plate_normal;
                                $lev = levenshtein($plate_number, $plate);
                                if ($lev <= $threshold) return true;
                                elseif (str_contains($plate_number, $plate) || str_contains($plate, $plate_number)) return true;

                                $plate = preg_replace('/\D/', '', $plate);
                                $plate_number = preg_replace('/\D/', '', $plate_number);
                                $lev = levenshtein($plate_number, $plate);
                                if ($lev <= $threshold) return true;
                                elseif (str_contains($plate_number, $plate) || str_contains($plate, $plate_number)) return true;

                                $plate = substr($plate, 0, 5);
                                $plate_number = substr($plate_number, 0, 5);
                                if (str_contains($plate_number, $plate) || str_contains($plate, $plate_number)) return true;
                            });
                        }
                    }
                }
                log::build(['driver' => 'single', 'path' => storage_path("logs/TrafficMatch"),])
                    ->info("result finded : (" . $dd . ')' . json_encode($result));

                // return;

                if (!$result->isEmpty()) {
                    $trafficData = $result->mapWithKeys(function ($bijac) {
                        return [$bijac->id => ['type' => 'Plate']];
                    });
                    $Traffic->bijacs()->sync($trafficData);
                    return $result;
                }
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }
}
