<?php

namespace Modules\BijacInvoice\Models;

use App\Models\Base;
use Modules\Gcoms\Models\GcomsOutData;

class Invoice extends Base
{
    protected $guarded = ['id'];

    protected $with = ['customer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bijacs()
    {
        return $this->hasMany(Bijac::class, 'receipt_number', 'receipt_number');
    }

    public function gcomsOutData()
    {
        return $this->hasMany(GcomsOutData::class);
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer->title;
    }

    public function getPayDatePersianAttribute()
    {
        return verta($this->pay_date)->formatJalaliDatetime();
    }

}
