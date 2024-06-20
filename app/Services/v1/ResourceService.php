<?php


namespace App\Services\v1;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceService
{
    protected $includes = [];
    protected $queryFields = [];
    protected $sortFields = [];
    protected $columnMap = [];

    public function single($model, $input)
    {
        $parms = $this->buildParameters($input);

        if (!empty($parms['include'])) {
            $model->load($parms['include']);
        }

        return $this->formatToJson($model, $parms['include']);
    }

    protected function buildParameters($input)
    {
        $limit = $input['limit'] ?? 20;
        $offset = $input['offset'] ?? 0;

        // Set Max of $limit
        if ($limit > 50) {
            $limit = 50;
        }

        // TODO: defaults for limit, offset, and Max limit

        return [
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $this->buildSort($input['sort'] ?? ''),
            'where' => $this->buildWhere($input['where'] ?? ''),
            'include' => $this->buildWith($input['include'] ?? '')
        ];
    }

    protected function buildWith($rawInclude)
    {
        if (empty($rawInclude)) {
            return [];
        }

        $includeable = $this->includes;
        $parts = explode(',', strtolower($rawInclude));

        return array_intersect($includeable, $parts);
    }

    protected function buildWhere($rawWhere)
    {
        if (empty($rawWhere)) {
            return [];
        }

        $queryable = collect($this->queryFields);

        $operators = collect([
            'eq' => '=',
            'ne' => '<>',
            'lt' => '<',
            'lte' => '<=',
            'gt' => '>',
            'gte' => '>=',
        ]);

        //column:operator:value,column2:operator2:value2
        $rawClause = collect(explode(',', strtolower($rawWhere)));
        $clauses = collect([]);

        $rawClause->each(function ($item, $key) use ($queryable, $operators, $clauses) {
            //column:operator:value
            $parts = explode(':', $item);

            if (count($parts) != 3) {
                return false;
            }

            $field = $queryable->get($parts[0]) ?? '';
            $operator = $operators->get($parts[1]) ?? '';

            if (empty($field) || empty($operator)) {
                return false;
            }

            $clauses->push([
                $field,
                $operator,
                $parts[2]
            ]);
        });

        return $clauses->all();

    }

    protected function buildSort($rawSort)
    {
        if (empty($rawSort)) {
            return [];
        }

        $orderable = collect($this->sortFields);

        $direction = collect([
            'asc' => 'asc',
            'desc' => 'desc'
        ]);

        $parts = explode(':', strtolower($rawSort));
        $field = $orderable->get($parts[0]);
        $dir = $direction->get($parts[1] ?? '') ?? 'asc';

        return [$field, $dir];
    }

    protected function convertToActual($payload)
    {
        $data = [];

        collect($payload)->each(function ($value, $key) use (&$data) {
            $data[$this->columnMap[$key]] = $value;
        });

        return $data;
    }

    protected function uploadFile(Request $request, $key, $disk, $model)
    {
        if (!empty($request->file($key))) {
            $file = $request->file($key)->store('/', $disk);

            if ($model === 'author') {
                $model = User::query()->where('id', Auth::id())->first();
            }
            if (!empty($model[$key])) {
                $oldFile = $model[$key];
                $this->deleteFileIfExists($oldFile);
            }
            $model->forceFill([$key => $file])->save();

            return $model;
        } else {
            return false;
        }
    }

    protected function deleteFileIfExists($file)
    {
//   Этот код для локального компьютера

        if (file_exists(public_path('storage\\avatars\\' . $file))) {
            unlink(public_path('storage\\avatars\\' . $file));
        }
        if (file_exists(public_path('storage\\photos\\' . $file))) {
            unlink(public_path('storage\\photos\\' . $file));
        }

//    Этот Код для хостинга

//        if (file_exists(env('LINK_IMG') . $file )) {
//            unlink(env('LINK_IMG') . $file );
//            }
//        if (file_exists(env('LINK_AVATARS') . $file )) {
//            unlink(env('LINK_AVATARS') . $file );
//            }

    }
}
