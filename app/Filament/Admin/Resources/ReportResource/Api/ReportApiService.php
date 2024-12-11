<?php
namespace App\Filament\Admin\Resources\ReportResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Admin\Resources\ReportResource;
use Illuminate\Routing\Router;


class ReportApiService extends ApiService
{
    protected static string | null $resource = ReportResource::class;

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
