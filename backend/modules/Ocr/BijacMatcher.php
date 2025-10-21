<?php

namespace Modules\Ocr;

use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Services\InvoiceService;

class BijacMatcher
{
    const MATCH_DAY = 3;// تعداد روز های جست و جو برای پیدا کردن بیجک
    public static function plateMatching($item)
    {
        if (!$item->plate_number)
            return;

        if ($item->plate_type !== 'iran' && $item->plate_type !== 'iranian') {
            $plate = static::extractDigits($item->plate_number);
            $plate = "%{$plate}%";
            $op = 'LIKE';
            $field = "plate";
        } else {
            $op = '=';
            $field = "plate_normal";
            $plate = substr($item->plate_number, 0, -2) . '??';
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
            $allBijacs = Bijac::where(function ($q) use ($item, $plate, $op, $field) {
                $q
                    ->where($field, $op, $plate)
                    ->orWhere($field, $op, $item->plate_number);
            })
                ->where('bijac_date', $bijac->bijac_date)
                ->pluck('id');

            $item->bijacs()->sync($allBijacs);

            // try {
            //     if (!isset($bijac->tabarInvoice)) {
            //         $service = new InvoiceService();
            //         $service->getWithReceiptNumber($bijac->receipt_number);
            //     }
            // } catch (\Exeption $exeption) {

            // }

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

    public static function extractDigits($string)
    {
        preg_match_all('/\d+/', $string, $matches);

        return implode('', $matches[0]);
    }

    public static function getContainerBijacs($item)
    {
        if (!$item->container_code)
            return false;

        $bijac = Bijac::where('container_number', 'LIKE', '%' . $item->container_code_standard . '%')
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at->addDay()])
            ->orderByDesc('bijac_date')
            ->first();

        if (!$bijac && $item->container_code_2) {
            $bijac = Bijac::where('container_number', 'LIKE', '%' . $item->container_code_standard2 . '%')
                ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at->addDay()])
                ->orderByDesc('bijac_date')
                ->first();
        }

        if ($bijac) {
            $bijac = Bijac::where('container_number', $bijac->container_number)
                ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at->addDay()])
                ->orderByDesc('bijac_date')
                ->get();
        } else {
            $code = static::extractDigits($item->container_code);
            $code = substr($code, 0, 6);
            if (strlen($code) > 5)
                $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                    ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at->addDay()])
                    ->orderByDesc('bijac_date')
                    ->get();

            if (!$bijac) {
                $code = static::extractDigits($item->container_code_2);
                $code = substr($code, 0, 6);
                if (strlen($code) > 5)
                    $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                        ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(self::MATCH_DAY), $item->created_at->addDay()])
                        ->orderByDesc('bijac_date')
                        ->get();
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
            $op = '=';
            $field = "plate_normal";
            $plate = substr($item->plate_number, 0, -2) . '??';
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

    public static function containerMatching($item)
    {
        $bijacs = static::getContainerBijacs($item);

        if (!$bijacs || count($bijacs) === 0)
            return;

        $item->bijacs()->sync($bijacs->pluck('id'));

        // foreach ($bijacs as $bijac) {
        //     if (!isset($bijac->invoice)) {
        //         try {
        //             $service = new InvoiceService();
        //             $service->getWithReceiptNumber($bijac->receipt_number);
        //         } catch (\Exception $e) {
        //         }
        //     }
        // }

        Bijac::whereIn('id', $bijacs->pluck('id'))
            ->update(['revoke_receipt' => 1]);

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

    public static function bijacMatching($item, $firstTime = false)
    {
        if (!!$item->container_code) {
            if ($firstTime) {
                static::plateMatching($item);
            }
            static::containerMatching($item);
        } else
            static::plateMatching($item);
    }
}
