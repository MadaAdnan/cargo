<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivateStatusEnum:string implements HasLabel ,HasColor,HasIcon
{
    case PENDING='pending';
    case ACTIVE='active';
    case INACTIVE='inactive';
    case BLOCK='block';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'بالإنتظار',
            self::ACTIVE => 'مفعل',
            self::INACTIVE => 'غير مفعل',
            self::BLOCK => 'محظور',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::ACTIVE => 'success',
            self::INACTIVE => 'warning',
            self::BLOCK => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'fas-clock',
            self::ACTIVE => 'fas-circle-check',
            self::INACTIVE => 'fas-lock',
            self::BLOCK => 'fas-ban',
        };
    }

}
