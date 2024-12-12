<?php
namespace App\Filament\Admin\Resources\UnitResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\UnitResource;
use Illuminate\Routing\Router;


class UnitApiService extends ApiService
{
    protected static string | null $resource = UnitResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
