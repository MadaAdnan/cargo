<?php
namespace App\Filament\Admin\Resources\ExchangeResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\ExchangeResource;
use Illuminate\Routing\Router;


class ExchangeApiService extends ApiService
{
    protected static string | null $resource = ExchangeResource::class;

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
