<?php

namespace Modules\Ocr;

use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

class BijacMatcher
{
    const MATCH_DAY = 3;
    public static function plateMatching($item)
    {
        if (!$item->plate_number) return;

        // if ($item->plate_type !== 'iran' && $item->plate_type !== 'iranian') {
        if (!in_array($item->plate_type, ['iran', 'iranian'], true)) {
            $plate = static::extractDigits($item->plate_number);
            $plate = "%{$plate}%";
            $op = 'LIKE';
            $field = "plate";
        } else {
            $plate = substr($item->plate_number, 0, -2) . '??';
            $op = '=';
            $field = "plate_normal";
        }

        $bijac = Bijac::select('id', 'bijac_date', 'type', 'container_number') //, 'plate_normal'
            ->where(function ($q) use ($item, $plate, $op, $field) {
                $q->where($field, $op, $plate)
                    ->orWhere($field, $op, $item->plate_number);
            })
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), (clone $item->created_at)->addDay()])
            // ->where('revoke_receipt', 0)
            ->orderByDesc('bijac_date')
            ->first();

        if ($bijac) {
            $allBijacs = Bijac::where(function ($q) use ($item, $plate, $op, $field) {
                $q->where($field, $op, $plate)
                    ->orWhere($field, $op, $item->plate_number);
            })
                ->where('bijac_date', $bijac->bijac_date)
                ->pluck('id');

            $item->bijacs()->sync($allBijacs);
            Bijac::whereIn('id', $allBijacs)
                ->update(['revoke_receipt' => 1]);
        }

        if (!$item->container_code && $bijac && $bijac->type !== 'gcoms') {
            $finalContainer = str_replace(' ', '', $bijac->container_number);
            $item->container_code = $finalContainer;
            $item->container_code_2 = null;
            $item->saveQuietly();
        }
    }

    public static function containerMatching($item)
    {
        $bijacs = static::getContainerBijacs($item);

        if (!$bijacs || count($bijacs) === 0)
            return;

        $ids = $bijacs->pluck('id');
        $item->bijacs()->sync($ids);
        Bijac::whereIn('id', $ids)->update(['revoke_receipt' => 1]);

        if (count($bijacs) > 0) {
            $finalContainer = str_replace(' ', '', $bijacs[0]->container_number);
            preg_match('/(\d{2}[A-Z]\d*)$/', $item->container_code, $matches);
            preg_match('/(\d{2}[A-Z]\d*)$/', $item->container_code_2, $matches2);
            $containerTypeGroup = $matches[0] ?? $matches2[0] ?? '';
            $item->container_code = $finalContainer . $containerTypeGroup;
            $item->container_code_2 = null;
            $item->saveQuietly();
        }
    }

    public static function extractDigits($string)
    {
        preg_match_all('/\d+/', $string, $matches);

        return implode('', $matches[0]);
    }

    public static function getContainerBijacsQuery($container_code, $dateRange, $qu = "get", $like = true)
    {
        $bijac =  Bijac::whereBetween('bijac_date', $dateRange)->orderByDesc('bijac_date');

        if ($like) $bijac->where('container_number', 'LIKE', "%{$container_code}%");
        else $bijac->where('container_number', 'LIKE', $container_code);

        if ($qu == "get") return $bijac->get();
        return $bijac->first();
    }
    public static function getContainerBijacs($item)
    {
        if (!$item->container_code)
            return false;

        $dateRange = [
            (clone $item->created_at)->subDays(self::MATCH_DAY),
            (clone $item->created_at)->addDay()
        ];

        $bijac = static::getContainerBijacsQuery($item->container_code_standard, $dateRange);
        if (!$bijac && $item->container_code_2) {
            $bijac = static::getContainerBijacsQuery($item->container_code_standard2, $dateRange);
        }

        if ($bijac) {
            $bijac = static::getContainerBijacsQuery($bijac->container_number, $dateRange, "get", false);

            // $bijac = Bijac::where('container_number', $bijac->container_number)
            //     ->whereBetween('bijac_date', $dateRange)
            //     ->orderByDesc('bijac_date')
            //     ->get();
        } else {
            $code = static::extractDigits($item->container_code);
            $code = substr($code, 0, 6);
            if (strlen($code) > 5) {
                $bijac = static::getContainerBijacsQuery($code, $dateRange, "get");

                // $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                //     ->whereBetween('bijac_date', $dateRange)
                //     ->orderByDesc('bijac_date')
                //     ->get();
            }

            if (!$bijac) {
                $code = static::extractDigits($item->container_code_2);
                $code = substr($code, 0, 6);
                if (strlen($code) > 5) {
                    $bijac = static::getContainerBijacsQuery($code, $dateRange, "get");

                    // $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                    //     ->whereBetween('bijac_date', $dateRange)
                    //     ->orderByDesc('bijac_date')
                    //     ->get();
                }
            }
        }

        return $bijac;
    }

    public static function getPlateBijacs($item)
    {
        if (!$item->plate_number)
            return false;

        if ($item->plate_type !== 'iran' && $item->plate_type !== 'iranian') {
            $plate = static::extractDigits($item->plate_number);
            $plate = "%{$plate}%";
            $op = 'LIKE';
            $field = "plate";
        } else {
            $plate = substr($item->plate_number, 0, -2) . '??';
            $op = '=';
            $field = "plate_normal";
        }

        $bijac = Bijac::where(function ($q) use ($item, $plate, $op, $field) {
            $q
                ->where($field, $op, $plate)
                ->orWhere($field, $op, $item->plate_number);
        })
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at])
            // ->where('revoke_receipt', 0)
            ->orderByDesc('bijac_date')
            ->first();

        if ($bijac) {
            $bijac = Bijac::where(function ($q) use ($item, $plate, $op, $field) {
                $q
                    ->where($field, $op, $plate)
                    ->orWhere($field, $op, $item->plate_number);
            })
                ->where('bijac_date', $bijac->bijac_date)
                ->get();
        }

        return $bijac;
    }

    public static function bijacMatching($item, $firstTime = false)
    {
        if (!!$item->container_code) {
            if ($firstTime) {
                static::plateMatching($item);
            }
            static::containerMatching($item);
        } else {
            static::plateMatching($item);
        }


        if ($item->gate_number == 3) {
            log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog"),])
                ->info("BijacMatcher proccesed ({$item->id}) by palte : {$item->plate_number}  ");
        }
    }
}
