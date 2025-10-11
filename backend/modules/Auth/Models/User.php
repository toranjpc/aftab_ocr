<?php

namespace Modules\Auth\Models;

use Abbasudo\Purity\Traits\Sortable;
use Haruncpi\LaravelUserActivity\Traits\Loggable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Event;
use Abbasudo\Purity\Traits\Filterable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Sortable, Loggable, Filterable, HasApiTokens;

    protected $with = ['userLevel'];

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userLevel()
    {
        return $this->belongsTo(UserLevelPermission::class, 'user_level');
    }

    public static function boot()
    {
        parent::boot();
        Event::listen(
            ['user.beforeCreate'],
            function ($query) {
                $query->password = bcrypt($query->password);
            }
        );
    }
}
