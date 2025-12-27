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
    public function getList(Request $request)
    {
        $startTime = microtime(true);
        $timeTok = [
            "starts" => microtime(true) - $startTime,
        ];
        $logwright = 0;

        $ocrMatches = OcrMatch::with([
            'bijacs' => function ($query) {
                $query->withCount('ocrMatches')
                    ->with('invoices')
                    ->with('allbijacs')
                    ->orderBy('bijac_date', 'desc');
            },
            "isCustomCheck",
            "isSerachBijac"
        ]);


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
                $ocrMatches->whereNotNull('IMDG')->where('IMDG', '>', 0);
            }
            if (in_array('no_danger_AI', $dangerStatus)) {
                $ocrMatches->where('IMDG', 0);
            }

            // return $ocrMatches->select('id', 'IMDG')->paginate(5);
        }
        // $timeTok["after IMDG filter"] = microtime(true) - $startTime;


        if (!empty($request->findThis)) {
            $ocrMatches->where('id', $request->findThis);
        }

        if (!empty($request->gate)) {
            $ocrMatches->where('gate_number', $request->gate);
        } else {
            // $ocrMatches->where('gate_number', 0);
        }


        $ocrMatches = $ocrMatches
            // ->where('id', '233639')//حذف
            // ->whereIn('match_status', ["container_without_bijac", "plate_without_bijac"]) //حذف
            ->filter()
            ->sort()
            ->orderBy('id', 'DESC')
            ->paginate($request->itemPerPage ?? 10);
        // $timeTok["after paginate"] = microtime(true) - $startTime;

        $customTariff = config('ocr.custom_tariff');




        // $timeTok["before new cache"] = microtime(true) - $startTime;
        $ocrIds = $ocrMatches->pluck('id');
        $allInvoiceKeys = $ocrIds->map(fn($id) => "ocr:{$id}:invoice")->toArray();
        $allInvoicesKeys = $ocrIds->map(fn($id) => "ocr:{$id}:invoices")->toArray();
        $allCachedInvoices = Cache::many($allInvoiceKeys);
        $allCachedInvoicesList = Cache::many($allInvoicesKeys);
        $missingInvoiceKeys = array_filter($allInvoiceKeys, fn($key) => !isset($allCachedInvoices[$key]));
        $missingInvoicesKeys = array_filter($allInvoicesKeys, fn($key) => !isset($allCachedInvoicesList[$key]));
        if (!empty($missingInvoiceKeys)) {
            foreach ($ocrMatches as $ocr) {
                $key = "ocr:{$ocr->id}:invoice";
                if (in_array($key, $missingInvoiceKeys)) {
                    $invoice = $ocr->invoice;
                    Cache::put($key, $invoice ?? "emptyData", now()->addMinutes(15));
                    $allCachedInvoices[$key] = $invoice ?? "emptyData";
                }
            }
        }
        if (!empty($missingInvoicesKeys)) {
            foreach ($ocrMatches as $ocr) {
                $key = "ocr:{$ocr->id}:invoices";
                if (in_array($key, $missingInvoicesKeys)) {
                    $invoices = $ocr->invoices;
                    Cache::put($key, $invoices ?? "emptyData", now()->addMinutes(15));
                    $allCachedInvoicesList[$key] = $invoices ?? "emptyData";
                }
            }
        }
        // $timeTok["affter new cache"] = microtime(true) - $startTime;



        // logCheck -> 
        // $timeTok["before ocrMatches->map"] = microtime(true) - $startTime;
        $ocrMatches->map(function ($ocr) use ($customTariff, $request, &$timeTok, $startTime, $allCachedInvoices, $allCachedInvoicesList) { //

            // $timeTok["before append invoices and invoice"] = microtime(true) - $startTime;

            // $ocr->append('invoice');
            // $timeTok["after append invoice"] = microtime(true) - $startTime;
            // $ocr->append('invoices');

            $timeTok["map"]["before append invoices"][] = microtime(true) - $startTime;
            $invoiceKey = "ocr:{$ocr->id}:invoice";
            $invoicesKey = "ocr:{$ocr->id}:invoices";
            $invoiceData = $allCachedInvoices[$invoiceKey] ?? "emptyData";
            $ocr->setAttribute('invoice', $invoiceData === "emptyData" ? null : $invoiceData);
            $invoicesData = $allCachedInvoicesList[$invoicesKey] ?? "emptyData";
            $ocr->setAttribute('invoices', $invoicesData === "emptyData" ? null : $invoicesData);
            $timeTok["map"]["affter append invoices"][] = microtime(true) - $startTime;


            $ocr['total_vehicles'] = 0;
            $ocr['ocr_vehicles'] = 0;

            $bijac = $ocr->bijacs->first();
            $timeTok["map"]["after first bijac"][] = microtime(true) - $startTime;

            // if ($bijac && $bijac->receipt_number) {//موقتا برای محاسبه نشدن مادرتخصصی ها
            if ($bijac && $bijac->receipt_number && $bijac->is_single_carry == 0) {

                // $bijacs = $bijac->allbijacs;
                // $timeTok["after all bijacs"] = microtime(true) - $startTime;
                $allbijacsKey = "bijac:{$bijac->receipt_number}:allbijacs";
                if (!Cache::has($allbijacsKey)) {
                    $allbijacs = $bijac->allbijacs;
                    Cache::put($allbijacsKey, $allbijacs, now()->addMinutes(15));
                } else {
                    $allbijacs = Cache::get($allbijacsKey);
                }
                $bijacs = $allbijacs;
                $bijacIds = $bijacs->pluck('id');
                $timeTok["map"]["after all bijacs"][] = microtime(true) - $startTime;

                // $ocr['total_vehicles'] = $bijacs->count();
                if (
                    $bijac->invoice &&
                    Str::startsWith($bijac->invoice?->invoice_number ?? '', 'AFTAB_C')
                ) {
                    // if (str_starts_with($bijac->receipt_number, "AFTAB_CE")) {
                    $invoice_ = $bijac->invoice;
                    $timeTok["map"]["after find bijac invoice"][] = microtime(true) - $startTime;

                    $totalTu = ceil($invoice_->amount / $customTariff);
                    $downedTu = $invoice_->bijacs->count() ?? 0;
                    // foreach ($invoice_->bijacs as $item) {
                    //     $downedTu++;

                    //     if (!empty($item->container_size) && $item->container_size == "_40Feet") {
                    //         $downedTu++;
                    //     }
                    // }
                    // $mandeTu = $totalTu - $downedTu;
                    $ocr['total_vehicles'] = $totalTu;
                    $ocr['ocr_vehicles'] = $downedTu;
                    $timeTok["map"]["after data finded in fatab datas"][] = microtime(true) - $startTime;
                } else {
                    // $ocr['total_vehicles'] = $bijacs->pluck('plate_normal')->unique()->count();
                    $receipt = $bijac->receipt_number;
                    $logDate = $ocr->log_time;
                    $totalVehiclesKey = "ocr_vehicles:bijacs_:{$receipt}:{$logDate}";

                    // $totalVehiclesKey = "total_vehicles:bijacs_" . md5(implode(',', $bijacIds->toArray()));
                    if (!Cache::has($totalVehiclesKey)) {
                        $totalVehicles = $bijacs->pluck('plate_normal')->unique()->count();
                        Cache::put($totalVehiclesKey, $totalVehicles, now()->addMinutes(15));
                    } else {
                        $totalVehicles = Cache::get($totalVehiclesKey);
                    }
                    $ocr['total_vehicles'] = $totalVehicles;
                    $timeTok["map"]["after find total_vehicles usually"][] = microtime(true) - $startTime;

                    // $ocr['ocr_vehicles'] = DB::table('bijacables')
                    //     ->where('bijacable_type', OcrMatch::class)
                    //     ->whereExists(function ($query) use ($ocr) {
                    //         $query->select(DB::raw(1))
                    //             ->from('ocr_matches')
                    //             ->whereColumn('ocr_matches.id', 'bijacables.bijacable_id')
                    //             ->where('ocr_matches.log_time', '<=', $ocr->log_time);
                    //     })
                    //     ->whereIn('bijac_id', $bijacIds)
                    //     ->distinct('bijac_id')
                    //     ->count('bijac_id');
                    // logCheck ->
                    // log::info("{$id}_Operation took for allbijacs " . microtime(true) - $startTime . " seconds in gate {$request->gate}");

                    // $ocr['ocr_vehicles'] = DB::table('bijacables')
                    //     ->select(DB::raw('MIN(bijacable_id) as bijacable_id'))
                    //     ->where('bijacable_type', OcrMatch::class)
                    //     ->whereIn('bijac_id', $bijacIds)
                    //     ->whereExists(function ($query) use ($ocr) {
                    //         $query->select(DB::raw(1))
                    //             ->from('ocr_matches')
                    //             ->whereColumn('ocr_matches.id', 'bijacables.bijacable_id')
                    //             ->where('ocr_matches.log_time', '<=', $ocr->log_time ?? now());
                    //     })
                    //     ->groupBy('bijac_id')
                    //     ->distinct()
                    //     ->get()
                    //     ->count();


                    // $bijacIdsKey = md5(implode(',', $bijacIds->toArray() ?: []));
                    // $ocrVehiclesKey = "ocr_vehicles:{$bijacIdsKey}";

                    $receipt = $bijac->receipt_number;
                    $logDate = $ocr->log_time;
                    $ocrVehiclesKey = "ocr_vehicles:receipt:{$receipt}:{$logDate}";

                    if (!Cache::has($ocrVehiclesKey)) {
                        $ocrVehiclesCount = DB::table('bijacables')
                            ->select(DB::raw('MIN(bijacable_id) as bijacable_id'))
                            ->where('bijacable_type', OcrMatch::class)
                            ->whereIn('bijac_id', $bijacIds)
                            ->whereExists(function ($query) use ($ocr) {
                                $query->select(DB::raw(1))
                                    ->from('ocr_matches')
                                    ->whereColumn('ocr_matches.id', 'bijacables.bijacable_id')
                                    ->where('ocr_matches.log_time', '<=', $ocr->log_time ?? now());
                            })
                            ->groupBy('bijac_id')
                            ->distinct()
                            ->get()
                            ->count();
                        Cache::put($ocrVehiclesKey, $ocrVehiclesCount, now()->addMinutes(15));
                    } else {
                        $ocrVehiclesCount = Cache::get($ocrVehiclesKey);
                    }
                    $ocr['ocr_vehicles'] = $ocrVehiclesCount;
                    $timeTok["map"]["after find ocr_vehicles usually"][] = microtime(true) - $startTime;


                    // logCheck ->
                    // log::info("{$id}_Operation took for ocr_vehicles (" . $ocr['ocr_vehicles'] . ") " . microtime(true) - $startTime . " seconds in gate {$request->gate}");
                }


                if ($bijac->type == 'ccs' && $ocr->invoice) { //کانتینر
                    // $Invoicebase = $ocr->invoicebase;

                    $ocr['total_tu'] = round($ocr->invoice->amount / $customTariff);
                    $timeTok["map"]["after find total_tu usually"][] = microtime(true) - $startTime;

                    // $ocr['ocr_tu'] = Bijac::has('ocrMatches')
                    //     ->whereIn('id', $bijacIds)
                    //     ->selectRaw("SUM(CASE 
                    //             WHEN container_size = '_20Feet' THEN 1
                    //             WHEN container_size IN ('_40Feet', '_45Feet') THEN 2
                    //             ELSE 0 
                    //         END) as ocrTu")
                    //     ->value('ocrTu');
                    $ocrTuKey = "ocr_tu:bijacs_" . md5(implode(',', $bijacIds->toArray() ?: []));
                    if (!Cache::has($ocrTuKey)) {
                        $ocrTuValue = Bijac::has('ocrMatches')
                            ->whereIn('id', $bijacIds)
                            ->selectRaw("SUM(CASE 
                                    WHEN container_size = '_20Feet' THEN 1
                                    WHEN container_size IN ('_40Feet', '_45Feet') THEN 2
                                    ELSE 0 
                                END) as ocrTu")
                            ->value('ocrTu') ?? 0;
                        Cache::put($ocrTuKey, $ocrTuValue, now()->addMinutes(15));
                    } else {
                        $ocrTuValue = Cache::get($ocrTuKey);
                    }
                    $ocr['ocr_tu'] = $ocrTuValue;
                    $timeTok["map"]["after find ocr_tu usually"][] = microtime(true) - $startTime;
                }

                if ($bijac->type == 'gcoms' && $ocr->invoice && $ocr->invoice->weight) { //فله
                    $ocr->type = 'gcoms';
                    $ocr['total_weight'] = $ocr->invoice->weight / 1000;
                    $timeTok["map"]["after find total_weight usually"][] = microtime(true) - $startTime;

                    // در محاسبه زیر وزن بار ماشین در حال ورود وارد نمیشود، چون وزن کشی بعدا انجام میشود
                    // $ocr['outed_weight'] = round(
                    //     GcomsOutData::where('customNb', $ocr->invoice->kutazh)
                    //         ->where('created_at', '<=', $ocr->log_time ?? now())
                    //         ->sum('weight') / 1000,
                    //     2
                    // );
                    $customNb = $ocr->invoice->kutazh;
                    $logTime = $ocr->log_time;
                    $timeKey = $logTime;
                    $outedWeightKey = "outed_weight:{$customNb}_{$timeKey}";
                    if (!Cache::has($outedWeightKey)) {
                        $weightSum = GcomsOutData::where('customNb', $customNb)
                            ->where('created_at', '<=', $logTime)
                            ->sum('weight');
                        $outedWeightValue = round($weightSum / 1000, 2);
                        Cache::put($outedWeightKey, $outedWeightValue, now()->addMinutes(15));
                    } else {
                        $outedWeightValue = Cache::get($outedWeightKey);
                    }
                    $ocr['outed_weight'] = $outedWeightValue;
                    $timeTok["map"]["after find outed_weight usually"][] = microtime(true) - $startTime;
                }
            }

            // log::info("gate:{$request->gate}_Operation took : " . microtime(true) - $startTime . " ditailes :" . json_encode($timeTok));

            return $ocr;
        });
        $timeTok["after ocrMatches->map"] = microtime(true) - $startTime;

        // logCheck ->
        if ($logwright)
            log::info("gate:{$request->gate}_Operation took : " . microtime(true) - $startTime . " ditailes :" . json_encode($timeTok));
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
            \Log::error('update_customCheck error: ' . $e->getMessage(), [
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
            \Log::error('❌ خطا در addBaseInvoice:', ['error' => $e->getMessage()]);
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
