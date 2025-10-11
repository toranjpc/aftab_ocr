<?php

namespace Modules\Dynamic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FilterController extends Controller
{
    public function filter(Request $request)
    {
        [$key, $query, $model] = $this->resolveModel($request->model);

        $this->setFilters($request, $query);

        method_exists($model, 'setFilter') && $model->setFilter($query);

        $modelName = \Str::of($request->model)->snake();

        $modelName = str_replace('_', '-', $modelName);

        event($modelName . '.index', $query);

        if ($request->fields) {
            $query->where(function ($query) use ($request) {
                collect($request->fields)->each(function ($key, $index) use ($query, $request) {
                    $query->Orwhere($key, 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->selects) {
            collect($request->selects)->each(function ($item, $index) use ($query) {
                count($item) > 0 && $query->whereIn($index, $item);
            });
        }

        if ($request->excepts) {
            collect($request->excepts)->each(function ($item, $index) use ($query) {
                count($item) > 0 && $query->where($index, $item[0], $item[1]);
            });
        }

        if ($request->dates) {
            collect($request->dates)->each(function ($item, $index) use ($query) {
                count($item) > 1 && $query->whereBetween($index, $item) ||
                    count($item) > 0 && $query->whereDate($index, $item);
            });
        }

        if ($request->arrays) {
            collect($request->arrays)->each(function ($item, $index) use ($query, $request) {
                count($item) > 0 && collect($item)->each(function ($itemArray, $indexArray) use ($query, $index) {
                    $query->where($index, 'like', '%' . $itemArray . '%');
                });
            });
        }

        if ($request->order)
            $send = $query->orderBy($request->order['key'], $request->order['value'])->paginate($request->itemPerPage ? $request->itemPerPage : 15);
        else {
            if ($modelName === 'profile')
                $send = $query->orderBy('automatic_norm', 'DESC')->paginate(
                    $request->all ? 1500 : $request->itemPerPage ?? 15
                );
            else
                $send = $query->orderBy('id', 'ASC')->paginate(
                    $request->all ? 1500 : $request->itemPerPage ?? 15
                );
        }

        return response(['message' => 'ok', "$key" => $send], Response::HTTP_OK);
    }

    private function resolveModel($modelName): array
    {
        $modelNameCamelCase = \Str::of($modelName)->camel()->ucfirst();

        $key = $modelNameCamelCase;
        if (str_contains($modelNameCamelCase, '=>')) {
            [$key, $modelNameCamelCase] = explode('=>', $modelNameCamelCase);
        }

        $model = app('Model.' . $modelNameCamelCase);

        $query = $model->query();

        return [$key, $query, $model];
    }


    private function setFilters(Request $request, $query)
    {
        $req = collect($request->query())->except(['page', 'all', 'itemPerPage']);
        if ($req) {
            collect($req)->each(function ($item, $index) use ($query) {
                $query->where($index, $item);
            });
        }
    }
}
