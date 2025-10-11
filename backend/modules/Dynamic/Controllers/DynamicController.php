<?php

namespace Modules\Dynamic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DynamicController extends Controller
{
    public function index(Request $request, $modelName)
    {
        [$key, $query, $model] = $this->resolveModel($modelName);

        $this->checkAccess();

        if (method_exists($model, 'overwriteIndex')) {
            return $model->overwriteIndex($key, $query);
        }

        $event_index = event($modelName . '.index', $query);

        $this->setFilters($request, $query);

        $paginatedItems = $query->orderBy('id', 'DESC')->paginate(
            $request->all ? 1500 : $request->itemPerPage ?? 15
        );

        if ($request->_append) {
            $appends = explode(',', $request->_append);
            $paginatedItems->each(fn($item) => $item->append($appends));
        }

        $key = $request->key ?: $key;

        return response(['message' => 'ok', 'event_index' => $event_index, "$key" => $paginatedItems], Response::HTTP_OK);
    }

    public function sum(Request $request)
    {
        $models = [];
        foreach ($request->Model as $modelName) {
            [$key, $query] = $this->resolveModel($modelName);
            event($modelName . '.sum', $query);
            $models["$key"] = $query->orderBy('id', 'DESC')->paginate(15);
        }
        return response(['message' => 'ok', 'Model' => $models], Response::HTTP_OK);
    }

    public function show($modelName, $id)
    {
        [$key, $query] = $this->resolveModel($modelName);

        $this->checkAccess();

        event($modelName . '.show', $query);

        $findItem = $query->find($id);

        return response(["$key" => $findItem], Response::HTTP_OK);
    }

    public function store(Request $request, $modelName)
    {
        [$key, $query, $model] = $this->resolveModel($modelName);

        $this->checkAccess();

        if (method_exists($model, 'overwriteStore')) {
            return $model->overwriteStore($key, $query);
        }

        try {
            $beforeCreate = new $model($request->$key ?? [] + ['user' => auth()->id()]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $b = event($modelName . '.beforeCreate', $beforeCreate);

        try {
            $created = $beforeCreate->save();
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code == 23000) {
                return response(['message' => 'رکورد وارد شده تکراری است.', 's' => $beforeCreate, 'b' => $b, '$e' => $e], Response::HTTP_BAD_REQUEST);
            }
            return response(['message' => $e->getMessage(), "d" => $beforeCreate], Response::HTTP_BAD_REQUEST);
        }

        if ($created) {
            $item = $query->find($beforeCreate->id);
            //            $item = $beforeCreate;
            $x = event($modelName . '.created', [$item, $request->$key]);
            return response(['message' => 'اضافه شد', "$key" => $item, "ddd" => $x], Response::HTTP_OK);
        }

        return response(['message' => 'مشکلی پیش آمده'], Response::HTTP_BAD_REQUEST);
    }

    public function update(Request $request, $modelName, $id)
    {
        [$key, $query, $model] = $this->resolveModel($modelName);

        $this->checkAccess();

        if (method_exists($model, 'overwriteUpdate')) {
            return $model->overwriteUpdate($key, $query, $id);
        }

        try {
            $result = event($modelName . '.beforeUpdate', [$query, $request->$key, $id]);
            if (!empty($result) && is_array($result)) {
                if (!empty($result[0]) && is_array($result[0]) && isset($result[0][0]) && $result[0][0] === 0) {
                    return response(['message' => $result[0][1]], Response::HTTP_BAD_REQUEST);
                }
            }
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $findItem = $query->findOrFail($id);

        $update = $findItem->update($request->$key);

        if ($update) {
            $findItem = $query->find($id);

            $event_updated = event($modelName . '.updated', [$findItem, $request->$key]);

            return response(['message' => 'بروز شد', "$key" => $findItem, 'event_updated' => $event_updated], Response::HTTP_OK);
        }

        return response(['message' => 'مشکلی پیش آمده'], Response::HTTP_BAD_REQUEST);
    }

    public function destroy($modelName, $id)
    {
        [$key, $query, $model] = $this->resolveModel($modelName);

        $this->checkAccess();

        if (method_exists($model, 'overwriteDestroy')) {
            return $model->overwriteDestroy($id, $key, $query);
        }

        $findItem = $query->findOrFail($id);

        event($modelName . '.beforeDestroy', $findItem);

        $deleteItem = $findItem->delete();

        event($modelName . '.destroyed', $findItem);

        if ($deleteItem) {
            return response(['message' => "حذف شد"], Response::HTTP_OK);
        }

        return response(['message' => 'اشتباهی در پاک کردن رخ داده است'], Response::HTTP_BAD_REQUEST);
    }

    public function changeOrdering(Request $request, $modelName)
    {
        [$key, $query, $model] = $this->resolveModel($modelName);

        $lists = $request->lists;
        foreach ($lists as $list) {
            $model::find($list['id'])->update(['order' => $list['ordering']]);
        }
    }

    // private function setFilters(Request $request, $query)
    // {
    //     $req = $request->except(['_*', '_without', '_with', '_notNull', 'page', 'all', 'itemPerPage']);
    //     if ($req) {
    //         collect($req)->each(function ($item, $index) use ($query) {
    //             $items = explode(',', $item);
    //             $query->whereIn($index, $items);
    //         });
    //     }
    // }

    private function setFilters(Request $request, $query)
    {
        $req = $request->except([
            '_*',
            '_without',
            '_with',
            '_select',
            '_notNull',
            '_has',
            '_doesnt_have',
            'page',
            'all',
            'itemPerPage',
            'filters',
            '_append',
            'sort'
        ]);

        if ($req) {
            collect($req)->each(function ($item, $index) use ($query) {
                $items = explode(',', $item);
                $query->whereIn($index, $items);
            });
        }

        if ($request->_has) {
            $query->whereHas($request->_has);
        }

        if ($request->_doesnt_have) {
            $query->whereDoesntHave($request->_doesnt_have);
        }

        if ($request->_without) {
            $query->setEagerLoads([]);
        }

        if ($request->_with) {
            $query->with(explode(',', $request->_with));
        }

        if ($request->_notNull) {
            $query->whereNotNull($request->_notNull);
        }

        if ($request->_select) {
            $fields = explode(',', $request->_select);
            $query->select($fields);
        }

        $query->filter();

        $query->sort();
    }

    private function resolveModel($modelName): array
    {
        $modelNameCamelCase = \Str::of($modelName)->camel()->ucfirst();

        $key = $modelNameCamelCase;
        if (str_contains($modelNameCamelCase, '=>')) {
            [$key, $modelNameCamelCase] = explode('=>', $modelNameCamelCase);
        }

        try {
            $model = app('Model.' . $modelNameCamelCase);
        } catch (\Exception $e) {
            $model = app('App\\Models\\' . $modelNameCamelCase);
        }

        $query = $model->query();

        return [$key, $query, $model];
    }

    private function checkAccess()
    {
        //
    }
}
