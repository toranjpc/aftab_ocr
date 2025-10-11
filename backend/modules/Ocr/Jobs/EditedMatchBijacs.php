<?php

namespace Modules\Ocr\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Ocr\Models\OcrMatch;
use Modules\BijacInvoice\Services\BijacSearchService;

// class EditedMatchBijacs implements ShouldQueue
class EditedMatchBijacs
{
    use Dispatchable, InteractsWithQueue;

    protected $matchId;

    public function __construct($matchId)
    {
        $this->matchId = $matchId;
    }

    public function handle(BijacSearchService $bijacService)
    {
        $match = OcrMatch::findOrFail($this->matchId);

        if (!in_array($match->match_status, [
            'container_without_bijac',
            'no_bijac',
            'gcoms_nok',
            'miss_container_ccs_nok',


            "gcoms_nok",
            "ccs_nok",
            "plate_ccs_nok",
            "container_ccs_nok",
            "plate_without_bijac",
            "container_without_bijac"
        ]))
            return;

        $bijacs = $bijacService::getBijacs($match, true);

        if ($bijacs && isset($bijacs[0])) {

            $bijac = $bijacs[0];

            $similar = false;

            if ($bijac->type == 'ccs') {
                $containerNumber = str_replace(' ', '', $bijac->container_number);

                $similar = OcrMatch::where(function ($query) use ($match) {
                    $query->whereBetween('id', [$match->id - 3, $match->id - 1])
                        ->orWhereBetween('id', [$match->id + 1, $match->id + 3]);
                })
                    ->where(function ($query) use ($containerNumber) {
                        $query->where('container_code_3', $containerNumber)
                            ->orWhere('container_code', 'LIKE', '%' . $containerNumber . '%');
                    })
                    ->orderByRaw('CASE WHEN container_code_3 = ? THEN 0 ELSE 1 END', [$containerNumber])
                    ->orderByRaw('CAST(id AS SIGNED) - ?', [$match->id])
                    ->first();
            }

            if (!$similar) {
                $plate = $bijac->plate_normall ?? $bijac->plate;

                $similar = OcrMatch::where(function ($query) use ($match) {
                    $query->whereBetween('id', [$match->id - 3, $match->id - 1])
                        ->orWhereBetween('id', [$match->id + 1, $match->id + 3]);
                })->where(function ($query) use ($plate) {

                    $query->where('plate_number_3', $plate)
                        ->orWhere('plate_number', 'LIKE', '%' . preg_replace('/\D/', '', $plate) . '%');
                })
                    ->orderByRaw('CASE WHEN plate_number_3 = ? THEN 0 ELSE 1 END', [$plate])
                    ->orderByRaw('CAST(id AS SIGNED) - ?', [$match->id])
                    ->first();
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

                    if (!$similar->plate_number_3) {
                        $similar->bijacs()->sync($bijacs->pluck('id'));
                        // Bijac::whereIn('id', $bijacs->pluck('id')->toArray())
                        //     ->increment('revoke_number');
                    }

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
                ]);
                $match->bijacs()->sync($bijacs->pluck('id'));
            }
        }
    }
}
