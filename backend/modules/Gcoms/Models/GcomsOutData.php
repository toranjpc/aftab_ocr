<?php

namespace Modules\Gcoms\Models;

use App\Models\Base;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Models\User;
use Modules\BijacInvoice\Models\Invoice;
use Modules\Ocr\Models\OcrMatch;

class GcomsOutData extends Base
{
    protected $guarded = ['id'];

    public function gcomsInvoice()
    {
        return $this->belongsTo(GcomsInvoice::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();
        Event::listen(
            ['gcoms-out-data.beforeCreate'],
            function ($query) {
                if ($query->plate_number_id !== 0) {
                    $ocrLog = OcrMatch::where('plate_number', $query->plate_number)->orderBy('id', 'DESC')->first();
                    if ($ocrLog) {
                        $ocrLog->update(['valid_exit_gcoms' => 1]);
                    }
                }
            }
        );
    }
}
