<?php
namespace App\Filament\Admin\Resources\CurrencyResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\CurrencyResource;
use Illuminate\Routing\Router;


class CurrencyApiService extends ApiService
{
    protected static string | null $resource = CurrencyResource::class;

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
