<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderTypeEnum:string implements HasLabel ,HasColor,HasIcon
{
    case HOME='home';
    case BRANCH='branch';


    public function getLabel(): string
    {
        return match ($this) {
            self::HOME => 'إلتقاط من المتجر',
            self::BRANCH => 'إلتقاط من المكتب ',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOME => 'info',
            self::BRANCH => 'danger',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HOME => 'fas-door-open',
            self::BRANCH => 'fas-cart-flatbed',

        };
    }

}
