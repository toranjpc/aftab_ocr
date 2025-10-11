<?php

namespace Modules\Ocr\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Modules\Ocr\Models\OcrMatch;

class TruckStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $log;
    public $type;

    public function __construct($log, $type)
    {
        $this->log = $log;
        $this->type = $type;
    }

    public function handle()
    {
        $match = OcrMatch::with(["bijacs" => function ($query) {
            $query->with('invoices');
        }])
            ->find($this->log);

        if ($match)

            if ($match->bijac_has_invoice) {
                if ($match->bijacs->first()->type === 'gcoms') {
                    return $match->forceFill([
                        'match_status' => 'gcoms_ok'
                    ])->save();
                } else {
                    if ($match->plate_number && $match->container_code)
                        return $match->forceFill([
                            'match_status' => 'ccs_ok'
                        ])->save();
                    else if ($match->plate_number)
                        return $match->forceFill([
                            'match_status' => 'plate_ccs_ok'
                        ])->save();
                    else
                        return $match->forceFill([
                            'match_status' => 'container_ccs_ok'
                        ])->save();
                }
            } else if ($bijac = $match->bijacs->first()) {


                // if (!cache()->get('truckstatus_reran_' . $this->log)) {
                //     cache()->put('truckstatus_reran_' . $this->log, true, 60);

                //     (new Modules\BijacInvoice\Clients\BijacApiClient::fetchBijacs($this->log, $type))->handle();

                //     return (new self($this->log, $this->type))->handle();
                // }


                if ($bijac->type === 'gcoms') {
                    return $match->forceFill([
                        'match_status' => 'gcoms_nok'
                    ])->save();
                } else {
                    if ($match->plate_number && $match->container_code)
                        return $match->forceFill([
                            'match_status' => 'ccs_nok'
                        ])->save();
                    else if ($match->plate_number)
                        return $match->forceFill([
                            'match_status' => 'plate_ccs_nok'
                        ])->save();
                    else
                        return $match->forceFill([
                            'match_status' => 'container_ccs_nok'
                        ])->save();
                }
            } else {
                if ($match->plate_number)
                    return $match->forceFill([
                        'match_status' => 'plate_without_bijac'
                    ])->save();
                else
                    return $match->forceFill([
                        'match_status' => 'container_without_bijac'
                    ])->save();
            }
    }
}
