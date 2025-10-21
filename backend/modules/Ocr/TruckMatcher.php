<?php

namespace Modules\Ocr;

use Carbon\Carbon;
use Modules\Ocr\Jobs\SendLogJob;
use Modules\Ocr\Models\LogReceiver;
use Illuminate\Support\Facades\Log;

use Modules\Collector\Models\CcsData;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Collector\Services\InvoiceService;

class TruckMatcher
{
    public static $gates = [
        1
    ];

    public static function makeSendJobs($logId)
    {
        $receivers = LogReceiver::all();

        foreach ($receivers as $receiver) {
            SendLogJob::dispatch($logId, $receiver);
        }
    }

    public static function plateContainerMatchingWithBuffer($item1, $item2)
    {
        try {
            $diffInSeconds = (new Carbon($item1['data']->log_time))->diffInSeconds($item2['data']->log_time);

            if ($item1['type'] !== $item2['type'] && $diffInSeconds <= 6) {
                if ($item1['type'] === 'container_code') {
                    $result = $item2['data']->update([
                        'parent_id' => $item1['data']->id
                    ]);

                    static::makeSendJobs($item2['data']->id);

                    return $result;
                }

                $result = $item1['data']->update([
                    'parent_id' => $item2['data']->id
                ]);

                static::makeSendJobs($item1['data']->id);

                return $result;
            }

            if ($item1['type'] === $item2['type']) {
                return $item2;
            }

            if (static::containerIs20Foot($item2['data']->container_code)) {
                return $item2;
            }

            if ($item1['type'] !== $item2['type'] && $item1['type'] === 'plate') {
                $result = $item1['data']->update([
                    'parent_id' => $item2['data']->id
                ]);

                static::makeSendJobs($item1['data']->id);

                return $result;
            }
        } catch (\Exception $e) {
            Log::error("err : " . $e);
        }
        return $item2;
    }

    static function setCache($cacheKey)
    {
        return function ($stack) use ($cacheKey) {
            cache()->set($cacheKey, $stack);
        };
    }

    static function getType($item)
    {
        if ($item->plate_number)
            return 'plate';

        if ($item->container_code)
            return 'container_code';

        return false;
    }

    static function containerIs20Foot($containerCode)
    {
        if (!$containerCode)
            return false;

        return str_contains($containerCode, '22G1');
    }





