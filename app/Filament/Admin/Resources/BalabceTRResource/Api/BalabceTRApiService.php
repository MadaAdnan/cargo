<?php
namespace App\Filament\Admin\Resources\BalabceTRResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\BalabceTRResource;
use Illuminate\Routing\Router;


class BalabceTRApiService extends ApiService
{
    protected static string | null $resource = BalabceTRResource::class;

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
