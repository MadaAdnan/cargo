<?php

namespace App\Exports;

use App\Models\Balance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BalanceTrExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    public function query()
    {
        return  Balance::query();
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
            'تاريخ الطلب',
            'ساعة الطلب',
            'نوع الشحنة',
            'التحصيل دولار',
            'الأجور دولار',
            'التحصيل تركي',
            'الأجور تركي',
            'معرف المرسل',
            'اسم المرسل',
            'المنطقة',
            'من بلدة',
            'المنطقة',
            'إلى بلدة',
            'الفرع المرسل',
            'الفرع المستلم',
            'معرف المستلم',
            'اسم المستلم',
            'هاتف المستلم',
            'عنوان المستلم',
            'موظف الإلتقاط',
            'موظف التسليم',

        ];
    }



    public function map($row): array
    {
        return [



        ];
    }
}
