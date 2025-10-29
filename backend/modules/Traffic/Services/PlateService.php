<?php

namespace Modules\Traffic\Services;

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

    function preNormalizeRawPlate($plate)
    {
        // مرحله 1: پاک‌سازی فضای خالی و نشانه‌ها
        $plate = strtoupper(trim($plate));
        $plate = str_replace(
            [' ', '-', '_', '.', ',', '/', '\\', 'ـ', '|'],
            '',
            $plate
        );

        // مرحله 2: تبدیل ارقام فارسی به انگلیسی
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $plate = str_replace($persianDigits, $englishDigits, $plate);

        // مرحله 3: نگاشت حروف فارسی ↔ انگلیسی
        // کمک می‌کنه اگر کاربر "الف" یا "A" بنویسه، یکسان بشه
        $map = [
            'الف' => 'A',
            'ب' => 'B',
            'ج' => 'C',
            'د' => 'D',
            'س' => 'E',
            'ص' => 'S',
            'ط' => 'T',
            'ع' => 'O',
            'ق' => 'Q',
            'ل' => 'L',
            'م' => 'M',
            'ن' => 'N',
            'هـ' => 'H',
            'ه' => 'H',
            'ی' => 'Y',
            'پ' => 'P',
            'ر' => 'R',
            'ش' => 'X',
            'ت' => 'T',
            'ز' => 'Z',
        ];
        $plate = strtr($plate, $map);

        // مرحله 4: حذف کاراکترهای غیرالفانامریک (بعد از نگاشت)
        $plate = preg_replace('/[^A-Z0-9]/u', '', $plate);

        // مرحله 5: تصحیح حالت‌های ناقص (حذف حروف یا اعداد ناقص)
        // مثل 22_ یا 22-- → تبدیل به عدد خالص برای جستجوی جزئی
        if (preg_match('/^\d{1,5}$/', $plate)) {
            // صرفاً عددی، حالت ناقص – بزار همون بمونه
            return $plate;
        }

        return $plate;
    }

    // نرمال‌سازی کلی پلاک: حذف نویز، تشخیص نوع (ایرانی/افغانی)، نرمال‌سازی مطابق الگو
    public function normalizePlate_(string $plate): ?string
    {
        $standardizedPlate = $this->standardizePlate($plate);
        $plateType = $this->determinePlateType($standardizedPlate);

        return match ($plateType) {
            'iran' => $this->normalizeIranPattern($standardizedPlate),
            'afghan' => $this->normalizeAfghanPattern($standardizedPlate),
            default => $this->extractDigits($plate)
        };
    }

    public function normalizePlate(string $plate): ?string
    {
        $clean = $this->preNormalizeRawPlate($plate);
        $normalized = $this->normalizePlate_($clean);

        return $normalized;
    }

    // تمیزکردن رشته ورودی: حروف کوچک، حذف کاراکترهای غیرمجاز (فاصله، علامت و ...)
    private function standardizePlate(string $plate): string
    {
        $plate = strtolower(trim($plate));
        return str_replace(
            array_keys($this->standardReplacements),
            array_values($this->standardReplacements),
            $plate
        );
    }

    // استخراج فقط اعداد از ورودی (حذف تمام حروف و نمادها)
    public function extractDigits(string $input): string
    {
        return preg_replace('/\D/', '', $input);
    }

    // استخراج فقط حروف از ورودی (حذف تمام اعداد)
    private function extractLetters(string $input): string
    {
        return preg_replace('/\d/', '', $input);
    }

    // ساخت فرمت استاندارد برای پلاک ایرانی با افزودن 'ein' بین دو بخش عددی
    private function normalizeIranPattern(string $plate): string
    {
        $digits = $this->extractDigits($plate);
        $firstPart = substr($digits, 0, 2);
        $secondPart = substr($digits, 2);

        return $firstPart . 'ein' . $secondPart;
    }

    // ساخت ساختار استاندارد برای پلاک افغانی با تبدیل نام ولایت به کد و افزودن پسوند ',L'
    private function normalizeAfghanPattern(string $plate): string
    {
        $digits = $this->extractDigits($plate);
        $provinceCode = $this->extractLetters($plate);
        $normalizedProvince = $this->normalizeAfghanProvince($provinceCode);

        return $normalizedProvince . ',' . $digits . ',L';
    }

    // تبدیل نام ولایت (فارسی یا انگلیسی) به کد سه‌حرفی استاندارد از آرایه afghanProvinces
    private function normalizeAfghanProvince(string $province): string
    {
        return $this->afghanProvinces[$province] ?? '';
    }

    // تشخیص نوع پلاک بر اساس regexهای تعریف‌شده (ایران، افغانستان یا سایر)
    private function determinePlateType(string $plate): string
    {
        // استخراج عدد و حروف
        $digits = preg_replace('/\D/', '', $plate);
        $letters = preg_replace('/\d/', '', $plate);
        $digitCount = strlen($digits);
        $letterCount = strlen($letters);

        // 1️⃣ پلاک‌های ۳ یا ۴ رقمی → حتماً افغان
        if (in_array($digitCount, [3, 4])) {
            return 'afghan';
        }

        // 2️⃣ پلاک‌های ۷ رقمی → حتماً ایران
        if ($digitCount === 7) {
            return 'iran';
        }

        // 3️⃣ پلاک‌های ۵ رقمی
        if ($digitCount === 5) {

            // استثناهای ایران → اگر EE, AA, E, A دارد
            if (preg_match('/(ee|aa|\be\b|\ba\b)/i', $plate)) {
                return 'iran';
            }

            // شرط افغان:
            // - شامل حرف L یا یکی از حروف فارسی
            // - یا سه حرف انگلیسی متوالی (مثل HRT, BDN)
            if (
                str_contains($plate, 'l') ||
                preg_match('/[\x{0600}-\x{06FF}]{2,}/u', $plate) ||
                preg_match('/[a-zA-Z]{3,}/', $plate)
            ) {
                return 'afghan';
            }

            // بقیه‌ی ۵ رقمی‌ها → ایران
            return 'iran';
        }

        // 4️⃣ بررسی ترانزیت:
        // اگر حروف داخل پلاک در لیست ولایت‌های افغان نباشند ولی وجود داشته باشند
        if ($letterCount > 0) {
            $lettersUpper = strtoupper($letters);
            if (!in_array($lettersUpper, $this->afghanProvinces)) {
                return 'transit';
            }
        }

        // بررسی نهایی طبق regex تعریف‌شده در الگوهای قبلی
        foreach ($this->platePatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $plate)) {
                    return $type;
                }
            }
        }

        // بررسی نام ولایت‌های افغان
        if ($this->isAfghanProvincePattern($plate)) {
            return 'afghan';
        }

        // در غیر این صورت پلاک ناشناخته
        return 'other';
    }

    // بررسی اینکه آیا پلاک دارای الگوی مرتبط با نام ولایت‌های افغانستان است یا نه
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
