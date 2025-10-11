<?php

namespace Modules\Ocr\MatchingStrategies;

use Modules\BijacInvoice\Models\Bijac;
use Modules\Ocr\Models\OcrMatch;

abstract class MatchBase
{
    protected function isSimilarContainer($code1, $code2): bool
    {
        $threshold = config('ocr.field_thresholds.container_code', config('ocr.levenshtein_threshold'));

        return $this->compare($code1, $code2, $threshold, 'levenshtein', 6);
    }

    protected function isSimilarPlate($plate1, $plate2): bool
    {
        // $threshold = config('ocr.field_thresholds.plate_number', config('ocr.levenshtein_threshold'));
        // $threshold = 26;
        $threshold = 21;

        return $this->compare($plate1, $plate2, $threshold, 'similar_text');
    }

    private function compare($val1, $val2, $threshold, $method = 'levenshtein', $limitLength = false): bool
    {
        if (!$val1 || !$val2)
            return false;

        // $method = config('ocr.comparison_method', 'levenshtein');

        [$val1, $val2] = $this->normalizeValues($val1, $val2, $limitLength);

        if ($method === 'similar_text') {
            similar_text($val1, $val2, $percent);
            return (100 - $percent) < $threshold;
        }

        // default = levenshtein
        return levenshtein($val1, $val2) < $threshold;
    }

    /**
     * نرمالایز کردن ورودی‌ها (پاکسازی کاراکترهای غیرعددی و محدود کردن طول)
     */
    private function normalizeValues(string $val1, string $val2, ?int $limitLength): array
    {
        $val1 = preg_replace('/\D/', '', $val1);
        $val2 = preg_replace('/\D/', '', $val2);

        if (!$limitLength)
            $limitLength = min(
                strlen($val2),
                strlen($val1)
            );

        $val1 = substr($val1, 0, $limitLength);
        $val2 = substr($val2, 0, $limitLength);

        return [$val1, $val2];
    }

    protected function isPlateMatchPossible($bijac, $match): bool
    {
        $bijacPlate = $bijac->plate_normal ?? $bijac->plate;
        $matchPlate = $match->plate_number_3 ?? $match->plate_number;

        return
            $this->isSimilarPlate($bijacPlate, $matchPlate) ||
            (
                strlen($matchPlate) > 3 &&
                str_contains($bijacPlate, $matchPlate)
            );

        // return empty($match->container_code) &&
        //     empty($match->container_code_3) &&
        //     $this->isSimilarPlate($bijac->plate_normal ?? $bijac->plate, $match->plate_number_3 ?? $match->plate_number);
    }

    protected function isContainerMatchPossible($bijac, $match): bool
    {
        return empty($match->plate_number_3) &&
            empty($match->plate_number) &&
            $this->isSimilarContainer($bijac->container_number, $match->container_code_3 ?? $match->container_code);
    }

    protected function fillMatchFromOcr($match, $ocr, array $fields)
    {
        $dataToUpdate = [];
        foreach ($fields as $key => $field) {
            if (is_string($key)) {
                $dataToUpdate[$key] = $field;
            } elseif (empty($match->$field) && isset($ocr->$field)) {
                $dataToUpdate[$field] = $ocr->$field;
            }
        }
        if (!empty($dataToUpdate)) {
            $match->update($dataToUpdate);
        }
    }

    protected function createOrUpdateMatch($ocr, $bijac = null, $bijacs = [])
    {
        $data = $ocr->toArray();

        if ($bijac) {
            $data['plate_number_3'] = $bijac->plate_normal ?? $bijac->plate;
            $data['container_code_3'] = str_replace(' ', '', $bijac->container_number);
        }

        $newMatch = OcrMatch::updateOrCreate(
            ['ocr_log_id' => $ocr->id],
            $data
        );

        if ($bijac) {
            $newMatch->bijacs()->sync($bijacs->pluck('id'));
            // Bijac::whereIn('id', $bijacs->pluck('id')->toArray())
            //     ->increment('revoke_number');
        }
    }
}
