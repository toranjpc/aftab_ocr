<?php

namespace Modules\Ocr\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;

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
                COUNT(CASE WHEN container_size = "40f" THEN 1 END) AS `40f`,
                COUNT(CASE WHEN container_size = "20f" THEN 1 END) AS `20f`,
                COUNT(CASE WHEN container_size = "unknown" THEN 1 END) AS `unknown`,
                COUNT(CASE WHEN container_size IS NULL THEN 1 END) AS `fale`,
                COUNT(CASE WHEN plate_type = "iran" THEN 1 END) AS `iran`,
                COUNT(CASE WHEN plate_type = "afghan" THEN 1 END) AS `afghan`,
                COUNT(CASE WHEN plate_type = "iran-regular" THEN 1 END) AS `regular`,
                COUNT(CASE WHEN gate_number = 2 THEN 1 END) AS `gate_2`,
                COUNT(CASE WHEN gate_number = 3 THEN 1 END) AS `gate_3`,
                COUNT(CASE WHEN gate_number = 4 THEN 1 END) AS `gate_4`,
                COUNT(CASE WHEN plate_type = "europe" THEN 1 END) AS `europe`,
                COUNT(CASE WHEN type = "gcoms" THEN 1 END) AS `gcoms_count`,
                COUNT(CASE WHEN type = "ccs" THEN 1 END) AS `container`,
                COUNT(CASE WHEN type IS NULL OR type NOT IN ("ccs", "gcoms") THEN 1 END) AS `no_bijac`
            ')->first();

        return response()->json($counts);
    }



    public function index(Request $request)
    {
        // ساخت زیرکوئری اولیه از ocr_logs به همراه JOIN های لازم
        $subQuery = OcrMatch::query()
		    ->leftJoin('bijacables', function ($join) {
                $join->on('ocr_matches.id', '=', 'bijacables.bijacable_id')
                    ->where('bijacables.bijacable_type', '=', 'Modules\Ocr\Models\OcrMatch');
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

        // انتخاب ستون‌های مورد نیاز جهت شمارش؛ فرض بر این است که برای هر ocr_log، این مقادیر یکتا هستند.
        $subQuery->select(
            'ocr_matches.id',
            'ocr_matches.log_time',
            'ocr_matches.container_size',
            'ocr_matches.plate_type',
            'ocr_matches.gate_number',
            'bijacs.type'
        )->distinct();

        // دریافت SQL و binding های زیرکوئری
        $subSql = $subQuery->toSql();
        $bindings = $subQuery->getBindings();

        // استفاده جداگانه از زیرکوئری برای محاسبه counts
        $counts = DB::table(DB::raw("($subSql) as sub"))
            ->setBindings($bindings)
            ->selectRaw('
                        COUNT(*) AS `all`,
                        COUNT(CASE WHEN container_size = "40f" THEN 1 END) AS `40f`,
                        COUNT(CASE WHEN container_size = "20f" THEN 1 END) AS `20f`,
                        COUNT(CASE WHEN container_size = "unknown" THEN 1 END) AS `unknown`,
                        COUNT(CASE WHEN container_size IS NULL THEN 1 END) AS `fale`,
                        COUNT(CASE WHEN plate_type = "iran" THEN 1 END) AS `iran`,
                        COUNT(CASE WHEN plate_type = "afghan" THEN 1 END) AS `afghan`,
                        COUNT(CASE WHEN plate_type = "iran-regular" THEN 1 END) AS `regular`,
                        COUNT(CASE WHEN gate_number = 2 THEN 1 END) AS `gate_2`,
                        COUNT(CASE WHEN gate_number = 3 THEN 1 END) AS `gate_3`,
                        COUNT(CASE WHEN gate_number = 4 THEN 1 END) AS `gate_4`,
                        COUNT(CASE WHEN plate_type = "europe" THEN 1 END) AS `europe`,
                        COUNT(CASE WHEN type = "gcoms" THEN 1 END) AS `gcoms_count`,
                        COUNT(CASE WHEN type = "ccs" THEN 1 END) AS `container`,
                        COUNT(CASE WHEN type IS NULL OR type NOT IN ("ccs", "gcoms") THEN 1 END) AS `no_bijac`
                    ')
            ->first();

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
    }
}
