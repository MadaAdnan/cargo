<?php
namespace App\Filament\Admin\Resources\TaskResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Admin\Resources\TaskResource;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = TaskResource::class;
    

    public function handler()
    {
        $query = QueryBuilder::for(static::getEloquentQuery())
            ->allowedFields($this->getAllowedFields() ?? [])
            ->allowedSorts($this->getAllowedSorts() ?? [])
            ->allowedFilters($this->getAllowedFilters() ?? [])
            ->allowedIncludes($this->getAllowedIncludes() ?? [])
            ->defaultSort('-created_at');
        // Dynamically apply filters based on query parameters
        foreach (request()->query() as $key => $value) {
            // Skip pagination parameters
            if (in_array($key, ['page', 'per_page'])) {
                continue;
            }

            // Apply the filter
            $query->where($key, $value);
        }

        // Apply pagination
        $paginatedQuery = $query->paginate(request()->query('per_page', 15))->appends(request()->query());

        return static::getApiTransformer()::collection($paginatedQuery);
    }
}
