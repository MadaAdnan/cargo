<?php

namespace App\Filament\Branch\Resources\BalanceResource\Pages;

use App\Filament\Branch\Resources\BalanceResource;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewBalance extends ViewRecord
{
    protected static string $resource = BalanceResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(function ($record) {
                return  [
                    KeyValueEntry::make('r')->label('تقرير')->default(function($record){
                        $list2=[];
                        if ($record->credit > 0) {
                            $list2[' من حساب ']=$record->customer_name;
                            $list2['إلى حساب ']=$record->user?->name;
                            $list2['المبلغ']=$record->credit .' '.$record->currency?->name;;

                        } elseif ($record->debit > 0) {
                            $list2[' من حساب ']=$record->user?->name;
                            $list2['إلى حساب ']=$record->customer_name;
                            $list2['المبلغ']=$record->debit .' '.$record->currency?->name;
                        }
                        if ($record->order_id != null) {
                            $list2['رقم الطلب']=$record->order_id;
                        }
                        $list2['البيان']=$record->info;
                        $list2['التوقيت']=$record->created_at->format('Y-m-d H:i');
                        return $list2;
                    })->keyLabel('شركة الفاتح للشحن')->valueLabel('رقم العملية : '.$record->id),
                ];
            });
    }
}
