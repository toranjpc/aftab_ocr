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

use Illuminate\Support\Facades\Log;
use Modules\Auth\Controllers\AuthController;

class OcrMatchController extends Controller
{
    public function getList(Request $request)
    {
        $id = time();
        $startTime = microtime(true);

        $ocrMatches = OcrMatch::with([
            'bijacs' => function ($query) {
                $query->withCount('ocrMatches')
                    ->with('invoices')
                    ->with('allbijacs')
                ;
            },
            "isCustomCheck"
        ]);
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
            ->paginate($request->itemPerPage ?? 15);

        $customTariff = config('ocr.custom_tariff');
        // if ($request->gate == 2) log::info("{$id}_Operation took vasat " . microtime(true) - $startTime . " seconds in gate {$request->gate}");

        $ocrMatches->map(function ($ocr) use ($customTariff, $request, $id, $startTime) {
            $ocr->append('invoice');
            $ocr->append('invoices');
            $ocr['total_vehicles'] = 0;
            $ocr['ocr_vehicles'] = 0;

            $bijac = $ocr->bijacs->sortByDesc('bijac_date')->first();
            // if ($request->gate == 2) log::info("{$id}_Operation took for bijac " . microtime(true) - $startTime . " seconds in gate {$request->gate}");

            if ($bijac && $bijac->receipt_number) {
                // $bijacs = Bijac::where('receipt_number', $bijac->receipt_number)->select("id", "receipt_number", "plate_normal")->get();
                $bijacs = $bijac->allbijacs;

                $bijacIds = $bijacs->pluck('id');

                // $ocr['total_vehicles'] = $bijacs->count();
                $ocr['total_vehicles'] = $bijacs->pluck('plate_normal')->unique()->count();


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
                // if ($request->gate == 2) log::info("{$id}_Operation took for allbijacs " . microtime(true) - $startTime . " seconds in gate {$request->gate}");
                $ocr['ocr_vehicles'] = DB::table('bijacables')
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

                // if ($request->gate == 2) log::info("{$id}_Operation took for ocr_vehicles (" . $ocr['ocr_vehicles'] . ") " . microtime(true) - $startTime . " seconds in gate {$request->gate}");


                if ($bijac->type == 'ccs' && $ocr->invoice) { //کانتینر
                    // $Invoicebase = $ocr->invoicebase;

                    $ocr['total_tu'] = round($ocr->invoice->amount / $customTariff);

                    $ocr['ocr_tu'] = Bijac::has('ocrMatches')
                        ->whereIn('id', $bijacIds)
                        ->selectRaw("SUM(CASE 
                                WHEN container_size = '_20Feet' THEN 1
                                WHEN container_size IN ('_40Feet', '_45Feet') THEN 2
                                ELSE 0 
                            END) as ocrTu")
                        ->value('ocrTu');
                }

                if ($bijac->type == 'gcoms' && $ocr->invoice && $ocr->invoice->weight) { //فله
                    $ocr->type = 'gcoms';
                    $ocr['total_weight'] = $ocr->invoice->weight / 1000;

                    // در محاسبه زیر وزن بار ماشین در حال ورود وارد نمیشود، چون وزن کشی بعدا انجام میشود
                    $ocr['outed_weight'] = round(
                        GcomsOutData::where('customNb', $ocr->invoice->kutazh)
                            ->where('created_at', '<=', $ocr->log_time ?? now())
                            ->sum('weight') / 1000,
                        2
                    );
                }
            }

            return $ocr;
        });

        // if ($request->gate == 2) log::info("{$id}_Operation took tamam " . microtime(true) - $startTime . " seconds in gate {$request->gate}");
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


        if ($plate_number_edit = data_get($request, 'OcrMatch.plate_number_edit', null)) {

            if ($this->checkPlateIsDuplicate([
                $plate_number_edit,
                $ocrMatch->gate_number
            ], 2, $ocrMatch->ocr_log_id)) {

                return response()->json([
                    'message' => 'شماره پلاک وارد شده قبلا ارسال شده است!'
                ], 422);
            }

            $request->merge([
                'plate_number_edit' =>  $plate_number_edit
            ]);
        }

        if ($container_code_edit = data_get($request, 'OcrMatch.container_code_edit', null)) {

            if ($this->checkIsDuplicateContainer([
                $container_code_edit,
                $ocrMatch->gate_number
            ], 2, $ocrMatch->ocr_log_id)) {

                return response()->json([
                    'message' => 'شماره کانتینر وارد شده قبلا ارسال شده است!'
                ], 422);
            }
            $request->merge([
                'container_code_edit' =>  str_replace(' ', '', $container_code_edit)
            ]);
        }

        if ($plate_number_edit || $container_code_edit) {
            $ocrMatch->update($request->only([
                'plate_number_edit',
                'container_code_edit'
            ]));

            return response()->json([
                'message' => 'با موفقیت ویرایش شد!',
                'data' => $ocrMatch->fresh() ? $ocrMatch->fresh()->load('bijacs')->append('invoice') : null,
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
                'data'    => $ocrMatch->fresh()
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
        if (empty($ocr->log_time)) $log_time = now();
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
