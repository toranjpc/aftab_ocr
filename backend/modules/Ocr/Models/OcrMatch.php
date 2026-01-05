<?php

namespace Modules\Ocr\Models;

use App\Models\Base;
use App\Models\Log;
use Modules\BijacInvoice\Traits\HasBijac;
use Modules\Gcoms\Models\GcomsReport;

class OcrMatch extends Base
{
    use HasBijac;

    protected $fillable = [
        'ocr_log_id',
        'vehicle_image_front_url',
        'vehicle_image_back_url',
        'vehicle_image_left_url',
        'vehicle_image_right_url',
        'plate_image_url',
        'plate_type',
        'plate_number',
        'plate_number_2',
        'plate_number_3',
        'plate_number_edit',
        'vehicle_type',
        'camera_number',
        'gate_number',
        'log_time',
        'exit_time',
        'data',
        'container_code_image_url',
        'container_size',
        'container_type',
        'container_code',
        'container_code_edit',
        'container_code_2',
        'container_code_3',
        'container_code_validation',
        'IMDG', //کالای خطر ناک
        'seal', //پلمپ
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function isSerachBijac()
    {
        return $this->hasOne(Log::class, 'table_id')
            ->select("user_id", "table_id")
            ->where('table_name', 'ocr_matches')
            ->where('log_type', 'serachBijac');
    }

    public function isCustomCheck()
    {
        return $this->hasOne(Log::class, 'table_id')
            ->select("user_id", "table_id")
            ->where('table_name', 'ocr_matches')
            ->where('log_type', 'checked');
    }


    public function gcomsReport()
    {
        return $this->hasOne(GcomsReport::class);
    }

    // public function getInvoiceAttribute()
    // {
    //     $invoices = $this->bijacs
    //         ->flatMap
    //         ->invoices
    //         ->unique('id')
    //         ->sortBy([
    //             ['pay_date', 'desc'],
    //             ['request_date', 'desc'],
    //         ]);
    //     $preferred = $invoices->where('base', 1)->first();

    //     return $preferred ?? $invoices->first();
    // }
public function getInvoiceAttribute()
{
    $invoices = $this->bijacs
        ->flatMap
        ->invoices
        ->unique('id')
        ->sortByDesc(function ($invoice) {
            return [
                (int) $invoice->base,     // اول base=1
                $invoice->amount,         // بعد بیشترین amount
                $invoice->pay_date,       // بعد تاریخ پرداخت
                $invoice->request_date,   // بعد تاریخ ثبت
            ];
        });

    return $invoices->first();
}


    public function getBijacHasInvoiceAttribute()
    {
        try {
            $allInvoices = $this->bijacs->flatMap(function ($bijac) {
                return $bijac->invoices;
            });
            return $allInvoices
                ->unique('id')
                ->sortBy([
                    ['pay_date', 'desc'],
                    ['request_date', 'desc'],
                ])
                ->first() ??
                null;
            /*
        return $this->bijacs
            ->flatMap
            ->invoices
            ->unique('id')
            ->sortBy([
                ['pay_date', 'desc'],
                ['request_date', 'desc'],
            ])
            ->first() ??
            null;
        */
            // return $this->bijacs()->whereHas('invoice')->first() ?? null;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getInvoicesAttribute()
    {
        return $this->bijacs
            ->flatMap
            ->invoices
            ->unique('id')
            ->sortBy([
                ['pay_date', 'desc'],
                ['request_date', 'desc'],
            ])
            ->values()
            ->all();
    }

    public function getContainerCodeStandardAttribute()
    {
        $code = $this->container_code;

        if (!$code || !is_string($code))
            return '';

        $split = explode("_", $code);
        // $words = substr($code, 0, 4);
        // $digits = substr($code, 4, 6);

        $words = $split[0] ?? '';
        $digits = $split[1] ?? '';

        return $words . ' ' . $digits;
    }

    public function getContainerCodeStandard2Attribute()
    {
        $code = $this->container_code_2;

        if (!$code || !is_string($code))
            return '';

        $split = explode("_", $code);
        // $words = substr($code, 0, 4);
        // $digits = substr($code, 4, 6);

        $words = $split[0] ?? '';
        $digits = $split[1] ?? '';

        return $words . ' ' . $digits;
    }

    public function getContainerCodeEditStandardAttribute()
    {
        $code = $this->container_code_edit;

        if (!$code || !is_string($code))
            return '';

        $split = explode("_", $code);
        // $words = substr($code, 0, 4);
        // $digits = substr($code, 4, 6);

        $words = $split[0] ?? '';
        $digits = $split[1] ?? '';

        return $words . ' ' . $digits;
    }
}
