<?php

namespace Modules\Collector\Models;

use App\Models\Base;

class TabarInvoiceContainer extends Base
{
    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(TabarInvoice::class, 'tabar_invoice_id');
    }
}
