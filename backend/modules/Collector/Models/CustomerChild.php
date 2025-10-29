<?php

namespace Modules\Collector\Models;

use App\Models\Base;

class CustomerChild extends Base
{
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
