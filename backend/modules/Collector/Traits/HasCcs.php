<?php
namespace Modules\Collector\Traits;

use Modules\Collector\Models\CcsData;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCcs
{
    public function ccsData(): MorphToMany
    {
        return $this->morphToMany(CcsData::class, 'ccs_able', 'ccs_able', 'ccs_able_id', 'ccs_data_id');
    }

    public function getTabarInvoiceAttribute()
    {
        return $this->ccsData()->whereHas('tabarInvoice')->first()->tabarInvoice ?? null;
    }

    public function getCcsHasTabarInvoiceAttribute()
    {
        return $this->ccsData()->whereHas('tabarInvoice')->first() ?? null;
    }
}
