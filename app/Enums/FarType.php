<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FarType:int
{
    case AFTER=0;
    case BEFORE=1;


    public function getLabel(): string
    {
        return match ($this) {
            self::AFTER => 'على المستلم',
            self::BEFORE => 'على المرسل',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AFTER => 'danger',
            self::BEFORE => 'success',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::AFTER => 'fas-money-bill-transfer',
            self::BEFORE => 'fas-hand-holding-dollar',
        };
    }


}
