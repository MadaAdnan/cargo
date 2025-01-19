<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TypeAccountEnum: string implements HasLabel ,HasColor,HasIcon
{
    case OIL = 'oil';
    case OFFICE = 'office';
    case SENDER = 'sender';
    case TOOL = 'tool';
    case TRANSFER = 'transfer';
    case ANY = 'any';
    case STAFF = 'staff';
    case BALANCE = 'balance';
    case WITHDRAW = 'withdraw';










    public function getLabel(): string
    {
        return match ($this) {
            self::OIL => 'مصاريف المحروقات',
            self::OFFICE => 'مصاريف مكتب',
            self::SENDER => 'مصاريف ارساليات',
            self::TOOL => 'مصاريف صيانة',
            self::TRANSFER => 'مصاريف حوالات',
            self::ANY => 'مصاريف متنوعة',
            self::STAFF => 'مسحوبات الكادر',
            self::BALANCE => 'رأس المال',
            self::WITHDRAW => 'رواتب الموظفين',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OIL => 'info',
            self::OFFICE => 'danger',
            self::SENDER => 'orange',
            self::TOOL => 'success',
            self::TRANSFER => 'warning',
            self::ANY => 'warning',
            self::STAFF => 'warning',
            self::BALANCE => 'warning',
            self::WITHDRAW => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::OIL,
            self::OFFICE ,
            self::SENDER ,
            self::TOOL ,
            self::TRANSFER ,
            self::ANY  ,
            self::STAFF ,
            self::BALANCE  ,
            self::WITHDRAW => 'fas-user-gear',
        };
    }
}
