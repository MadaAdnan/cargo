<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatusEnum:string implements HasLabel ,HasColor,HasIcon
{
    case PENDING='pending';
    case AGREE='agree';
    case PICK='pick';
    case TRANSFER='transfer';
    case SUCCESS='success';
    case RETURNED='returned';
    case CONFIRM_RETURNED='confirm_returned';
    case CANCELED='canceled';


    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'إنتظار الموافقة',
            self::AGREE => 'تمت الموافقة',
            self::PICK => 'تم التحميل',
            self::TRANSFER => 'بإنتظار التسليم',
            self::SUCCESS => 'تم التسليم',
            self::RETURNED => 'مرتجع',
            self::CONFIRM_RETURNED => 'تسليم المرتجع',
            self::CANCELED => 'ملغي',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::AGREE, self::PICK => 'primary',
            self::TRANSFER => 'primary',
            self::SUCCESS => 'success',
            self::RETURNED => 'danger',
            self::CONFIRM_RETURNED => 'red',
            self::CANCELED => 'warning',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'fas-cart-flatbed',
            self::AGREE => 'fas-cart-flatbed',
            self::PICK => 'fas-cart-flatbed',
            self::TRANSFER => 'fas-cart-flatbed',
            self::SUCCESS => 'fas-cart-flatbed',
            self::RETURNED => 'fas-cart-flatbed',
            self::CONFIRM_RETURNED => 'fas-cart-flatbed',
            self::CANCELED => 'fas-cart-flatbed',

        };
    }

}
