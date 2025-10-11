<?php

namespace Modules\BijacInvoice\Services;

class PlateService
{
    protected $afghanProvinces = [
        'پکیتا' => 'PTA',
        'پکتیا' => 'PTA',
        'زابل' => 'ZBL',
        'نیمروز' => 'NAZ',
        'نيمروز' => 'NAZ',
        'کندهار' => 'KDR',
        'قندهار' => 'KDR',
        'هرات' => 'HRT',
        'لغمان' => 'LGH',
        'لقمان' => 'LGH',
        'هلمند' => 'HEL',
        'هلمن' => 'HEL',
        'خوست' => 'KST',
        'وردگ' => 'WDK',
        'کندز' => 'KDZ',
        'فراه' => 'FRH',
        'فرا' => 'FRH',
        'تخار' => 'TAK',
        'کابل' => 'KBL',
        'لوگر' => 'LOG',
        'بغلان' => 'BGL',
        'سمنگان' => 'SMG',
        'پروان' => 'PRN',
        'بروان' => 'PRN',
        'بادغیس' => 'BDG',
        'بامیان' => 'BAM',
        'غور' => 'GHR',
        'وردک' => 'WDK',
        'ننگرهار' => 'NGR',
        'غزنی' => 'GAZ',
        'smg' => 'SMG',
        'bgl' => 'BGL',
        'lghman' => 'LGH',
        'lgh' => 'LGH',
        'pta' => 'PTA',
        'zbl' => 'ZBL',
        'zabol' => 'ZBL',
        'naz' => 'NAZ',
        'kdr' => 'KDR',
        'hrt' => 'HRT',
        'harat' => 'HRT',
        'herat' => 'HRT',
        'lhrt' => 'HRT',
        'hel' => 'HEL',
        'kst' => 'KST',
        'wdk' => 'WDK',
        'kdz' => 'KDZ',
        'frh' => 'FRH',
        'tak' => 'TAK',
        'kbl' => 'KBL',
        'kabol' => 'KBL',
        'log' => 'LOG',
        'h' => 'HRT',
        'nimrooz' => 'NAZ',
        'nimruz' => 'NAZ',
    ];

    protected $standardReplacements = [
        '-' => '',
        '.' => '',
        '`' => '',
        ' ' => '',
        '/' => '',
        '\\' => '',
        'A' => 'a',
        ',,' => '',
        ',' => '',
        'qa' => '',
        '..' => '',
        '...' => '',
        ' l' => 'l',
        '.l' => 'l',
        '-l' => 'l',
        '،' => '',
        'zz' => '',
        '_' => ''
    ];

    protected $platePatterns = [
        'iran' => [
            '/^\d{6,7}$/',
            '/^\d{2}e\d{3}$/',
            '/^\d{2}a\d{3}(?:\-\d{2})?$/',
            '/^\d{2}[aàxq]{2,3}\d{5}$/',
            '/^\d{5}\D\d{2}$/',
            '/^\d{2}\D\d{5}$/',
            '/^\d{2}\D\d{3}\D\d{1,2}$/',
            '/^\d{2}\D-\d{3}\-\d{2}$/',
            '/^(?=(?:.*\d){7})(?!.*\d{8,}).*$/'
        ],
        'afghan' => [
            '/^l\d{3,5}$/u',
            '/^\d{3,5}l$/u',
            '/^\d{3}h$/u',
            '/^\d{3}$/u',
            '/^\d{5}[-]?[\x{0600}-\x{06FF}]+$/u',
            '/^(?=(?:.*\d){3,4})(?!.*\d{5,}).*$/u'
        ]
    ];

    public function normalizePlate(string $plate): ?string
    {
        $standardizedPlate = $this->standardizePlate($plate);
        $plateType = $this->determinePlateType($standardizedPlate);

        return match ($plateType) {
            'iran' => $this->normalizeIranPattern($standardizedPlate),
            'afghan' => $this->normalizeAfghanPattern($standardizedPlate),
            default => $this->extractDigits($plate)
        };
    }

    private function standardizePlate(string $plate): string
    {
        $plate = strtolower(trim($plate));
        return str_replace(
            array_keys($this->standardReplacements),
            array_values($this->standardReplacements),
            $plate
        );
    }

    private function extractDigits(string $input): string
    {
        return preg_replace('/\D/', '', $input);
    }

    private function extractLetters(string $input): string
    {
        return preg_replace('/\d/', '', $input);
    }

    private function normalizeIranPattern(string $plate): string
    {
        $digits = $this->extractDigits($plate);
        $firstPart = substr($digits, 0, 2);
        $secondPart = substr($digits, 2);

        return $firstPart . 'ein' . $secondPart;
    }

    private function normalizeAfghanPattern(string $plate): string
    {
        $digits = $this->extractDigits($plate);
        $provinceCode = $this->extractLetters($plate);
        $normalizedProvince = $this->normalizeAfghanProvince($provinceCode);

        return $normalizedProvince . ',' . $digits . ',L';
    }

    private function normalizeAfghanProvince(string $province): string
    {
        return $this->afghanProvinces[$province] ?? '';
    }

    private function determinePlateType(string $plate): string
    {
        foreach ($this->platePatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $plate)) {
                    return $type;
                }
            }
        }

        if ($this->isAfghanProvincePattern($plate)) {
            return 'afghan';
        }

        return 'other';
    }

    private function isAfghanProvincePattern(string $plate): bool
    {
        foreach (array_keys($this->afghanProvinces) as $province) {
            $patterns = [
                '/^' . preg_quote($province, '/') . '\d{3,5}$/u',
                '/^\d{3,5}' . preg_quote($province, '/') . '$/u',
                '/^' . preg_quote($province, '/') . '\d{3,5}[lل]$/u',
                '/^[lل]\d{3,5}' . preg_quote($province, '/') . '$/u',
                '/^\d{3,5}[lل]' . preg_quote($province, '/') . '$/u',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $plate)) {
                    return true;
                }
            }
        }

        return false;
    }
}
