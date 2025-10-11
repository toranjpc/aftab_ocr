<?php

namespace Modules\BijacInvoice\Traits;

use Modules\BijacInvoice\Models\Bijac;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

trait HasBijac
{
    public function bijacs(): MorphToMany
    {
        return $this->morphToMany(Bijac::class, 'bijacable', "bijacables");
    }

    /*
    public function getInvoiceAttribute()
    {
        return $this->bijacs()->whereHas('invoice')->first()->invoice ?? null;
    }

    public function getBijacHasInvoiceAttribute()
    {
        return $this->bijacs()->whereHas('invoice')->first() ?? null;
    }
    */
}
