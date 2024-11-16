<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivateAgencyEnum:string implements HasLabel ,HasColor,HasIcon
{
    case PENDING='pending';
    case CANCELED='canceled';
    case COMPLETE='complete';


    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'بإنتظار الإكمال',
            self::CANCELED => 'ملغي',
            self::COMPLETE => 'مكتمل',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::CANCELED => 'success',
            self::COMPLETE => 'warning',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'fas-clock',
            self::CANCELED => 'fas-ban',
            self::COMPLETE => 'fas-check',

        };
    }

}
