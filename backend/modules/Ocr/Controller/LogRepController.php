<?php

namespace Modules\Ocr\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Mpdf\Tag\Tr;
use Modules\BijacInvoice\Models\Customer;
use PhpParser\Node\Expr\AssignOp\Minus;

class LogRepController extends Controller
{
    public function test(Request $request)
    {
        // ساخت زیرکوئری اولیه از ocr_logs به همراه JOIN های لازم
        $subQuery = OcrLog::query()
            ->leftJoin('bijacables', function ($join) {
                $join->on('ocr_logs.id', '=', 'bijacables.bijacable_id')
                    ->where('bijacables.bijacable_type', '=', 'Modules\Ocr\Models\OcrLog');
            })
            ->leftJoin('bijacs', 'bijacables.bijac_id', '=', 'bijacs.id')
            ->filter(); // فرض می‌کنیم scope filter() دارید

        // اعمال شرط‌های _has و _doesnt_have
        if ($request->has('_has')) {
            if ($request['_has'] === 'bijacs') {
                // رکوردهایی که ارتباط bijacs دارند
                $subQuery->whereNotNull('bijacables.bijac_id');
            } elseif ($request['_has'] === 'bijacs.invoice') {
                // اتصال به جدول invoices و فقط رکوردهایی که invoice دارند
                $subQuery->leftJoin('invoices', 'bijacs.receipt_number', '=', 'invoices.receipt_number')
                    ->whereNotNull('invoices.receipt_number');
            }
        } elseif ($request->has('_doesnt_have')) {
            if ($request['_doesnt_have'] === 'bijacs') {
                // رکوردهایی که ارتباط bijacs ندارند
                $subQuery->whereNull('bijacables.bijac_id');
            } elseif ($request['_doesnt_have'] === 'bijacs.invoice') {
                // اتصال به جدول invoices و رکوردهایی که invoice ندارند
                $subQuery->leftJoin('invoices', 'bijacs.receipt_number', '=', 'invoices.receipt_number')
                    ->whereNull('invoices.receipt_number');
            }
        }

        // انتخاب ستون‌های مورد نیاز جهت شمارش؛
        // فرض بر این است که برای هر ocr_log، این مقادیر یکتا هستند.
        $subQuery->select(
            'ocr_logs.id',
            'ocr_logs.container_size',
            'ocr_logs.plate_type',
            'ocr_logs.gate_number',
            'bijacs.type'
        )
            ->distinct();

        // استفاده از زیرکوئری به عنوان یک جدول موقت (derived table)
        $derivedTable = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery->getQuery());

