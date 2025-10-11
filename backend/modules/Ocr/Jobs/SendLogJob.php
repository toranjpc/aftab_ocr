<?php

namespace Modules\Ocr\Jobs;

use Modules\Ocr\Models\OcrMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Ocr\Models\OcrLog;
use Illuminate\Support\Facades\Log;

// class SendLogJob implements ShouldQueue
class SendLogJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $log;
    protected $receiver;

    public function __construct($log, $receiver)
    {
        $this->log = $log;
        $this->receiver = $receiver;
    }

    public function handle()
    {
        $log = OcrLog::with('parent')->find($this->log);

        $data = $this->makeStandardData($log);

        OcrMatch::updateOrCreate(
            ['ocr_log_id' => $log->id],
            $data
        );

        // Http::retry(3, 1000)->post($this->receiver->endpoint, $data);
    }

    public function makeStandardData($log)
    {
        $plateFields = [
            'vehicle_image_front_url',
            'vehicle_image_back_url',
            'vehicle_image_left_url',
            'vehicle_image_right_url',
            'plate_image_url',
            'plate_type',
            'plate_number',
            'plate_number_2',
            'plate_number_edit',
            'vehicle_type',
            'camera_number',
            'gate_number',
            'log_time',
            'exit_time',
            'data',
            'parent_id',
            'parent',
            'id'
        ];

        $containerFields = [
            'container_code_image_url',
            'vehicle_image_back_url',
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

        $data = $log->only(array_merge($plateFields, $containerFields));

        
        if (!$data['parent_id'])
            return $data;

        foreach ($containerFields as $field) {
            $data[$field] = data_get($data['parent'], $field);
        }

        $items = [
            '45G' => '40f',
            '22G' => '20f',
            'L5G' => '40f',
            '42G' => '40f',
        ];

        foreach ($items as $key => $value) {
            if (str_contains($data['container_code'] ?? '', $key)) {
                $data['container_size'] = $value;
            }
        }

        // $data['vehicle_image_back_url'] = data_get($data['parent'], 'vehicle_image_front_url');

        return $data;
    }
}
