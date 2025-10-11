<?php

namespace Modules\Ocr\MatchingStrategies;

use Modules\BijacInvoice\Models\Bijac;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\BijacInvoice\Services\BijacSearchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlateMatchStrategy extends MatchBase implements MatchStrategyInterface
{
    public function match(OcrLog $ocr, $matches, BijacSearchService $bijacService): bool
    {
        if (!$ocr->plate_number)
            return false;

        $plate = $ocr->plate_number;

        $match = $matches->where('plate_number_3', $plate)->first();

        if ($match) {
            $this->fillMatchFromOcr($match, $ocr, [
                'vehicle_image_front_url',
                'plate_image_url',
                'plate_type',
                'plate_number',
                'plate_number_2',
            ]);
            return true;
        } else if (strpos($plate, 'ein')) {
            $match = $matches->where('plate_number_3', substr($plate, 0, 8))->first();
            if ($match) {
                $this->fillMatchFromOcr($match, $ocr, [
                    'vehicle_image_front_url',
                    'plate_image_url',
                    'plate_type',
                    'plate_number',
                    'plate_number_2',
                ]);
                return true;
            }
        } else if (strpos($plate, ',L')) {
            $match = $matches->where('plate_number_3', substr($plate, 3))->first();
            if ($match) {
                $this->fillMatchFromOcr($match, $ocr, [
                    'vehicle_image_front_url',
                    'plate_image_url',
                    'plate_type',
                    'plate_number',
                    'plate_number_2',
                ]);
                return true;
            }
        }

        $numbers = preg_replace('/\D/', '', $plate);

        $match = $matches->where('plate_number_3', substr($numbers, 0, 5))->first();

        if ($match) {
            $this->fillMatchFromOcr($match, $ocr, [
                'vehicle_image_front_url',
                'plate_image_url',
                'plate_type',
                'plate_number',
                'plate_number_2',
            ]);
            return true;
        }

        $bijacs = $bijacService::getPlateBijacs($ocr);
        $bijac  = $bijacs[0] ?? null;

        if ($bijac && $bijac->type == 'gcoms') {
            $match = $matches->whereNotNull('plate_number')
                ->whereNull('plate_number_3')
                ->first();

            if ($match && $this->isSimilarPlate(
                $ocr->plate_number,
                $match->plate_number
            )) {
                $this->fillMatchFromOcr($match, $ocr, [
                    'vehicle_image_front_url',
                    'plate_image_url',
                    'plate_type',
                    'plate_number',
                    'plate_number_2',
                ]);
                return true;
            }

            $this->createOrUpdateMatch($ocr, $bijac, $bijacs);
            return true;
        }

        if ($bijac && $bijac->type === 'ccs') {
            $code = str_replace(' ', '', $bijac->container_number);
            $match = $matches->where('container_number_3', $code)->first();

            if ($match) {
                $this->fillMatchFromOcr($match, $ocr, [
                    'vehicle_image_front_url',
                    'plate_image_url',
                    'plate_type',
                    'plate_number',
                    'plate_number_2',
                ]);
                return true;
            }

            $numbers = substr(preg_replace('/\D/', '', $code), 0, 6);

            if (strlen($numbers) == 6)

                $match = $matches->whereNotNull('container_code_3')
                    ->filter(
                        fn($b) =>
                        str_contains(
                            $b->container_code_3,
                            $numbers
                        )
                    )
                    ->first();

            if ($match) {
                $this->fillMatchFromOcr($match, $ocr, [
                    'vehicle_image_front_url',
                    'plate_image_url',
                    'plate_type',
                    'plate_number',
                    'plate_number_2',
                ]);
                return true;
            }

            if (Carbon::parse($bijac->bijac_date)->diffInDays(Carbon::parse($ocr->log_time), false) < 3) {
                foreach ($matches->whereNull('plate_number_3')->take(3) as $match) {
                    if (
                        (
                            $match->plate_number &&
                            $this->isSimilarPlate(
                                $ocr->plate_number,
                                $match->plate_number
                            )
                        ) ||
                        $this->isContainerMatchPossible($bijac, $match)
                    ) {
                        $this->fillMatchFromOcr($match, $ocr, [
                            'plate_number_3'   => $bijac->plate_normal ?? $bijac->plate,
                            'container_code_3' => str_replace(' ', '', $bijac->container_number),
                            'vehicle_image_front_url',
                            'plate_image_url',
                            'plate_type',
                            'plate_number',
                            'plate_number_2',
                        ]);
                        $match->bijacs()->sync($bijacs->pluck('id'));

                        return true;
                    }
                }

                $lastPlate = $matches->take(3)
                    // ->whereNotNull('plate_number')
                    ->whereNotNull('plate_number_3')
                    ->first();

                if ($lastPlate) {

                    $b = $lastPlate->bijacs->first();
                    if (Carbon::parse($b->bijac_date)->diffInDays(Carbon::parse($lastPlate->log_time), false) > 3) {
                        if (
                            (
                                $lastPlate->plate_number &&
                                $this->isSimilarPlate(
                                    $ocr->plate_number,
                                    $lastPlate->plate_number
                                )
                            ) ||
                            $this->isContainerMatchPossible($bijac, $lastPlate)
                        ) {
                            $this->fillMatchFromOcr($lastPlate, $ocr, [
                                'plate_number_3'   => $bijac->plate_normal ?? $bijac->plate,
                                'container_code_3' => str_replace(' ', '', $bijac->container_number),
                                'vehicle_image_front_url',
                                'plate_image_url',
                                'plate_type',
                                'plate_number',
                                'plate_number_2',
                            ]);
                            $lastPlate->bijacs()->sync($bijacs->pluck('id'));

                            return true;
                        }
                    }
                }
            } else {
                $lastPlate = $matches->take(3)
                    // ->whereNotNull('plate_number')
                    ->whereNotNull('plate_number_3')
                    ->first();

                if ($lastPlate) {

                    $b = $lastPlate->bijacs->first();
                    if (Carbon::parse($b->bijac_date)->diffInDays(Carbon::parse($lastPlate->log_time), false) < 3) {
                        if (
                            (
                                $lastPlate->plate_number &&
                                $this->isSimilarPlate(
                                    $ocr->plate_number,
                                    $lastPlate->plate_number
                                )
                            ) ||
                            $this->isContainerMatchPossible($bijac, $lastPlate)
                        ) {
                            $this->fillMatchFromOcr($lastPlate, $ocr, [
                                'vehicle_image_front_url',
                                'plate_image_url',
                                'plate_type',
                                'plate_number',
                                'plate_number_2',
                            ]);
                            return true;
                        }
                    }
                }
            }
        }

        if (!$bijac)
            foreach ($matches->whereNotNull('plate_number_3')->take(3) as $match) {
                if (
                    $this->isSimilarPlate(
                        $ocr->plate_number,
                        $match->plate_number_3
                    )
                ) {
                    $this->fillMatchFromOcr($match, $ocr, [
                        'vehicle_image_front_url',
                        'plate_image_url',
                        'plate_type',
                        'plate_number',
                        'plate_number_2',
                    ]);

                    return true;
                }
            }

        $this->createOrUpdateMatch($ocr, $bijac, $bijacs);
        return true;
    }
}