        // انجام شمارش‌های مورد نظر روی رکوردهای یکتا
        $counts = $derivedTable->selectRaw('
                COUNT(*) AS `all`,

                COUNT(CASE WHEN gate_number = 1 THEN 1 END) AS `gate_1`,
                COUNT(CASE WHEN gate_number = 2 THEN 1 END) AS `gate_2`,
                COUNT(CASE WHEN gate_number = 3 THEN 1 END) AS `gate_3`,
                COUNT(CASE WHEN gate_number = 4 THEN 1 END) AS `gate_4`,

            ')->first();
        // COUNT(CASE WHEN container_size = "40f" THEN 1 END) AS `40f`,
        // COUNT(CASE WHEN container_size = "20f" THEN 1 END) AS `20f`,
        // COUNT(CASE WHEN container_size = "unknown" THEN 1 END) AS `unknown`,
        // COUNT(CASE WHEN container_size IS NULL THEN 1 END) AS `fale`,
        // COUNT(CASE WHEN plate_type = "iran" THEN 1 END) AS `iran`,
        // COUNT(CASE WHEN plate_type = "afghan" THEN 1 END) AS `afghan`,
        // COUNT(CASE WHEN plate_type = "iran-regular" THEN 1 END) AS `regular`,
        // COUNT(CASE WHEN plate_type = "europe" THEN 1 END) AS `europe`,
        // COUNT(CASE WHEN type = "gcoms" THEN 1 END) AS `gcoms_count`,
        // COUNT(CASE WHEN type = "ccs" THEN 1 END) AS `container`,
        // COUNT(CASE WHEN type IS NULL OR type NOT IN ("ccs", "gcoms") THEN 1 END) AS `no_bijac`
        return response()->json($counts);
    }
    public function index(Request $request)
    {
        try {
            // ساخت زیرکوئری اولیه از ocr_logs به همراه JOIN های لازم
            $subQuery = OcrMatch::query()
                ->leftJoin('bijacables', function ($join) {
                    $join->on('ocr_matches.id', '=', 'bijacables.bijacable_id')
                        ->where('bijacables.bijacable_type', '=', 'Modules\Ocr\Models\OcrMatch');
                })
                ->leftJoin('bijacs', 'bijacables.bijac_id', '=', 'bijacs.id')
                ->filter();

            // if ($request->has('date_filter')) {
            //     $now = now();

            //     switch ($request->input('date_filter')) {
            //         case 'today':
            //             $subQuery->whereDate('ocr_logs.created_at', $now->toDateString());
            //             break;

            //         case 'this_week':
            //             $subQuery->whereBetween('ocr_logs.created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            //             break;

            //         case 'this_month':
            //             $subQuery->whereBetween('ocr_logs.created_at', [$now->startOfMonth(), $now->endOfMonth()]);
            //             break;

            //         case 'last_hour':
            //             $subQuery->where('ocr_logs.created_at', '>=', $now->subHour());
            //             break;
            //     }
            // }

            // اعمال شرط‌های _has و _doesnt_have
            if ($request->has('_has')) {
                if ($request['_has'] === 'bijacs') {
                    // رکوردهایی که ارتباط bijacs دارند
                    $subQuery->whereNotNull('bijacables.bijac_id');
                } elseif ($request['_has'] === 'bijacs.invoice') {
                    // اتصال به جدول invoices و فقط رکوردهایی که invoice دارند
                    $subQuery->leftJoin('invoices', 'bijacs.receipt_number', '=', 'invoices.receipt_number')
                        ->whereNotNull('invoices.receipt_number');
                }
            } elseif ($request->has('_doesnt_have')) {
                if ($request['_doesnt_have'] === 'bijacs') {
                    // رکوردهایی که ارتباط bijacs ندارند
                    $subQuery->whereNull('bijacables.bijac_id');
                } elseif ($request['_doesnt_have'] === 'bijacs.invoice') {
                    // اتصال به جدول invoices و رکوردهایی که invoice ندارند
                    $subQuery->leftJoin('invoices', 'bijacs.receipt_number', '=', 'invoices.receipt_number')
                        ->whereNull('invoices.receipt_number');
                }
            }

            // انتخاب ستون‌های مورد نیاز جهت شمارش؛ فرض بر این است که برای هر ocr_log، این مقادیر یکتا هستند.
            $subQuery->select(
                'ocr_matches.id',
                'ocr_matches.log_time',
                'ocr_matches.container_size',
                'ocr_matches.plate_type',
                'ocr_matches.gate_number',
                'ocr_matches.created_at',
                'bijacs.type'
            )->distinct();

            // دریافت SQL و binding های زیرکوئری
            $subSql = $subQuery->toSql();
            $bindings = $subQuery->getBindings();

            $oneHourAgo = now()->subHour();
            if (in_array($request->gate_number, ['2', '3', '4'])) $qq = "COUNT(CASE WHEN gate_number IN (2,3,4) THEN 1 END) AS `gate_collection`,";
            else $qq = "COUNT(CASE WHEN gate_number IN (1) THEN 1 END) AS `gate_collection`,";
            $counts = DB::table(DB::raw("($subSql) as sub"))
                ->setBindings($bindings)
                ->selectRaw("{$qq}
                    COUNT(CASE WHEN gate_number = '{$request->gate_number}' THEN 1 END) AS `gate_count`,COUNT(CASE WHEN gate_number = '{$request->gate_number}' AND created_at >= '" . $oneHourAgo . "' THEN 1 END) AS `gate_count_last_hour`
                    ")
                ->first();
            // COUNT(*) AS `all`,
            // COUNT(CASE WHEN container_size = "40f" THEN 1 END) AS `40f`,
            // COUNT(CASE WHEN container_size = "20f" THEN 1 END) AS `20f`,
            // COUNT(CASE WHEN container_size = "unknown" THEN 1 END) AS `unknown`,
            // COUNT(CASE WHEN container_size IS NULL THEN 1 END) AS `fale`,
            // COUNT(CASE WHEN plate_type = "iran" THEN 1 END) AS `iran`,
            // COUNT(CASE WHEN plate_type = "afghan" THEN 1 END) AS `afghan`,
            // COUNT(CASE WHEN plate_type = "iran-regular" THEN 1 END) AS `regular`,
            // COUNT(CASE WHEN gate_number = 1 THEN 1 END) AS `gate_1`,
            // COUNT(CASE WHEN gate_number = 2 THEN 1 END) AS `gate_2`,
            // COUNT(CASE WHEN gate_number = 3 THEN 1 END) AS `gate_3`,
            // COUNT(CASE WHEN gate_number = 4 THEN 1 END) AS `gate_4`,
            // COUNT(CASE WHEN plate_type = "europe" THEN 1 END) AS `europe`,
            // COUNT(CASE WHEN type = "gcoms" THEN 1 END) AS `gcoms_count`,
            // COUNT(CASE WHEN type = "ccs" THEN 1 END) AS `container`,
            // COUNT(CASE WHEN type IS NULL OR type NOT IN ("ccs", "gcoms") THEN 1 END) AS `no_bijac`

            // استفاده جداگانه از زیرکوئری برای نمودار بر اساس ساعت (chart)
            $chart = DB::table(DB::raw("($subSql) as sub"))
                ->setBindings($bindings)
                ->select(DB::raw('DATE_FORMAT(log_time, "%H") AS x, COUNT(*) AS y'))
                ->groupBy('x')
                ->orderBy('x')
                ->get();

            // استفاده جداگانه از زیرکوئری برای نمودار بر اساس تاریخ (chart2)
            $chart2 = DB::table(DB::raw("($subSql) as sub"))
                ->setBindings($bindings)
                ->select(DB::raw('DATE_FORMAT(log_time, "%Y-%m-%d") AS x, COUNT(*) AS y'))
                ->groupBy('x')
                ->orderBy('x')
                ->get();

            // (در صورت نیاز تبدیل تاریخ به فرمت شمسی برای chart2)
            foreach ($chart2 as $item) {
                $item->x = Verta::instance($item->x)->format('m-d');
            }
            return response()->json([
                'message' => 'ذخیره شد',
                'counts'  => $counts,
                'chart'   => $chart,
                'chart2'  => $chart2,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    public function gateCounter(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $oneHourAgo = now()->subHour();
            if (in_array($request->gate_number, ['2', '3', '4'])) {
                $qq = "COUNT(CASE WHEN gate_number IN (2,3,4) THEN 1 END) AS `gate_collection`,";
            } else {
                $qq = "COUNT(CASE WHEN gate_number IN (1) THEN 1 END) AS `gate_collection`,";
            }

            $counts = OcrMatch::selectRaw("{$qq}
                    COUNT(CASE WHEN gate_number = '{$request->gate_number}' THEN 1 END) AS `gate_count`,
                    COUNT(CASE WHEN gate_number = '{$request->gate_number}' AND created_at >= '" . $oneHourAgo . "' THEN 1 END) AS `gate_count_last_hour`                ")
                ->whereDate("created_at", ">=", $today)
                ->first();
            return response()->json($counts, Response::HTTP_OK);
        } catch (\Throwable $th) {
            throw $th;
        }
    }






    public function makeReport(Request $request)
    {
        $startTime = microtime(true);
        $inputData = $request->all();

        // try {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $dateRange = $request->input('dateRange', 'today');
        switch ($dateRange) {
            case '1hour':
                $dateTo = now()->format('Y-m-d H:i:s');
                $dateFrom = now()->subHour()->format('Y-m-d H:i:s');
                break;
            case 'today':
                $dateFrom = now()->startOfDay()->format('Y-m-d H:i:s');
                $dateTo = now()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'week':
                $dateFrom = now()->startOfWeek()->format('Y-m-d H:i:s');
                $dateTo = now()->endOfWeek()->format('Y-m-d H:i:s');
                break;
            case 'month':
                $dateFrom = now()->startOfMonth()->format('Y-m-d H:i:s');
                $dateTo = now()->endOfMonth()->format('Y-m-d H:i:s');
                break;
            case 'custom':
                // Use provided dates
                break;
            default:
                $dateFrom = now()->startOfDay()->format('Y-m-d H:i:s');
                $dateTo = now()->endOfDay()->format('Y-m-d H:i:s');
                break;
        }

        $query = OcrMatch::query()
            ->with("bijacs.invoices.customer")
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->has('gate') && !empty($request->gate)) {
            $gates = is_array($request->gate) ? $request->gate : explode(',', $request->gate);
            $query->whereIn('ocr_matches.gate_number', $gates);
        }

        if ($request->has('cargo_type') && !empty($request->cargo_type)) {
            $cargoTypes = is_array($request->cargo_type) ? $request->cargo_type : explode(',', $request->cargo_type);
            $query->whereHas('bijacs', function ($bq) use ($cargoTypes) {
                if (in_array('bulk', $cargoTypes) && in_array('container', $cargoTypes)) {
                    // Both types - no filter needed
                } elseif (in_array('bulk', $cargoTypes)) {
                    $bq->whereNull('container_number');
                } elseif (in_array('container', $cargoTypes)) {
                    $bq->whereNotNull('container_number');
                }
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $status) {
                    switch ($status) {
                        case 'gcoms_ok':
                            $q->orWhere(function ($sq) {
                                $sq->whereHas('bijacs', function ($bq) {
                                    $bq->where('type', 'gcoms')
                                        ->whereHas('invoices');
                                });
                            });
                            break;
                        case 'gcoms_nok':
                            $q->orWhere(function ($sq) {
                                $sq->whereHas('bijacs', function ($bq) {
                                    $bq->where('type', 'gcoms')
                                        ->doesntHave('invoices');
                                });
                            });
                            break;
                        case 'container_ccs_ok':
                            $q->orWhere(function ($sq) {
                                $sq->whereNotNull('container_code')
                                    ->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->whereHas('invoices');
                                    });
                            });
                            break;
                        case 'container_ccs_nok':
                            $q->orWhere(function ($sq) {
                                $sq->whereNotNull('container_code')
                                    ->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->doesntHave('invoices');
                                    });
                            });
                            break;
                        case 'container_without_bijac':
                            $q->orWhere(function ($sq) {
                                $sq->whereNotNull('container_code')
                                    ->doesntHave('bijacs');
                            });
                            break;
                        case 'plate_without_bijac':
                            $q->orWhere(function ($sq) {
                                $sq->whereNull('container_code')
                                    ->doesntHave('bijacs');
                            });
                            break;
                        case 'plate_ccs_ok':
                            $q->orWhere(function ($sq) {
                                $sq->whereNull('container_code')
                                    ->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->whereHas('invoices');
                                    });
                            });
                            break;
                        case 'plate_ccs_nok':
                            $q->orWhere(function ($sq) {
                                $sq->whereNull('container_code')
                                    ->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->doesntHave('invoices');
                                    });
                            });
                            break;
                        case 'ccs_ok':
                            $q->orWhere(function ($sq) {
                                $sq->whereHas('bijacs', function ($bq) {
                                    $bq->where('type', 'ccs')
                                        ->whereHas('invoices');
                                });
                            });
                            break;
                        case 'ccs_nok':
                            $q->orWhere(function ($sq) {
                                $sq->whereHas('bijacs', function ($bq) {
                                    $bq->where('type', 'ccs')
                                        ->doesntHave('invoices');
                                });
                            });
                            break;
                    }
                }
            });
        }

        if ($request->has('danger_type') && !empty($request->danger_type)) {
            $dangerTypes = is_array($request->danger_type) ? $request->danger_type : explode(',', $request->danger_type);
            $query->where(function ($q) use ($dangerTypes) {
                foreach ($dangerTypes as $dangerType) {
                    switch ($dangerType) {
                        case 'danger_AI':
                            $q->orWhere('IMDG', '!=', '');
                            break;
                        case 'no_danger_AI':
                            $q->orWhereNull('IMDG')
                                ->orWhere('IMDG', '');
                            break;
                        case 'danger_Bijac':
                            $q->orWhereHas('bijacs', function ($bq) {
                                $bq->whereNotNull('dangerous_code');
                            });
                            break;
                        case 'no_danger_Bijac':
                            $q->orWhereDoesntHave('bijacs', function ($bq) {
                                $bq->whereNotNull('dangerous_code');
                            });
                            break;
                    }
                }
            });
            // return $query->toSql();
        }

        if ($request->has('plate_number') && !empty($request->plate_number)) {
            $plate_number = $request->plate_number;
            // $plate_number = str_replace("_", "", $plate_number);
            $query->whereHas('bijacs', function ($q) use ($plate_number) {
                $q->where('plate_normal', 'LIKE', '%' . $plate_number . '%');
            });

            // $query->where(function ($q) use ($plate_number) {
            //     $q->where('ocr_matches.plate_number', 'LIKE', '%' . $plate_number . '%')
            //         ->orWhere('ocr_matches.plate_number_edit', 'LIKE', '%' . $plate_number . '%')
            //         ->orWhere('ocr_matches.plate_number_3', 'LIKE', '%' . $plate_number . '%');
            // });
        }

        if ($request->has('container_code') && !empty($request->container_code)) {
            $container_code = $request->container_code;
            // $container_code = str_replace("_", "", $container_code);
            $query->whereHas('bijacs', function ($q) use ($container_code) {
                $q->where('container_number', 'LIKE', '%' . $container_code . '%');
            });

            // $query->where(function ($q) use ($container_code) {
            //     $q->where('ocr_matches.container_code', 'LIKE', '%' . $container_code . '%')
            //         ->orWhere('ocr_matches.container_code_edit', 'LIKE', '%' . $container_code . '%')
            //         ->orWhere('ocr_matches.container_code_3', 'LIKE', '%' . $container_code . '%');
            // });
        }

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $customerIds = is_array($request->customer_id) ? $request->customer_id : explode(',', $request->customer_id);
            $query->whereHas('bijacs.invoices.customer', function ($cq) use ($customerIds) {
                $cq->whereIn('id', $customerIds);
            });
        }

        if ($request->has('warehouse_bill') && !empty($request->warehouse_bill)) {
            $query->whereHas('bijacs', function ($bq) use ($request) {
                $bq->where('receipt_number', 'LIKE', '%' . $request->warehouse_bill . '%');
            });
        }

        if ($request->has('bijak_number') && !empty($request->bijak_number)) {
            $query->whereHas('bijacs', function ($bq) use ($request) {
                $bq->where('bijac_number', 'LIKE', '%' . $request->bijak_number . '%');
            });
        }

        // return $query->toSql();

        $chartQuery = clone $query;
        // $perPage = $request->input('per_page', 10);
        // $results = $query->paginate($perPage);

        $DATA = [
            'total_traffic' => 0,
            'with_bijac' => 0,
            'with_invoice' => 0,
            'container_count' => 0,
            'bulk_count' => 0,
            'danger_AI' => 0,
            'danger_Bijac' => 0,
        ];
        $chartQuery = $chartQuery->get();
        foreach ($chartQuery as $key => $value) {
            $firstBijac = $value->bijacs->first();
            $firstInvoice = $firstBijac ? $firstBijac->invoices->first() : null;
            // $firstCustomer = $firstInvoice ? $firstInvoice->customer : null;
            $unit = 1;
            if (!$firstBijac && $value->container_code && 0) { //!$value->plate_number && !$value->plate_number_3
                $prevItem = $chartQuery[$key - 1];
                if (!$prevItem->bijacs->first() && $prevItem->plate_number) {
                    $unit = 0;
                } else {
                    $nextItem = $chartQuery[$key + 1];
                    if (
                        (!$nextItem->bijacs->first() && $nextItem->plate_number) &&
                        abs($nextItem->created_at->timestamp - $value->created_at->timestamp) <= 30
                    ) {
                        $unit = 0;
                    }
                }
            }

            $cargoType = '';
            $cargoType = '';
            if ($firstBijac) {
                $DATA['with_bijac'] += $unit;

                if (!empty($firstBijac->container_number)) {
                    $cargoType = 'container';
                    $DATA['container_count'] += $unit;
                } else {
                    $cargoType = 'bulk';
                    $DATA['bulk_count'] += $unit;
                    // return $value;
                }
            }
            if ($firstInvoice) $DATA['with_invoice'] += $unit;

            if (!isset($DATA['gate_count'][$value->gate_number])) $DATA['gate_count'][$value->gate_number] = 0;
            $DATA['gate_count'][$value->gate_number] += $unit;

            $bijacType = $firstBijac ? $firstBijac->type : null;
            $hasInvoice = $firstInvoice ? true : false;
            $status = 'ccs_nok'; // Default
            if ($bijacType === 'gcoms' && $hasInvoice) {
                $status = 'gcoms_ok';
            } elseif ($bijacType === 'gcoms' && !$hasInvoice) {
                $status = 'gcoms_nok';
            } elseif ($cargoType === 'container' && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'container_ccs_ok';
            } elseif ($cargoType === 'container' && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'container_ccs_nok';
            } elseif ($cargoType === 'container' && !$bijacType) {
                $status = 'container_without_bijac';
            } elseif ($cargoType === 'bulk' && !$bijacType) {
                $status = 'plate_without_bijac';
            } elseif ($cargoType === 'bulk' && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'plate_ccs_ok';
            } elseif ($cargoType === 'bulk' && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'plate_ccs_nok';
            } elseif ($bijacType === 'ccs' && $hasInvoice) {
                $status = 'ccs_ok';
            }
            if (!isset($DATA['status_count'][$status])) $DATA['status_count'][$status] = 0;
            $DATA['status_count'][$status] += $unit;


            if (!empty($value->IMDG)) $DATA['danger_AI'] += $unit;
            if ($firstBijac && !empty($firstBijac->dangerous_code)) $DATA['danger_Bijac'] += $unit;
        }
        $DATA["total_traffic"] = $chartQuery->count();
        $DATA["request"] = $request->all();

        /*
            $results = collect([
                // "request" => $request->all(),
                // "SQL" => vsprintf(str_replace('?', "'%s'", $query->toSql()), $query->getBindings()),
                "data" => $results->items(),
                "total_traffic" => $results->total(),
                "current_page" => $results->currentPage(),
                "per_page" => $results->perPage(),
                "total_pages" => $results->lastPage(),
            ]);
            */


        $response = response()->json([
            'success' => true,
            'chart' => $DATA,
            // 'table' => $results,
            'message' => 'گزارش تردد با موفقیت تولید شد'
        ], Response::HTTP_OK);
        return $response;




        return $transformedResults = $results->getCollection()->map(function ($item) {
            // Determine cargo type
            $cargoType = !empty($item->container_code) ? 'container' : 'bulk';

            // Get first bijac relationship (if exists)


            // Determine danger type
            $hasDangerAI = !empty($item->IMDG);
            $hasDangerBijac = $firstBijac && !empty($firstBijac->dangerous_code);

            if ($hasDangerAI || $hasDangerBijac) {
                $dangerType = 'danger_AI'; // Prioritize AI detection
            } elseif ($hasDangerBijac) {
                $dangerType = 'danger_Bijac';
            } else {
                $dangerType = $hasDangerAI ? 'no_danger_AI' : 'no_danger_Bijac';
            }

            // Determine status
            $bijacType = $firstBijac ? $firstBijac->type : null;
            $hasInvoice = $firstInvoice ? true : false;
            $status = 'ccs_nok'; // Default
            if ($bijacType === 'gcoms' && $hasInvoice) {
                $status = 'gcoms_ok';
            } elseif ($bijacType === 'gcoms' && !$hasInvoice) {
                $status = 'gcoms_nok';
            } elseif ($cargoType === 'container' && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'container_ccs_ok';
            } elseif ($cargoType === 'container' && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'container_ccs_nok';
            } elseif ($cargoType === 'container' && !$bijacType) {
                $status = 'container_without_bijac';
            } elseif ($cargoType === 'bulk' && !$bijacType) {
                $status = 'plate_without_bijac';
            } elseif ($cargoType === 'bulk' && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'plate_ccs_ok';
            } elseif ($cargoType === 'bulk' && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'plate_ccs_nok';
            } elseif ($bijacType === 'ccs' && $hasInvoice) {
                $status = 'ccs_ok';
            }

            return [
                'id' => $item->id,
                'gate_number' => $item->gate_number,
                'cargo_type' => $cargoType,
                'date' => $item->created_at->format('Y-m-d'),
                'plate_number' => $item->plate_number,
                'container_code' => $item->container_code,
                'danger_type' => $dangerType,
                'bijac_number' => $firstBijac ? $firstBijac->bijac_number : null,
                'warehouse_bill' => $firstBijac ? $firstBijac->receipt_number : null,
                'customer_name' => $firstCustomer ? $firstCustomer->title : null,
                // 'total_traffic' => 1, // Each record represents 1 traffic
                // 'success_traffic' => $hasInvoice ? 1 : 0,
                // 'failed_traffic' => $hasInvoice ? 0 : 1,
                // 'avg_processing_time' => null, // Not implemented in current schema
                'status' => $status,
                'log_time' => $item->log_time,
                'vehicle_image_front_url' => $item->vehicle_image_front_url,
                'vehicle_image_back_url' => $item->vehicle_image_back_url,
                'plate_image_url' => $item->plate_image_url
            ];
        });
        // Generate chart data (raw numbers)
        $chartData = $this->generateChartData($chartQuery);

        $response = response()->json([
            'success' => true,
            'data' => [
                'table' => $results, // Paginated table data
                'chart' => [] // Raw numbers for chart
            ],
            'message' => 'گزارش تردد با موفقیت تولید شد'
        ], Response::HTTP_OK);

        $this->logFunctionExecution('makeReport', $inputData, $response, microtime(true) - $startTime);

        // return [
        //     "request" => $request->all(),
        //     "SQL" => vsprintf(str_replace('?', "'%s'", $query->toSql()), $query->getBindings()),
        //     "count" => $query->get()->count()
        // ];

        return $response;
        // } catch (\Throwable $th) {
        //     $response = response()->json([
        //         'success' => false,
        //         'message' => 'خطا در تولید گزارش: ' . $th->getMessage(),
        //         'data' => null
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);

        //     $this->logFunctionExecution('makeReport', $inputData, $response, microtime(true) - $startTime);
        //     return $response;
        // }
    }

    public function getChartData(Request $request)
    {
        $startTime = microtime(true);
        $inputData = $request->all();

        try {
            // Parse date range (similar to makeReport)
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $now = now();
            if (!$dateFrom || !$dateTo) {
                $dateFrom = $now->startOfDay()->format('Y-m-d H:i:s');
                $dateTo = $now->endOfDay()->format('Y-m-d H:i:s');
            }

            // Build query similar to makeReport but for chart data
            $query = OcrMatch::query()
                ->with("bijacs.invoices.customer")
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            // Apply same filters as makeReport
            if ($request->has('gate') && !empty($request->gate)) {
                $gates = is_array($request->gate) ? $request->gate : explode(',', $request->gate);
                $query->whereIn('gate_number', $gates);
            }

            if ($request->has('cargo_type') && !empty($request->cargo_type)) {
                $cargoTypes = is_array($request->cargo_type) ? $request->cargo_type : explode(',', $request->cargo_type);
                if (in_array('bulk', $cargoTypes) && in_array('container', $cargoTypes)) {
                    // Both types - no filter needed
                } elseif (in_array('bulk', $cargoTypes)) {
                    $query->whereNull('container_code');
                } elseif (in_array('container', $cargoTypes)) {
                    $query->whereNotNull('container_code');
                }
            }

            if ($request->has('status') && !empty($request->status)) {
                $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
                $query->where(function ($q) use ($statuses) {
                    foreach ($statuses as $status) {
                        switch ($status) {
                            case 'gcoms_ok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'gcoms')
                                            ->whereHas('invoices');
                                    });
                                });
                                break;
                            case 'gcoms_nok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'gcoms')
                                            ->doesntHave('invoices');
                                    });
                                });
                                break;
                            case 'container_ccs_ok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNotNull('container_code')
                                        ->whereHas('bijacs', function ($bq) {
                                            $bq->where('type', 'ccs')
                                                ->whereHas('invoices');
                                        });
                                });
                                break;
                            case 'container_ccs_nok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNotNull('container_code')
                                        ->whereHas('bijacs', function ($bq) {
                                            $bq->where('type', 'ccs')
                                                ->doesntHave('invoices');
                                        });
                                });
                                break;
                            case 'container_without_bijac':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNotNull('container_code')
                                        ->doesntHave('bijacs');
                                });
                                break;
                            case 'plate_without_bijac':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNull('container_code')
                                        ->doesntHave('bijacs');
                                });
                                break;
                            case 'plate_ccs_ok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNull('container_code')
                                        ->whereHas('bijacs', function ($bq) {
                                            $bq->where('type', 'ccs')
                                                ->whereHas('invoices');
                                        });
                                });
                                break;
                            case 'plate_ccs_nok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereNull('container_code')
                                        ->whereHas('bijacs', function ($bq) {
                                            $bq->where('type', 'ccs')
                                                ->doesntHave('invoices');
                                        });
                                });
                                break;
                            case 'ccs_ok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->whereHas('invoices');
                                    });
                                });
                                break;
                            case 'ccs_nok':
                                $q->orWhere(function ($sq) {
                                    $sq->whereHas('bijacs', function ($bq) {
                                        $bq->where('type', 'ccs')
                                            ->doesntHave('invoices');
                                    });
                                });
                                break;
                        }
                    }
                });
            }

            if ($request->has('danger_type') && !empty($request->danger_type)) {
                $dangerTypes = is_array($request->danger_type) ? $request->danger_type : explode(',', $request->danger_type);
                $query->where(function ($q) use ($dangerTypes) {
                    foreach ($dangerTypes as $dangerType) {
                        switch ($dangerType) {
                            case 'danger_AI':
                                $q->orWhere('IMDG', '!=', '');
                                break;
                            case 'no_danger_AI':
                                $q->orWhereNull('IMDG')
                                    ->orWhere('IMDG', '');
                                break;
                            case 'danger_Bijac':
                                $q->orWhereHas('bijacs', function ($bq) {
                                    $bq->whereNotNull('dangerous_code');
                                });
                                break;
                            case 'no_danger_Bijac':
                                $q->orWhereDoesntHave('bijacs', function ($bq) {
                                    $bq->whereNotNull('dangerous_code');
                                });
                                break;
                        }
                    }
                });
            }

            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $customerIds = is_array($request->customer_id) ? $request->customer_id : explode(',', $request->customer_id);

                $query->whereHas('bijacs.invoices.customer', function ($cq) use ($customerIds) {
                    $cq->whereIn('id', $customerIds);
                });
            }

            // Generate comprehensive chart data
            $chartData = $this->generateChartData($query);

            $response = response()->json([
                'success' => true,
                'data' => $chartData,
                'message' => 'داده‌های نمودار با موفقیت دریافت شد'
            ], Response::HTTP_OK);

            $this->logFunctionExecution('getChartData', $inputData, $response, microtime(true) - $startTime);
            return $response;
        } catch (\Throwable $th) {
            $response = response()->json([
                'success' => false,
                'message' => 'خطا در دریافت داده‌های نمودار: ' . $th->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

            $this->logFunctionExecution('getChartData', $inputData, $response, microtime(true) - $startTime);
            return $response;
        }
    }

    public function searchCustomers(Request $request)
    {
        $startTime = microtime(true);
        $inputData = $request->all();

        try {
            $query = $request->input('q', '');
            $minLength = 3;

            if (strlen($query) < $minLength) {
                return response()->json([
                    'success' => false,
                    'message' => "عبارت جستجو باید حداقل {$minLength} کاراکتر باشد",
                    'data' => []
                ], Response::HTTP_BAD_REQUEST);
            }

            $customers = Customer::where('title', 'LIKE', '%' . $query . '%')
                ->orWhere('shenase_meli', 'LIKE', '%' . $query . '%')
                ->select('id', 'title')
                ->limit(20)
                ->get();

            $response = response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'نتایج جستجو دریافت شد'
            ], Response::HTTP_OK);

            $this->logFunctionExecution('searchCustomers', $inputData, $response, microtime(true) - $startTime);
            return $response;
        } catch (\Throwable $th) {
            $response = response()->json([
                'success' => false,
                'message' => 'خطا در جستجوی مشتریان: ' . $th->getMessage(),
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

            $this->logFunctionExecution('searchCustomers', $inputData, $response, microtime(true) - $startTime);
            return $response;
        }
    }

    private function generateChartData($query)
    {
        $startTime = microtime(true);
        $inputData = ['query_type' => get_class($query), 'query_sql' => $query->toSql(), 'query_bindings' => $query->getBindings()];

        // Get all records with eager loaded relationships
        $records = $query->get();

        // Date-wise aggregation
        $dateData = $records->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            return [
                'label' => $date,
                'value' => $group->count()
            ];
        })->sortBy('label')->values();

        // Gate-wise aggregation
        $gateData = $records->groupBy('gate_number')->map(function ($group, $gateNumber) {
            $gateNames = [
                '1' => 'west',
                '2' => 'east1',
                '3' => 'east2',
                '4' => 'east3'
            ];
            return [
                'label' => $gateNames[$gateNumber] ?? 'unknown',
                'value' => $group->count()
            ];
        })->sortBy('label')->values();

        // Status-wise aggregation
        $statusData = $records->groupBy(function ($item) {
            // Get first bijac relationship (if exists)
            $firstBijac = $item->bijacs->first();
            $firstInvoice = $firstBijac ? $firstBijac->invoices->first() : null;

            $bijacType = $firstBijac ? $firstBijac->type : null;
            $hasInvoice = $firstInvoice ? true : false;
            $hasContainer = !empty($item->container_code);

            // Determine status based on same logic as makeReport
            $status = 'ccs_nok'; // Default
            if ($bijacType === 'gcoms' && $hasInvoice) {
                $status = 'gcoms_ok';
            } elseif ($bijacType === 'gcoms' && !$hasInvoice) {
                $status = 'gcoms_nok';
            } elseif ($hasContainer && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'container_ccs_ok';
            } elseif ($hasContainer && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'container_ccs_nok';
            } elseif ($hasContainer && !$bijacType) {
                $status = 'container_without_bijac';
            } elseif (!$hasContainer && !$bijacType) {
                $status = 'plate_without_bijac';
            } elseif (!$hasContainer && $bijacType === 'ccs' && $hasInvoice) {
                $status = 'plate_ccs_ok';
            } elseif (!$hasContainer && $bijacType === 'ccs' && !$hasInvoice) {
                $status = 'plate_ccs_nok';
            } elseif ($bijacType === 'ccs' && $hasInvoice) {
                $status = 'ccs_ok';
            }

            return $status;
        })->map(function ($group, $status) {
            return [
                'label' => $status,
                'value' => $group->count()
            ];
        })->values();

        // Cargo type aggregation
        $cargoData = $records->groupBy(function ($item) {
            return !empty($item->container_code) ? 'container' : 'bulk';
        })->map(function ($group, $cargoType) {
            return [
                'label' => $cargoType,
                'value' => $group->count()
            ];
        })->values();

        // Hourly aggregation
        $hourlyData = $records->groupBy(function ($item) {
            return $item->created_at->format('H');
        })->map(function ($group, $hour) {
            return [
                'label' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                'value' => $group->count()
            ];
        })->sortBy('label')->values();

        // Summary statistics
        $totalTraffic = $records->count();
        $successTraffic = $records->filter(function ($item) {
            $firstBijac = $item->bijacs->first();
            $firstInvoice = $firstBijac ? $firstBijac->invoices->first() : null;
            return $firstInvoice ? true : false;
        })->count();

        $failedTraffic = $totalTraffic - $successTraffic;

        // Calculate average processing time
        $avgProcessingTime = $records->avg(function ($item) {
            if ($item->created_at && $item->log_time) {
                return $item->created_at->diffInSeconds($item->log_time);
            }
            return null;
        });

        $result = [
            'summary' => [
                'total_traffic' => $totalTraffic,
                'success_traffic' => $successTraffic,
                'failed_traffic' => $failedTraffic,
                'avg_processing_time' => $avgProcessingTime
            ],
            'by_date' => $dateData,
            'by_gate' => $gateData,
            'by_status' => $statusData,
            'by_cargo_type' => $cargoData,
            'by_hour' => $hourlyData
        ];

        $this->logFunctionExecution('generateChartData', $inputData, $result, microtime(true) - $startTime);
        return $result;
    }

    private function logFunctionExecution(string $functionName, array $inputData, $response, float $executionTime)
    {
        $logData = [
            'function' => $functionName,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'input_data' => $this->sanitizeInputData($inputData),
            'response_type' => gettype($response),
            'response_size' => is_object($response) ? json_encode($response) : strlen(json_encode($response ?? ''))
        ];

        // Log to custom channel that writes to separate file
        Log::channel('function_logs')->info('Function Execution', $logData);
    }

    private function sanitizeInputData(array $data): array
    {
        $sanitized = $data;

        // Remove or mask sensitive data if any
        // For now, just return as is since this appears to be internal API

        return $sanitized;
    }
}
