<?php

namespace App\Filament\Admin\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Admin\Resources\TaskResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = TaskResource::class;

    public function handler(Request $request)
    {
        $id = $request->route('id');

        // Get the base query
        $query = static::getEloquentQuery();

        // Fetch the task record with the given ID
        $task = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )->first();

        if (!$task) return static::sendNotFoundResponse();
        // Fetch additional data for user_id
        $user_id = \App\Models\User::select('name as user_name')
        ->find($task->user_id);

        // Fetch additional data for delegate
        $delegate = \App\Models\User::select('name as delegate_name', 'email as delegate_email', 'phone as delegate_phone')
            ->find($task->delegate_id);

        // Merge delegate data
        if ($delegate) {
            $task->delegate_name = $delegate->delegate_name;
            $task->delegate_email = $delegate->delegate_email;
            $task->delegate_phone = $delegate->delegate_phone;
        }
        // Merge user data
        if ($user_id) {
            $task->user_name = $user_id->user_name;
        }
        $transformer = static::getApiTransformer();

        return new $transformer($task);
    }
}
