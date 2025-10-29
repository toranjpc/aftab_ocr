<?php

namespace Modules\Collector\Models;

use App\Models\Base;

class TabarInvoice extends Base
{
    protected $appends = [
        'payment_date_persian',
        'payment_time',
    ];

    protected $casts = [
        'InvoiceDate' => 'datetime',
        'PaymentDate' => 'datetime',
        'DischargeDate' => 'datetime',
        'CalculationDate' => 'datetime',
    ];

    protected $guarded = ['id'];

    public function getPaymentDatePersianAttribute()
    {
        return verta($this->payment_date)->format('Y-m-d');
    }

    public function getPaymentTimeAttribute()
    {
        return verta($this->payment_date)->format('H:i');
    }

    public function tabarInvoiceContainers()
    {
        return $this->hasMany(TabarInvoiceContainer::class);
    }

    public function getCcsCountAttribute()
    {
        return $this->ccsData->count();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function ccsData()
    {
        return $this->hasMany(CcsData::class, 'ReceiptNumber', 'ReceiptNumber');
    }
}
