<?php

namespace Modules\Ocr\Controller;

use Illuminate\Http\Request;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\Invoice;
use Modules\Gcoms\Models\GcomsOutData;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use App\Http\Controllers\Controller;
use Modules\Ocr\OcrBuffer;
use Modules\Ocr\OcrComputedResolver;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Modules\Auth\Controllers\AuthController;
use Modules\Ocr\Jobs\ProcessOcrLog;
use Modules\Ocr\Jobs\EditedMatchBijacs;

use function PHPUnit\Framework\isNull;

class OcrMatchController extends Controller
{
    /**
     * ثبت زمان سپری شده برای یک بخش
     */
    private function logTimeSection(&$timeTok, $sectionName, $startTime = null)
    {
        $currentTime = microtime(true);
        if ($startTime === null) {
            $startTime = $timeTok["total_start"];
        }

        $elapsed = $currentTime - $startTime;
        $timeTok["sections"][$sectionName] = [
            "elapsed" => round($elapsed, 4),
            "timestamp" => $currentTime
        ];

        return $currentTime; // برای استفاده در بخش بعدی
    }
    public function getList(Request $request)
    {
        $startTime = microtime(true);
        $timeTok = [
            "total_start" => $startTime,
            "sections" => []
        ];
        $logwright = 0; // فعال کردن لاگ‌گذاری

        $queryBuildStart = microtime(true);
        $ocrMatches = OcrMatch::query();
        // with([
        //     'bijacs' => function ($query) {
        //         $query->withCount('ocrMatches')
        //             ->with('invoices')
        //             ->with('allbijacs')
        //             ->orderBy('bijac_date', 'desc');
        //     },
        //     "isCustomCheck",
        //     "isSerachBijac"
        // ]);


        //http://172.16.13.10/ocrbackend/api/ocr-match/list?_append=invoice_with=bijacs&gate_number=1&gate=1&page=1&filters[plate_number][$contains]=454&filters[IMDG][$in][0]=danger_AI&
        /*
        $filters = $request->input('filters', []);
        if (isset($filters['IMDG'])) {
            $dangerStatus = $request->input('filters.IMDG.$in', []);
            unset($filters['IMDG']);
            $request->merge(['filters' => $filters]);
            if (in_array('danger_Bijac', $dangerStatus)) {
                $ocrMatches->whereHas('bijacs', function ($query) {
                    $query->whereNotNull('dangerous_code')->where('dangerous_code', '!=', "0");
                });
            }
            if (in_array('no_danger_Bijac', $dangerStatus)) {
                $ocrMatches->whereHas('bijacs', function ($query) {
                    $query->where('dangerous_code', "0");
                });
            }
            if (in_array('danger_AI', $dangerStatus)) {
                $ocrMatches->whereNotNull('IMDG')->where('IMDG', '>', "0");
            }
            if (in_array('no_danger_AI', $dangerStatus)) {
                $ocrMatches->where('IMDG', "0");
            }

            // return $ocrMatches->select('id', 'IMDG')->paginate(5);
        }
        */
        // $timeTok["after IMDG filter"] = microtime(true) - $startTime;


        if (!empty($request->findThis)) {
            $ocrMatches->where('id', $request->findThis);
        }

        if (!empty($request->gate)) {
            $ocrMatches->where('gate_number', $request->gate);
        } else {
            // $ocrMatches->where('gate_number', 0);
        }

        $this->logTimeSection($timeTok, "query_building", $queryBuildStart);

        $paginationStart = microtime(true);
        $ocrMatches = $ocrMatches
            // ->where('id', '233639')//حذف
            // ->whereIn('match_status', ["container_without_bijac", "plate_without_bijac"]) //حذف
            ->filter()
            ->sort()
            ->orderBy('id', 'DESC')
            ->paginate($request->itemPerPage ?? 10);


        /*
        // 🔍🔍🔍 **کد جدید برای لاگ‌گیری رکوردهای بدون کش** 🔍🔍🔍
        if (!$ocrMatches->isEmpty()) {
            $ocrIds = $ocrMatches->pluck('id');

            // لیست همه کلیدهای کش مربوط به این IDها
            $allCacheKeys = [];
            foreach ($ocrIds as $id) {
                $allCacheKeys[] = "ocr:{$id}:invoice";
                $allCacheKeys[] = "ocr:{$id}:invoices";
                // اگر کلیدهای computed data هم می‌خواهی چک کنی، اضافه کن
                // $allCacheKeys[] = "ocr:computed:{$id}"; 
            }

            // بررسی وجود در کش
            $missingCacheRecords = [];
            foreach ($allCacheKeys as $key) {
                if (!Cache::has($key)) {
                    $missingCacheRecords[] = $key;
                }
            }

            if (!empty($missingCacheRecords)) {
                Log::warning("=== MISSING CACHE RECORDS ===", [
                    "gate" => $request->gate,
                    "page" => $request->get('page', 1),
                    "total_records" => $ocrMatches->count(),
                    "missing_cache_count" => count($missingCacheRecords),
                    "missing_cache_percentage" => round((count($missingCacheRecords) / count($allCacheKeys)) * 100, 2) . '%',
                    "missing_keys_sample" => array_slice($missingCacheRecords, 0, 5), // 5 کلید اول
                    "all_ids" => $ocrIds->toArray(),
                    "query_conditions" => [
                        'filters' => $request->input('filters', []),
                        'sort' => $request->input('sort'),
                        'gate' => $request->gate,
                        'findThis' => $request->findThis
                    ]
                ]);

                // لاگ مفصل‌تر برای هر ID
                foreach ($ocrIds as $id) {
                    $invoiceKey = "ocr:{$id}:invoice";
                    $invoicesKey = "ocr:{$id}:invoices";

                    if (!Cache::has($invoiceKey) || !Cache::has($invoicesKey)) {
                        Log::debug("Record missing cache", [
                            'ocr_id' => $id,
                            'invoice_cached' => Cache::has($invoiceKey) ? 'YES' : 'NO',
                            'invoices_cached' => Cache::has($invoicesKey) ? 'YES' : 'NO',
                            'gate' => $request->gate
                        ]);
                    }
                }
            } else {
                Log::info("=== ALL RECORDS CACHED ===", [
                    "gate" => $request->gate,
                    "page" => $request->get('page', 1),
                    "total_records" => $ocrMatches->count(),
                    "cache_hit_rate" => "100%"
                ]);
            }
        }
        // 🔍🔍🔍 **پایان کد جدید** 🔍🔍🔍
        */


        $this->logTimeSection($timeTok, "pagination", $paginationStart);

        // Early return if no results to process
        if ($ocrMatches->isEmpty() || $ocrMatches->total() === 0) {
            if ($logwright) {
                $totalTime = microtime(true) - $startTime;
                $timeTok["total_execution_time"] = round($totalTime, 4);

                Log::info("=== PERFORMANCE LOG ===", [
                    "gate" => $request->gate,
                    "total_time" => $timeTok["total_execution_time"] . "s",
                    "sections" => ["pagination" => ["time" => $totalTime, "percentage" => 100.0]],
                    "item_count" => 0,
                    "cache_hit_rate" => 0
                ]);
            }
            return response([
                'message' => 'ok',
                'event_index' => event('ocrMatch.index', ocrMatch::query()),
                "OcrMatch" => $ocrMatches
            ], Response::HTTP_OK);
        }

        $customTariff = config('ocr.custom_tariff');
        $sectionTime = $this->logTimeSection($timeTok, "custom_tariff_load");




        // ID-FIRST CACHE FLOW
        $this->logTimeSection($timeTok, "before_cache_setup");
        $ocrIds = $ocrMatches->pluck('id');

        // Ensure ocrIds is a proper collection/array
        if (!$ocrIds instanceof \Illuminate\Support\Collection && !is_array($ocrIds)) {
            Log::error('ocrIds is not a collection or array', [
                'ocrIds' => $ocrIds,
                'type' => gettype($ocrIds),
                'ocrMatches_type' => gettype($ocrMatches),
                'ocrMatches_count' => method_exists($ocrMatches, 'count') ? $ocrMatches->count() : 'unknown'
            ]);
            $ocrIds = collect();
        }

        // Ensure ocrIds contains only valid integers
        $ocrIds = $ocrIds->filter(function ($id) {
            return is_numeric($id) && (int)$id > 0;
        });

        $this->logTimeSection($timeTok, "pluck_ocr_ids");

        // Prepare computation context - now includes log_time for time-aware caching
        // Use the most recent log_time from the current page for context
        $mostRecentLogTime = $ocrMatches->max('log_time');
        $context = [
            'custom_tariff' => $customTariff,
            'gate' => $request->gate,
            'log_time' => $mostRecentLogTime, // Time-aware context for cache keys
        ];

        // Initialize resolver and get computed data for OCR IDs only
        // Resolver will handle loading models only for IDs missing from cache
        $resolver = new OcrComputedResolver();
        $computedDataStart = microtime(true);
        $ocrIdsArray = $ocrIds->toArray();
        $batchComputedData = $resolver->getBatchComputedData($ocrIdsArray, $context);
        $this->logTimeSection($timeTok, "batch_computed_data", $computedDataStart);

        // Keep existing invoice/invoices caching logic
        $allInvoiceKeys = $ocrIds->map(fn($id) => "ocr:{$id}:invoice")->toArray();
        $allInvoicesKeys = $ocrIds->map(fn($id) => "ocr:{$id}:invoices")->toArray();
        $this->logTimeSection($timeTok, "prepare_cache_keys");

        // Ensure cache keys are proper arrays of strings
        $allInvoiceKeys = is_array($allInvoiceKeys) ? array_filter($allInvoiceKeys, 'is_string') : [];
        $allInvoicesKeys = is_array($allInvoicesKeys) ? array_filter($allInvoicesKeys, 'is_string') : [];

        $allCachedInvoices = Cache::many($allInvoiceKeys);
        $allCachedInvoicesList = Cache::many($allInvoicesKeys);
        $this->logTimeSection($timeTok, "cache_many_fetch");

        $missingInvoiceKeys = array_filter($allInvoiceKeys, fn($key) => !isset($allCachedInvoices[$key]));
        $missingInvoicesKeys = array_filter($allInvoicesKeys, fn($key) => !isset($allCachedInvoicesList[$key]));

        if (!empty($missingInvoiceKeys)) {
            $cacheStart = microtime(true);
            foreach ($ocrMatches as $ocr) {
                $key = "ocr:{$ocr->id}:invoice";
                if (in_array($key, $missingInvoiceKeys)) {
                    $invoice = $ocr->invoice;
                    Cache::put($key, $invoice ?? "emptyData", now()->addMinutes(15));
                    $allCachedInvoices[$key] = $invoice ?? "emptyData";
                }
            }
            $this->logTimeSection($timeTok, "cache_missing_invoices", $cacheStart);
        }

        if (!empty($missingInvoicesKeys)) {
            $cacheStart = microtime(true);
            foreach ($ocrMatches as $ocr) {
                $key = "ocr:{$ocr->id}:invoices";
                if (in_array($key, $missingInvoicesKeys)) {
                    $invoices = $ocr->invoices;
                    Cache::put($key, $invoices ?? "emptyData", now()->addMinutes(15));
                    $allCachedInvoicesList[$key] = $invoices ?? "emptyData";
                }
            }
            $this->logTimeSection($timeTok, "cache_missing_invoices_list", $cacheStart);
        }



        // ASSEMBLY-ONLY MAP - No heavy computation here
        $this->logTimeSection($timeTok, "before_map_operation");
        $ocrMatches->map(function ($ocr) use ($allCachedInvoices, $allCachedInvoicesList, $batchComputedData) {
            // Attach cached invoice data
            $invoiceKey = "ocr:{$ocr->id}:invoice";
            $invoicesKey = "ocr:{$ocr->id}:invoices";
            $invoiceData = $allCachedInvoices[$invoiceKey] ?? "emptyData";
            $ocr->setAttribute('invoice', $invoiceData === "emptyData" ? null : $invoiceData);
            $invoicesData = $allCachedInvoicesList[$invoicesKey] ?? "emptyData";
            $ocr->setAttribute('invoices', $invoicesData === "emptyData" ? null : $invoicesData);

            // Attach computed data from cache
            $computed = $batchComputedData[$ocr->id] ?? [];
            foreach ($computed as $key => $value) {
                $ocr->setAttribute($key, $value);
            }

            return $ocr;
        });

        $this->logTimeSection($timeTok, "after_map_operation");

        if ($logwright) {
            $totalTime = microtime(true) - $startTime;
            $timeTok["total_execution_time"] = round($totalTime, 4);

            // محاسبه درصد زمان هر بخش
            $sectionsSummary = [];
            foreach ($timeTok["sections"] as $section => $data) {
                $percentage = $totalTime > 0 ? round(($data["elapsed"] / $totalTime) * 100, 2) : 0;
                $sectionsSummary[$section] = [
                    "time" => $data["elapsed"],
                    "percentage" => $percentage
                ];
            }

            Log::info("=== PERFORMANCE LOG ===", [
                "gate" => $request->gate,
                "total_time" => $timeTok["total_execution_time"] . "s",
                "sections" => $sectionsSummary,
                "item_count" => $ocrMatches->count(),
                "cache_hit_rate" => count($allCachedInvoices) > 0 ?
                    round((count($allCachedInvoices) - count($missingInvoiceKeys)) / count($allCachedInvoices) * 100, 2) : 0
            ]);
        }
        return response(
            [
                'message' => 'ok',
                'event_index' => event('ocrMatch.index', ocrMatch::query()),
                "OcrMatch" => $ocrMatches
            ],
            Response::HTTP_OK
        );
    }

