<?php

namespace Modules\Traffic\Models;

use App\Models\Base;
use Modules\Gcoms\Models\GcomsOutNotPermission;
use Illuminate\Support\Facades\DB;


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

    public function getContainerCodeStandardAttribute()
    {
        $code = $this->container_code;

        if (!$code) return '';

        $words = substr($code, 0, 4);
        $digits = substr($code, 4, 7);

        return [$words, $digits];
    }

    public function getContainerCodeStandard2Attribute()
    {
        $code = $this->container_code_2;
        if (!$code) return '';

        $words = substr($code, 0, 4);
        $digits = substr($code, 4, 6);

        return [$words, $digits];
    }
}