    /* from smart * /

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
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at])
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

            try {
                if (!isset($bijac->tabarInvoice)) {
                    $service = new InvoiceService();
                    $service->getWithReceiptNumber($bijac->receipt_number);
                }
            } catch (\Exeption $exeption) {
            }
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
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at->addDay()])
            ->orderByDesc('bijac_date')
            ->first();

        if (!$bijac && $item->container_code_2) {
            $bijac = Bijac::where('container_number', 'LIKE', '%' . $item->container_code_standard2 . '%')
                ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at->addDay()])
                ->orderByDesc('bijac_date')
                ->first();
        }

        if ($bijac) {
            $bijac = Bijac::where('container_number', $bijac->container_number)
                ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at->addDay()])
                ->orderByDesc('bijac_date')
                ->get();
        } else {
            $code = static::extractDigits($item->container_code);
            $code = substr($code, 0, 6);
            if (strlen($code) > 5)
                $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                    ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at->addDay()])
                    ->orderByDesc('bijac_date')
                    ->get();

            if (!$bijac) {
                $code = static::extractDigits($item->container_code_2);
                $code = substr($code, 0, 6);
                if (strlen($code) > 5)
                    $bijac = Bijac::where('container_number', 'LIKE', '%' . $code . '%')
                        ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at->addDay()])
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
            ->whereBetween('bijac_date', [(clone $item->created_at)->subDays(7), $item->created_at])
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

    public static function ccsMatching($item)
    {
        if (!$item->plate_number)
            return;

        if ($item->plate_type !== 'iran' && $item->plate_type !== 'iranian')
            return;

        $plate = substr($item->plate_number, 0, -2) . '??';

        $ccs = CcsData::where(function ($q) use ($item, $plate) {
            $q
                ->where('vehicle_number_normal', '=', $plate)
                ->orWhere('vehicle_number_normal', '=', $item->plate_number);
        })
            ->whereBetween('request_date', [(clone $item->created_at)->subDays(7), $item->created_at])
            // ->where('revoke_receipt', 0)
            ->orderByDesc('request_date')
            ->first();

        if ($ccs) {
            $allCcsData = CcsData::where(function ($q) use ($item, $plate) {
                $q
                    ->where('vehicle_number_normal', '=', $plate)
                    ->orWhere('vehicle_number_normal', '=', $item->plate_number);
            })
                ->where('request_date', $ccs->request_date)
                ->pluck('id');

            $item->ccsData()->sync($allCcsData);

            if (!isset($ccs->tabarInvoice)) {
                $service = new InvoiceService();
                $service->getWithReceiptNumber($ccs->ReceiptNumber);
            }

            CcsData::where('vehicle_number_normal', '=', $plate)
                ->where('request_date', $ccs->request_date)
                ->update(['revoke_receipt' => 1]);
        }
    }

    public static function findInvoiceByContainerCode($item)
    {
        if (!$item->container_code)
            return;

        $ccs = CcsData::where('ContainerNumber', 'LIKE', '%' . $item->container_code_standard . '%')
            ->whereBetween('request_date', [(clone $item->created_at)->subDays(30), $item->created_at])
            ->orderByDesc('request_date')
            ->first();

        if ($ccs) {
            $allCcsData = CcsData::where('ContainerNumber', 'LIKE', '%' . $item->container_code_standard . '%')
                ->whereBetween('request_date', [(clone $item->created_at)->subDays(30), $item->created_at])
                ->orderByDesc('request_date')
                ->pluck('id');

            $item->ccsData()->sync($allCcsData);

            if (!isset($ccs->tabarInvoice)) {
                $service = new InvoiceService();
                $service->getWithReceiptNumber($ccs->ReceiptNumber);
            }

            CcsData::where('ContainerNumber', 'LIKE', '%' . $item->container_code_standard . '%')
                ->where('request_date', $ccs->request_date)
                ->update(['revoke_receipt' => 1]);
        }
    }

    public static function plateContainerMatching($item)
    {
        $cacheKey = 'match_stack_' . $item->gate_number;
        $cacheKeyTraffic = 'trafic_rate_' . $item->gate_number;
        $stack = cache($cacheKey) ?? [];
        $trafficRates = cache($cacheKeyTraffic) ?? [];
        $cacheSetter = static::setCache($cacheKey);
        $type = static::getType($item);

        if ($type === false)
            return;

        $lastItem = array_pop($stack);

        if (!$lastItem) {
            $stack[] = ['type' => $type, 'data' => $item];

            return $cacheSetter($stack);
        }

        $diffInSeconds = (new Carbon($lastItem['data']->log_time))->diffInSeconds($item->log_time);


        if ($lastItem['type'] !== $type && $diffInSeconds <= 5) {
            if ($type === 'container_code')
                $lastItem['data']->update([
                    'parent_id' => $item->id
                ]);
            else
                $item->update([
                    'parent_id' => $lastItem['data']->id
                ]);

            return $cacheSetter($stack);
        }


        if ($lastItem['type'] === $type) {
            $stack[] = ['type' => $type, 'data' => $item];

            return $cacheSetter($stack);
        }

        if (static::containerIs20Foot($item->container_code) && $diffInSeconds > 5) {
            $stack[] = ['type' => $type, 'data' => $item];

            return $cacheSetter($stack);
        }

        if ($lastItem['type'] !== $type && $lastItem['type'] === 'plate') {
            $lastItem['data']->update([
                'parent_id' => $item->id
            ]);

            return $cacheSetter($stack);
        }

        $stack[] = ['type' => $type, 'data' => $item];

        return $cacheSetter($stack);
    }
   
    */
}
