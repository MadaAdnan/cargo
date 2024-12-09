<?php
namespace App\Filament\Admin\Resources\CityResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\CityResource;
use Illuminate\Routing\Router;


class CityApiService extends ApiService
{
    protected static string | null $resource = CityResource::class;

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
