<?php

namespace Modules\Traffic\Strategy;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\BijacInvoice\Models\Bijac;

class ContainerFindBijackStrategy
{
    const SEARCH_DAY = [3]; //, 7, 30

    public function match($Traffic)
    {
        try {
            if (!$Traffic->container_code) return false;
            $checklist = [
                "container_code",
                "container_code_edit",
                "container_code_2",
                "container_code_3",
                "container_code_by_bijac",
            ];
            $threshold = config('ocr.field_thresholds.container_code', config('ocr.levenshtein_threshold'));

            foreach (self::SEARCH_DAY as $day) {
                $dateRange = [
                    (clone $Traffic->created_at)->subDays($day),
                    $Traffic->created_at,
                ];
                $baseQuery = Bijac::select('id', 'container_number', 'bijac_date')
                    ->doesntHave('ContainerTraffics')
                    ->whereBetween('bijac_date', $dateRange);

                foreach ($checklist as $field) {
                    $container = $Traffic->$field;
                    if (!$container) continue;

                    $substr = substr($container, 0, 13);
                    $result = (clone $baseQuery)
                        ->where('container_number', 'LIKE', "{$substr}%")
                        ->get();
                    if (!$result->isEmpty()) break;

                    $substr = substr($container, 0, 11);
                    $result = (clone $baseQuery)
                        ->where('container_number', 'LIKE', "{$substr}%")
                        ->get();
                    if (!$result->isEmpty()) break;

                    $cleanNumber = substr(preg_replace('/\D/', '', $container), 0, 6);
                    $result = (clone $baseQuery)
                        ->where('container_number', 'LIKE', "%{$cleanNumber}%")
                        ->get();
                    if ($result->isEmpty()) continue;
                    $result = $result->filter(function ($value) use ($container, $cleanNumber, $threshold) {
                        $container_number = $value->container_number;
                        $lev = levenshtein($container_number, $container);
                        if ($lev <= $threshold) return true;

                        if (
                            strlen($container_number) > 6 &&
                            strlen($container) > 6 &&
                            (
                                str_contains($container_number, $container) ||
                                str_contains($container, $container_number)
                            )
                        ) return true;

                        $container_number = substr(preg_replace('/\D/', '', $container_number), 0, 6);
                        if ($container_number == $cleanNumber) return true;
                    });
                }
                if (!$result->isEmpty()) {
                    $trafficData = $result->mapWithKeys(function ($bijac) {
                        return [$bijac->id => ['type' => 'Container']];
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
