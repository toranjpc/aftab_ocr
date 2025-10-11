<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Sortable;
use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelUserActivity\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Base extends Model
{
    use Loggable, Filterable, Sortable, HasFactory;

    public function scopeWithRowNumber($query)
    {
        $query->selectRaw('*, ROW_NUMBER() OVER (ORDER BY id) AS _index_');
    }

    public function saver($request, $item)
    {
        $class_methods = get_class_methods($this); // or use new Myclass()
        foreach ($class_methods as $method_name) {
            if (substr($method_name, -5) === 'Saver') {
                $this->$method_name($request, $item);
            }
        }
    }
}
