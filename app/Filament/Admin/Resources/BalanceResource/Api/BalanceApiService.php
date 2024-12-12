<?php
namespace App\Filament\Admin\Resources\BalanceResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\BalanceResource;
use Illuminate\Routing\Router;


class BalanceApiService extends ApiService
{
    protected static string | null $resource = BalanceResource::class;

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
