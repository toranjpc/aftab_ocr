<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Abbasudo\Purity\Traits\Filterable;

class Log extends Model
{
    use HasFactory;
    use Filterable;

    protected $guarded = ['id'];

    public $timestamps = false;


}
