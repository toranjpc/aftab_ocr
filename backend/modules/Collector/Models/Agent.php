<?php

namespace Modules\Collector\Models;

use App\Models\Base;

class Agent extends Base
{
    protected $guarded = ['id'];
    public function toArray()
    {
        return ['searchName' => (parent::toArray()['code_meli'] . ' - ' . parent::toArray()['name'])] + parent::toArray();
    }
}
