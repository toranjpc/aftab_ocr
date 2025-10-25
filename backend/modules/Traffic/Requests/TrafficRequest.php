<?php

namespace Modules\Traffic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TrafficRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Log::debug(request()->all());
        // dd(request()->input('plate_number'));

        return  [
            'vehicle_image_front' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'vehicle_image_back' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'vehicle_image_left' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'vehicle_image_right' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'plate_image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'direction' => 'nullable|in:entry,exit',
            'plate_type' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',//required_without:container_code
            'plate_number_2' => 'nullable|string|max:255',
            'camera_number' => 'nullable|string|max:255',
            'gate_number' => 'nullable|string|max:255',
            'log_time' => 'nullable|date',
            'container_code_image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'container_code' => 'nullable|string|max:255',
            'container_code_2' => 'nullable|string|max:255',
            'ocr_accuracy' => 'nullable|numeric',
            'IMDG' => 'nullable|numeric',
            'seal' => 'nullable|numeric',
        ];
    }
}