    public function update(Request $request, OcrMatch $ocrMatch)
    {
        $request->validate([
            "OcrMatch.plate_number_edit" => "nullable|string|max:255",
            "OcrMatch.container_code_edit" => "nullable|string|max:255",
        ]);
        if (!in_array($ocrMatch->match_status, ['container_without_bijac', 'plate_without_bijac'])) {
            return response()->json([
                'success' => false,
                'message' => 'این فیلد قابل ویرایش نیست.'
            ], 422);
        }
        $ocrId = $ocrMatch->ocr_log_id;

        $plate_number_edit = data_get($request, 'OcrMatch.plate_number_edit', null);
        if ($plate_number_edit) {

            if (
                $this->checkPlateIsDuplicate([
                    $plate_number_edit,
                    $ocrMatch->gate_number
                ], 2, $ocrMatch->ocr_log_id)
            ) {

                return response()->json([
                    'message' => 'شماره پلاک وارد شده قبلا ارسال شده است!'
                ], 422);
            }

            $request->merge([
                'plate_number_3' => $plate_number_edit,
                'plate_number_edit' => $plate_number_edit
            ]);
        }

        $container_code_edit = data_get($request, 'OcrMatch.container_code_edit', null);
        if ($container_code_edit) {

            if (
                $this->checkIsDuplicateContainer([
                    $container_code_edit,
                    $ocrMatch->gate_number
                ], 2, $ocrMatch->ocr_log_id)
            ) {

                return response()->json([
                    'message' => 'شماره کانتینر وارد شده قبلا ارسال شده است!'
                ], 422);
            }
            $request->merge([
                'container_code_3' => str_replace(' ', '', $container_code_edit),
                'container_code_edit' => str_replace(' ', '', $container_code_edit)
            ]);
        }

        if ($plate_number_edit || $container_code_edit) {
            $ocrMatch->update($request->only([
                // 'plate_number_3',
                'plate_number_edit',
                // 'container_code_3',
                'container_code_edit'
            ]));
            // ProcessOcrLog::dispatch(
            //     $ocrId,
            // );

            // $ocrMatch =  $ocrMatch->fresh() ? $ocrMatch->fresh()->load('bijacs')->append('invoice') : null;
            // EditedMatchBijacs::dispatch($ocrMatch->id);

            return response()->json([
                'message' => 'با موفقیت ویرایش شد!',
                'data' => $ocrMatch,
            ], 200);
        }
    }

