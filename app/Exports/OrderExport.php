<?php

namespace App\Exports;

use App\Enums\FarType;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{


    public function query()
    {
      return  Order::query();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return [
            'رقم الطلب',
            'كود الطلب',
            'نوع الطلب',
            'حالة الطلب',
            'حالة الدفع',
            'وقت الطلب',
            'نوع الشحنة',
            'التحصيل دولار',
            'الأجور دولار',
            'التحصيل تركي',
            'الأجور تركي',
            'معرف المرسل',
            'اسم المرسل',
            'من بلدة',
            'إلى بلدة',
            'معرف المستلم',
            'اسم المستلم',
            'هاتف المستلم',
            'عنوان المستلم',
            'موظف الإلتقاط',
            'موظف التسليم',

        ];
    }

    /**
     * @param Order $row
     * @return array
     */

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->type?->getLabel(),
            $row->status?->getLabel(),
            FarType::tryFrom($row->far_sender)?->getLabel(),
            $row->created_at->format('Y-m-d h:i a'),
            $row->unit?->name,
            $row->price,
            $row->far,
            $row->price_tr,
            $row->far_tr,
            $row->sender?->name,
            $row->general_sender_name,
            $row->citySource?->name,
            $row->cityTarget?->name,
            $row->receive?->name,
            $row->global_name,
            $row->receive_phone,
            $row->receive_address,
            $row->pick?->name,
            $row->given?->name,


        ];
    }
}
