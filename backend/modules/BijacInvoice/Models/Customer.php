<?php

namespace Modules\BijacInvoice\Models;

use App\Models\Base;

class Customer extends Base
{
    // protected $connection = 'collector';

    protected $fillable = [
        'shenase_meli',
        'title',
        'registrationDate',
        'status',
        'registrationTypeTitle',
        'lastCompanyNewsDate',
        'shomare_sabt',
        'code_eghtesadi',
        'lat',
        'lng',
        'postal_code',
        'phone',
        'mobile',
        'webSite',
        'email',
        'address',
        'province',
        'city',
        'socialMedias',
        'type',
        'is_safe'
    ];

    // protected $appends = ['full_name'];

    public function getTypeNumberAttribute()
    {
        return $this->type === 'juridical' ? 2 : 1;
    }

    public function getTaxNumberAttribute()
    {
        return ($this->type_number == '1') ? '11111111111111' : '11111111111';
    }

    public static function boot()
    {
        parent::boot();
        // Event::listen(['customer.beforeCreate'], function ($query) {
        //     $query->province = Province::find($query->province)->name;
        // });
    }

    public function getFullNameAttribute()
    {
        // if ($this->type == 1)
        //     return $this->name . ' ' . $this->family;

        // else return $this->title;

        return $this->title;
    }

    public function toArray()
    {
        return ['searchName' => ($this->shenase_meli . ' - ' . $this->full_name)] + parent::toArray();
    }
}
