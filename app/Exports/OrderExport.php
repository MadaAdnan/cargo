<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::query()->with('citySource', 'cityTarget', 'branchTarget', 'branchSource');

        if (!empty($this->filters['created_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['created_from']);
        }

        if (!empty($this->filters['created_until'])) {
            $query->whereDate('created_at', '<=', $this->filters['created_until']);
        }

        if (!empty($this->filters['branch_source_id'])) {
            $query->whereIn('branch_source_id', $this->filters['branch_source_id']);
        }

        if (!empty($this->filters['pick_id'])) {
            $query->whereIn('pick_id', $this->filters['pick_id']);
        }

        if (!empty($this->filters['given_id'])) {
            $query->whereIn('given_id', $this->filters['given_id']);
        }

        if (!empty($this->filters['branch_target_id'])) {
            $query->whereIn('branch_target_id', $this->filters['branch_target_id']);
        }

        if (!empty($this->filters['receive_id'])) {
            $query->whereIn('receive_id', $this->filters['receive_id']);
        }

        if (!empty($this->filters['sender_id'])) {
            $query->whereIn('sender_id', $this->filters['sender_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->whereIn('status', $this->filters['status']);
        }

        if (!empty($this->filters['city_source_id'])) {
            $query->whereIn('city_source_id', $this->filters['city_source_id']);
        }

        if (!empty($this->filters['city_target_id'])) {
            $query->whereIn('city_target_id', $this->filters['city_target_id']);
        }

        return $query;
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
            'منطقة الإرسال',
            'بلدة الإرسال',
            'منطقة الاستلام',
            'بلدة الاستلام',
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
            $row->id,
            $row->code,
            $row->type?->getLabel(),
            $row->status?->getLabel(),
            $row->created_at->format('Y-m-d'),
            $row->created_at->format('H:i:s'),
            $row->unit?->name,
            $row->price,
            $row->far,
            $row->price_tr,
            $row->far_tr,
            $row->sender?->name,
            $row->general_sender_name,
            $row->citySource?->city?->name,
            $row->citySource?->name,
            $row->cityTarget?->city?->name,
            $row->cityTarget?->name,
            $row->branchSource?->name,
            $row->branchTarget?->name,
            $row->receive?->name,
            $row->global_name,
            $row->receive_phone,
            $row->receive_address,
            $row->pick?->name,
            $row->given?->name,
        ];
    }
}
