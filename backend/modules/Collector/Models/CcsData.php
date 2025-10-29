<?php

namespace Modules\Collector\Models;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CcsData extends Base
{
    protected $guarded = ['id'];

    public function tabariInvoice()
    {
        return $this->belongsTo(TabarInvoice::class, 'ReceiptNumber', 'ReceiptNumber');
    }


    public function tabarInvoice()
    {
        return $this->belongsTo(TabarInvoice::class, 'ReceiptNumber', 'ReceiptNumber');
    }
}