    public function update_customCheck(Request $request, OcrMatch $ocrMatch)
    {
        try {
            $AuthController = new AuthController();
            $AuthController->savelog($ocrMatch, "checked", "تایید دستی بدون بیجک ها (موارد افلاین)");

            return response()->json([
                'message' => 'با موفقیت تایید شد!',
                'data' => $ocrMatch->fresh()
                    ? $ocrMatch->fresh()->load('bijacs')->append('invoice')
                    : null,
            ], 200);
        } catch (\Throwable $e) {
            return $e;
            Log::error('update_customCheck error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'خطایی در پردازش درخواست رخ داد.',
            ], 500);
        }
    }
    public function addBaseInvoice(Request $request, $ocrMatchId)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|integer|exists:invoices,id',
            ]);
            $ocrMatch = OcrMatch::with('bijacs.invoices')->findOrFail($ocrMatchId);
            $invoiceIds = $ocrMatch->bijacs
                ->flatMap(fn($bijac) => $bijac->invoices->pluck('id'))
                ->unique();

            if ($invoiceIds->isEmpty()) {
                return response()->json(['message' => 'هیچ فاکتوری برای این مچ پیدا نشد.'], 404);
            }
            Invoice::whereIn('id', $invoiceIds)->update(['base' => 0]);
            Invoice::where('id', $request->invoice_id)->update(['base' => 1]);

            return response()->json([
                'message' => 'Base invoice بروزرسانی شد.',
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ خطا در addBaseInvoice:', ['error' => $e->getMessage()]);
            // return response()->json([
            //     'message' => 'خطا در بروزرسانی Base invoice',
            //     'error' => $e->getMessage(),
            // ], 500);
        }
    }




    public function getGroupItems(OcrMatch $ocr)
    {
        $bijac = $ocr->bijacs
            ->sortByDesc('bijac_date')
            ->first();
        $log_time = $ocr->log_time;
        if (empty($ocr->log_time))
            $log_time = now();
        $ocrMatches = [];

        if ($bijac && $bijac->receipt_number) {

            $ocrMatches = OcrMatch::with([
                'bijacs' => function ($query) {
                    $query->withCount('ocrMatches');
                }
            ])
                ->whereHas(
                    'bijacs',
                    function (Builder $query) use ($bijac) {
                        $query->where('receipt_number', $bijac->receipt_number);
                    }
                )
                ->where('log_time', '<=', $log_time)
                ->get()
                ->append('invoices');
        }

        // return $ocrMatches;
        return response()->json([
            'data' => $ocrMatches
        ]);
    }

    private function checkPlateIsDuplicate($data, $min = 3, $ignore_id = null)
    {
        [$input, $gate] = $data;

        $lastSixPlate = OcrBuffer::getBuffer($gate);

        $closest = false;

        foreach ($lastSixPlate as $plate) {
            if ($ignore_id && $plate->id == $ignore_id)
                continue;

            $lev = levenshtein($input, $plate->plate_number);

            if ($lev == 0)
                return $plate;

            if ($lev < $min) {
                $closest = $plate;
            }
        }

        return $closest;
    }

    private function checkIsDuplicateContainer($data, $min = 3, $ignore_id = null)
    {
        function extractDigits($string)
        {
            preg_match_all('/\d+/', $string, $matches);

            return implode('', $matches[0]);
        }

        [$input, $gate] = $data;

        $lastSix = OcrBuffer::getBuffer($gate, 'container');

        $ocrLog = OcrLog::find($ignore_id);

        $closest = false;

        foreach ($lastSix as $container) {
            if (
                $ignore_id &&
                (
                    $container->id == $ignore_id ||
                    $container->id == $ocrLog->parent_id
                )
            )
                continue;

            $lev = levenshtein(
                substr(extractDigits($input), 0, 6),
                substr(extractDigits($container->container_code), 0, 6)
            );

            if ($lev == 0)
                return $container;

            if ($lev < $min) {
                $closest = $container;
            }
        }

        return $closest;
    }
}
