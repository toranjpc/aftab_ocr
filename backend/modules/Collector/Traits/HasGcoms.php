<?php
namespace Modules\Collector\Traits;

use Modules\Gcoms\Models\GcomsBijac;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasGcoms
{
    public function bijac(): MorphOne
    {
        return $this->morphOne(GcomsBijac::class, 'bijacable');
    }

    public function getGcomsDataAttribute()
    {
        return $this->ccsData()->whereHas('tabarInvoice')->first()->tabarInvoice ?? null;
    }

    public function getCcsHasTabarInvoiceAttribute()
    {
        return $this->ccsData()->whereHas('tabarInvoice')->first() ?? null;
    }
}
