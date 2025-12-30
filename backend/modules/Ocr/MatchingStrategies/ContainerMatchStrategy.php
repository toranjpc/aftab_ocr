<?php

namespace Modules\Ocr\MatchingStrategies;

use Modules\BijacInvoice\Models\Bijac;
use Modules\Ocr\Models\OcrLog;
use Modules\BijacInvoice\Services\BijacSearchService;
use Modules\Ocr\Models\OcrMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContainerMatchStrategy extends MatchBase implements MatchStrategyInterface
{
    public function match(OcrLog $ocr, $matches, BijacSearchService $bijacService): bool
    {
        if (!$ocr->container_code)
            return false;

        $code = $ocr->container_code;

        $match = $matches->where('container_code_3', substr($code, 0, 11))->where('gate_number', $ocr->gate_number)->first();

        if ($match) {
            $this->fillMatchFromOcr($match, $ocr, [
                'container_code_image_url',
                'vehicle_image_back_url',
                'container_code',
                'container_code_2',
                'IMDG',
                'seal',
            ]);
            return true;
        }

        $numbers = substr(preg_replace('/\D/', '', $code), 0, 6);

        if (strlen($numbers) == 6) {
            $match = $matches->whereNotNull('container_code_3')
                ->filter(
                    fn($b) =>
                    str_contains(
                        $b->container_code_3,
                        $numbers
                    )
                )
                ->where('gate_number', $ocr->gate_number)
                ->first();
        }


        if ($match) {
            $this->fillMatchFromOcr($match, $ocr, [
                'container_code_image_url',
                'vehicle_image_back_url',
                'container_code',
                'container_code_2',
                'IMDG',
                'seal',
            ]);
            return true;
        }

        $bijacs = $bijacService::getContainerBijacs($ocr);
        $bijac = $bijacs[0] ?? null;

        if ($bijac) {
            $plate = $bijac->plate_normal ?? $bijac->plate;
            $match = $matches->where('plate_number_3', $plate)
                ->where('gate_number', $ocr->gate_number)
                // ->where(function($q) use ($bijac) {
                //     $q->whereDoesntHave('bijacs')
                //       ->orWhereHas('bijacs', function($q2) use ($bijac) {
                //           $q2->where('id', $bijac->id);
                //       });
                // })
                ->first();

            if ($match) {
                $this->fillMatchFromOcr($match, $ocr, [
                    // 'plate_number_3'   => $bijac->plate_normal ?? $bijac->plate,
                    'container_code_3' => str_replace(' ', '', $bijac->container_number),
                    'container_code_image_url',
                    'vehicle_image_back_url',
                    'container_code',
                    'container_code_2',
                    'IMDG',
                    'seal',
                ]);

                $match->bijacs()->sync($bijacs->pluck('id'));
                return true;
            }

            // if (Carbon::parse($bijac->bijac_date)->diffInDays(Carbon::parse($ocr->log_time), false) < 3) {

            //     $this->createOrUpdateMatch($ocr, $bijac, $bijacs);
            //     return true;
            // }
            foreach (
                $matches->whereNull('container_code')
                    ->filter(
                        fn($b) => ($b->container_code_3 &&
                            $b->plate_number_3) ||
                            (!$b->container_code_3 &&
                                !$b->plate_number_3)

                    )
                    ->where('gate_number', $ocr->gate_number)
                    ->take(3) as $match
            ) {

                if (
                    // (
                    //     $match->container_code_3 &&
                    //     $this->isSimilarContainer(
                    //         $bijac->container_number,
                    //         $match->container_code_3
                    //     )
                    // )
                    // || 
                    $this->isPlateMatchPossible($bijac, $match)
                ) {
                    $this->fillMatchFromOcr($match, $ocr, [
                        'plate_number_3' => $bijac->plate_normal ?? $bijac->plate,
                        'container_code_3' => str_replace(' ', '', $bijac->container_number),
                        'container_code_image_url',
                        'vehicle_image_back_url',
                        'container_code',
                        'container_code_2',
                        'IMDG',
                        'seal',
                    ]);

                    $match->bijacs()->sync($bijacs->pluck('id'));

                    return true;
                }
            }

            if (strlen(preg_replace('/\D/', '', $bijac->plate_normal)) == 5 && $matches[0]->plate_type == 'iran' && strlen(preg_replace('/\D/', '', $matches[0]->plate_number)) > 4 && str_contains(preg_replace('/\D/', '', $bijac->plate_normal), substr(preg_replace('/\D/', '', $matches[0]->plate_number), 0, -2))) {
                $this->fillMatchFromOcr($matches[0], $ocr, [
                    'plate_number_3' => $bijac->plate_normal ?? $bijac->plate,
                    'container_code_3' => str_replace(' ', '', $bijac->container_number),
                    'container_code_image_url',
                    'vehicle_image_back_url',
                    'container_code',
                    'container_code_2',
                    'IMDG',
                    'seal',
                ]);

                if (!$match) {
                    Log::warning("ContainerMatchStrategy: match is null for OCR ID: {$ocr->id}, container_code: {$ocr->container_code}");
                    return false;
                }

                $match->bijacs()->sync($bijacs->pluck('id'));

                return true;
            }
        }

        if (!$bijac)
            foreach ($matches->whereNotNull('container_code_3')->where('gate_number', $ocr->gate_number)->take(3) as $match) {
                if ($this->isSimilarContainer($ocr->container_code, $match->container_code_3)) {
                    $this->fillMatchFromOcr($match, $ocr, [
                        'container_code_image_url',
                        'vehicle_image_back_url',
                        'container_code',
                        'container_code_2',
                        'IMDG',
                        'seal',
                    ]);

                    return true;
                }
            }


        $this->createOrUpdateMatch($ocr, $bijac, $bijacs);

        return true;
    }
}
