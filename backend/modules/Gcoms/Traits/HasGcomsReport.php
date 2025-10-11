<?php

namespace Modules\Gcoms\Traits;

use Modules\Gcoms\Models\GcomsReport;

trait HasGcomsReport
{
    public function gcomsReport()
    {
        return $this->morphOne(GcomsReport::class, 'reportable');
    }

}