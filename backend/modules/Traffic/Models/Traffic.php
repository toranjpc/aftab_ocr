<?php

namespace Modules\Traffic\Models;

use App\Models\Base;
use Modules\Gcoms\Models\GcomsOutNotPermission;
use Illuminate\Support\Facades\DB;

use modules\BijacInvoice\Models\Bijac;

class Traffic extends Base
{
    protected $fillable = [
        // 'vehicle_image_front_url',
        // 'vehicle_image_back_url',
        // 'vehicle_image_left_url',
        // 'vehicle_image_right_url',
        // 'plate_image_url',
        // 'plate_type',
        // 'plate_number',
        // 'plate_number_2',
        // 'vehicle_type',
        // 'camera_number',
        // 'direction',
        // 'gate_number',
        // 'log_time',
        // 'exit_time',
        // 'data',
        // 'parent_id',
        // 'container_code_image_url',
        // 'container_code',
        // 'container_code_2',
        // 'ocr_accuracy',
        // 'IMDG',
        // 'seal',
        'parent_id',
        'vehicle_image_front_url',
        'vehicle_image_back_url',
        'vehicle_image_left_url',
        'vehicle_image_right_url',
        'plate_image_url',
        'container_code_image_url',
        "direction",
        "log_time",
        "exit_time",
        "camera_number",
        "gate_number",
        'IMDG',
        'seal',
        "container_type",
        "vehicle_type",
        "plate_number",
        "plate_number_edit",
        "plate_number_2",
        "vehicle_location",
        "plate_type",
        "container_code",
        "container_code_edit",
        "container_code_2",
        "container_code_3",
        "coordinate",
        "container_size",
        "ocr_accuracy",
        "frequency",
        "plate_reading_status",
        "data",
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function parent()
    {
        return $this->belongsTo(
            Traffic::class,
            'parent_id'
        );
    }

    public function isCustomCheck()
    {
        return $this->hasOne(Log::class, 'table_id')
            ->select("user_id", "table_id")
            ->where('table_name', 'OcrMatch')
            ->where('log_type', 'checked');
    }

    public function bijacs()
    {
        return $this->belongsToMany(
            Bijac::class,
            'traffic_bijac_invoice',
            'traffic_id',
            'bijac_id'
        );
    }
    public function getInvoiceAttribute()
    {
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
        // return $this->bijacs()->whereHas('invoice')->first()->invoice ?? null;
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
    public function getBijacHasInvoiceAttribute()
    {
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
    }




    public function getContainerCodeStandardAttribute()
    {
        $code = $this->container_code;

        if (!$code)
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

        if (!$code)
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

        if (!$code)
            return '';

        $split = explode("_", $code);
        // $words = substr($code, 0, 4);
        // $digits = substr($code, 4, 6);

        $words = $split[0] ?? '';
        $digits = $split[1] ?? '';

        return $words . ' ' . $digits;
    }
}
