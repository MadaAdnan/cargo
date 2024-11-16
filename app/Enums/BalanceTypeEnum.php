<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BalanceTypeEnum:string implements HasLabel ,HasColor,HasIcon
{
    case PUSH='push';
    case CATCH='catch';
    case START='start';
   // case TASK='branch';


    public function getLabel(): string
    {
        return match ($this) {
            self::PUSH => 'سند دفع',
            self::CATCH => 'سند قبض',
            self::START => 'بداية المدة',
           // self::TASK => 'نقل',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PUSH => 'info',
            self::CATCH => 'success',
            self::START => 'danger',
          //  self::TASK => 'warning',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PUSH => 'fas-door-open',
            self::CATCH => 'fas-cart-flatbed',
            self::START => 'fas-clock',
          //  self::TASK => 'fas-cart-flatbed',

        };
    }

}
