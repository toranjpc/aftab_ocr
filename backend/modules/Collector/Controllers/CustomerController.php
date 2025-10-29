<?php

namespace Modules\Collector\Controllers;

use App\Http\Controllers\Controller;
use Modules\Collector\Models\Customer;

class CustomerController extends Controller
{
    function all()
    {
        return ['Customer' => Customer::select('id', 'shenase_meli', 'title')->paginate(100000)];
    }
}
