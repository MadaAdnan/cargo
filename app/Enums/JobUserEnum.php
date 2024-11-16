<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum JobUserEnum: string implements HasLabel ,HasColor,HasIcon
{
    case STAFF = 'staff';
    case ACCOUNTING = 'accounting';
    case MANGER = 'manager';
   // case BRANCH = 'branch';


    public function getLabel(): string
    {
        return match ($this) {
            self::STAFF => 'موظف',
            self::ACCOUNTING => 'محاسب',
            self::MANGER => 'مدير',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::STAFF => 'info',
            self::ACCOUNTING => 'success',
            self::MANGER => 'warning',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::STAFF => 'fas-people-carry-box',
            self::ACCOUNTING => 'fas-cash-register',
            self::MANGER => 'fas-user-gear',

        };
    }
}
