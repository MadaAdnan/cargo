<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CategoryTypeEnum:string implements HasLabel ,HasColor,HasIcon
{
    case SIZE='size';
    case WEIGHT='weight';


    public function getLabel(): string
    {
        return match ($this) {
            self::SIZE => 'فئة الحجم',
            self::WEIGHT => 'فئة الوزن',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SIZE => 'danger',
            self::WEIGHT => 'success',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SIZE => 'fas-expand',
            self::WEIGHT => 'fas-scale-balanced',
        };
    }


}
