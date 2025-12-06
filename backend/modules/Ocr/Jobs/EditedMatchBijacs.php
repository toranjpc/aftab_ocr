<?php

namespace Modules\Ocr\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Ocr\Models\OcrMatch;
use Modules\BijacInvoice\Services\BijacSearchService;
use Illuminate\Support\Facades\Log;

// class EditedMatchBijacs implements ShouldQueue
class EditedMatchBijacs
{
    use Dispatchable, InteractsWithQueue;

    // private const LEVENSHTEIN_THRESHOLD = 3;
    // const MATCH_DAY = 7;

    protected $matchId;

    public function __construct($matchId)
    {
        $this->matchId = $matchId;
    }

    public function handle(BijacSearchService $bijacService)
    {
        $match = OcrMatch::findOrFail($this->matchId);

        /*
        if (!in_array($match->match_status, [
            'container_without_bijac',
            'no_bijac',
            'gcoms_nok',
            'miss_container_ccs_nok',


            // "gcoms_ok",
            // "ccs_ok",
            // "plate_ccs_ok",
            // "container_ccs_ok",
            "gcoms_nok",
            "ccs_nok",
            "plate_ccs_nok",
            "container_ccs_nok",
            "plate_without_bijac",
            "container_without_bijac",
            "gcoms_nok_req",
            "ccs_nok_req",
            "plate_ccs_nok_req",
            "container_ccs_nok_req",
            "plate_without_bijac_req",
            "container_without_bijac_req",
            "gcoms_nok_Creq",
            "ccs_nok_Creq",
            "plate_ccs_nok_Creq",
            "container_ccs_nok_Creq",
            "plate_without_bijac_Creq",
            "container_without_bijac_Creq",

        ]))
            return;
        */
        /*
        $text = $match->match_status;
        $keywords = ['_without', '_nok', 'no_bijac'];
        $found = false;
        foreach ($keywords as $word) {
            if (str_contains($text, $word)) {
                $found = true;
                break;
            }
        }
        if (!$found) return;
        */


        $bijacs = $bijacService::getBijacs($match, true); //پیدا کردن بیجک های یک پلاک یا کانتینر
        if (!$bijacs || !isset($bijacs[0])) return;
        try {
            log::info("EditedMatchBijacs ({$match->id}) palte recived : " . $match->plate_number_edit ?? $match->container_code_edit . "  - bijacs {$bijacs->count()}");
        } catch (\Throwable $th) {
        }

        $bijac = $bijacs[0];

        $similar = false;

        if ($bijac->type == 'ccs') {
            // $containerNumber = str_replace(' ', '', $bijac->container_number);
            $containerNumberBase = str_replace(' ', '', $bijac->container_number);
            $containerNumber = substr(preg_replace('/\D/', '', $bijac->container_number), 0, 6);

            // $similar = OcrMatch::where(function ($query) use ($match) {
            //     $query->whereBetween('id', [$match->id - 5, $match->id - 1])
            //         ->orWhereBetween('id', [$match->id + 1, $match->id + 5]);
            // })
            $similar = OcrMatch::where(function ($query) use ($match) {
                $query->whereBetween('id', [$match->id - 5, $match->id + 5]);
                $query->where('id', "!=", $match->id);
            })
                ->where(function ($query) use ($containerNumber, $containerNumberBase) {
                    // $query->whereRaw("REGEXP_REPLACE(container_code_3, '[^a-zA-Z0-9]', '') LIKE ?", [$containerNumberBase]);
                    $query->where('container_code_3', $containerNumberBase);
                    $query->orWhere('container_code', 'LIKE', '%' . $containerNumber . '%');
                })
                ->orderByRaw('CASE WHEN container_code_3 = ? THEN 0 ELSE 1 END', [$containerNumber])
                ->orderByRaw('CAST(id AS SIGNED) - ?', [$match->id])
                ->first();

            try {
                log::info("EditedMatchBijacs ({$match->id}) srech by this continer code : " . $containerNumber . "  - search continer");
            } catch (\Throwable $th) {
            }
        }

        if (!$similar) {
            // $plate = $bijac->plate_normall ?? $bijac->plate;
            $plate = $bijac->plate_normall;

            // $similar = OcrMatch::where(function ($query) use ($match) {
            //     $query->whereBetween('id', [$match->id - 5, $match->id - 1])
            //         ->orWhereBetween('id', [$match->id + 1, $match->id + 5]);
            // })
            $similar = OcrMatch::where(function ($query) use ($match) {
                $query->whereBetween('id', [$match->id - 5, $match->id + 5]);
                $query->where('id', "!=", $match->id);
            })
                ->where(function ($query) use ($plate) {
                    $query->where('plate_number_3', $plate)
                        ->orWhere('plate_number', 'LIKE', '%' . preg_replace('/\D/', '', $plate) . '%');
                })
                ->orderByRaw('CASE WHEN plate_number_3 = ? THEN 0 ELSE 1 END', [$plate])
                ->orderByRaw('CAST(id AS SIGNED) - ?', [$match->id])
                ->first();

            try {
                log::info("EditedMatchBijacs ({$match->id}) srech by this continer code : " . $plate . "  - search place");
            } catch (\Throwable $th) {
            }
        }

        if ($similar)
            if ($similar->plate_number_3 || $similar->id < $match->id) {
                $similar->update([
                    'plate_number' => $similar->plate_number ?? $match->plate_number,
                    'plate_number_3' => $similar->plate_number_3 ?? $bijac->plate_normall ?? $bijac->plate,
                    'plate_number_edit' => $similar->plate_number_edit ?? $match->plate_number_edit,
                    'vehicle_image_front_url' => $similar->vehicle_image_front_url ?? $match->vehicle_image_front_url,
                    'plate_image_url' => $similar->plate_image_url ?? $match->plate_image_url,
                    'plate_type' => $similar->plate_type ?? $match->plate_type,
                    'container_code_image_url' => $similar->container_code_image_url ?? $match->container_code_image_url,
                    'vehicle_image_back_url' => $similar->vehicle_image_back_url ?? $match->vehicle_image_back_url,
                    'container_code' => $similar->container_code ?? $match->container_code,
                    'container_code_3' => $similar->container_code_3 ?? str_replace(' ', '', $bijac->container_number),
                    'container_code_2' => $similar->container_code_2 ?? $match->container_code_2,
                    'container_code_edit' => $similar->container_code_edit ?? $match->container_code_edit,
                    'IMDG' => $similar->IMDG ?? $match->IMDG,
                    'seal' => $similar->seal ?? $match->seal
                ]);

                $similar->bijacs()->sync($bijacs->pluck('id'));
                // if (!$similar->plate_number_3) {
                // $similar->bijacs()->sync($bijacs->pluck('id'));
                // Bijac::whereIn('id', $bijacs->pluck('id')->toArray())
                //     ->increment('revoke_number');
                // }

                $match->delete();
            } else {
                $match->update([
                    'plate_number' => $match->plate_number ?? $similar->plate_number,
                    'plate_number_3' => $match->plate_number_3 ?? $bijac->plate_normall ?? $bijac->plate,
                    'plate_number_edit' => $match->plate_number_edit ?? $similar->plate_number_edit,
                    'vehicle_image_front_url' => $match->vehicle_image_front_url ?? $similar->vehicle_image_front_url,
                    'plate_image_url' => $match->plate_image_url ?? $similar->plate_image_url,
                    'plate_type' => $match->plate_type ?? $similar->plate_type,
                    'container_code_image_url' => $match->container_code_image_url ?? $similar->container_code_image_url,
                    'vehicle_image_back_url' => $match->vehicle_image_back_url ?? $similar->vehicle_image_back_url,
                    'container_code' => $match->container_code ?? $similar->container_code,
                    'container_code_3' => $match->container_code_3 ?? str_replace(' ', '', $bijac->container_number),
                    'container_code_2' => $match->container_code_2 ?? $similar->container_code_2,
                    'container_code_edit' => $match->container_code_edit ?? $similar->container_code_edit,
                    'IMDG' => $match->IMDG ?? $similar->IMDG,
                    'seal' => $match->seal ?? $similar->seal
                ]);

                $match->bijacs()->sync($bijacs->pluck('id'));
                // Bijac::whereIn('id', $bijacs->pluck('id')->toArray())
                //     ->increment('revoke_number');

                $similar->delete();
            }

        else {
            $match->update([
                'plate_number_3' => $bijac->plate_normal ?? $bijac->plate,
                'container_code_3' => str_replace(' ', '', $bijac->container_number),
            ]);
            $match->bijacs()->sync($bijacs->pluck('id'));
        }
    }
}
