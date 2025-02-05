<?php

namespace App\Exports;

use App\Models\Balance;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BalanceReport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    protected array $filters;

    protected $balanceTr = 0;
    protected $balanceUsd = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Balance::query();

        if (!empty($this->filters['value'])) {
            $query->where('user_id', $this->filters['value']);
        }

        return $query->orderBy('created_at', 'asc');
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return [
            'رقم الفاتورة',
            'مدين تركي',
            'دائن تركي',
            'مدين دولار',
            'دائن دولار',
            'رصيد تركي',
            'رصيد دولار',
            'ملاحظات',
            'الطرف المقابل',
            'الطلب',
            'المرسل',
            'المستلم',
            'حالة الطلب',
            'أنشئ بواسطة',
            'المدينة',
            'النوع',
            'التاريخ والوقت',
        ];
    }

    public function map($row): array
    {
        $creditTr = 0;
        $debitTr = 0;
        $creditUsd = 0;
        $debitUsd = 0;

        if ($row->currency_id == 1) {
            $creditUsd = $row->credit;
            $debitUsd = $row->debit;
            $this->balanceUsd += ($creditUsd - $debitUsd);
        } elseif ($row->currency_id == 2) {
            $creditTr = $row->credit;
            $debitTr = $row->debit;
            $this->balanceTr += ($creditTr - $debitTr);
        }

        return [
            $row->id,
            $creditTr,
            $debitTr,
            $creditUsd,
            $debitUsd,
            $this->balanceTr,
            $this->balanceUsd,
            $row->info,
            $row->customer_name,
            $row->order?->code,
            $row->order?->sender?->name,
            $row->order?->global_name,
            $row->order?->status->getLabel(),
            $row->createdBy?->name,
            $row->order?->cityTarget?->name,
            $row->pending == true ? "قيد التحصيل" : "",
            date('Y-m-d', strtotime($row->created_at))
        ];
    }
}
