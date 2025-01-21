<?php
namespace App\Filament\Admin\Resources\RequestExchangeResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\RequestExchangeResource;
use Illuminate\Routing\Router;


class RequestExchangeApiService extends ApiService
{
    protected static string | null $resource = RequestExchangeResource::class;

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
