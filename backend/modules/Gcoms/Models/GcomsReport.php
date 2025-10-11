<?php

namespace Modules\Gcoms\Models;

use App\Models\Base;

class GcomsReport extends Base
{
    protected $guarded = ['id'];

    public function reportable()
    {
        return $this->morphTo();
    }
}
