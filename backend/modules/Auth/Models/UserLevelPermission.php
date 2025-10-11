<?php

namespace Modules\Auth\Models;

use App\Models\Base;

class UserLevelPermission extends Base
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'permission_do' => 'array'
    ];
}
