<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use Filament\Resources\Pages\Page;
use App\Models\Order;
use App\Models\Marker;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
class MarkersOrder extends Page implements HasTable
{
     use InteractsWithTable;
    protected static ?string $title = 'تتبع الشحنة';
    protected static string $resource = OrderResource::class;
    public Order $record;
    protected static string $view = 'filament.admin.resources.order-resource.pages.markers-order';
    public function table(Table $table): Table
        {
            return $table
                ->query($this->record->markers()->getQuery())
                ->columns([
                    Tables\Columns\TextColumn::make('user.name')->label('في عهدة '),
                    Tables\Columns\TextColumn::make('created_at')->date('Y-m-d H:i')->label('تاريخ الاستلام'),
                    Tables\Columns\TextColumn::make('info')
                    ->label('الحالة')
                    ->wrap(),
                   Tables\Columns\TextColumn::make('updated_at')
                            ->label(' مدة البقاء في العهدة')
                            ->formatStateUsing(function ($state, $record) {
                                $nextMarker = $record->order->markers()
                                    ->where('created_at', '>', $record->created_at)
                                    ->orderBy('created_at', 'asc')
                                    ->first();

                                $start = $record->created_at;
                                $end = $nextMarker ? $nextMarker->created_at : now();
                                $diff = $start->diff($end);

                                // التنسيق المحسن
                                $parts = [];
                                if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                                if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                                if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                                if ($diff->h > 0) $parts[] = $diff->h . ' ساعة';
                                if ($diff->i > 0) $parts[] = $diff->i . ' دقيقة';

                                // إذا كانت المدة أقل من دقيقة
                                if (empty($parts)) {
                                    return 'أقل من دقيقة';
                                }

                                return implode(' و ', $parts);
                            })
                            ->description(fn ($record) => 'من ' . $record->created_at->format('Y-m-d H:i'))
                            ->tooltip('المدة بين هذه النقطة والنقطة التالية في التتبع'),
            ])->defaultSort('created_at', 'desc')
                ->filters([
                    //
                ])
                ->headerActions([
    //                Tables\Actions\CreateAction::make(),
                ])
                ->actions([
    //                Tables\Actions\EditAction::make(),
    //                Tables\Actions\DeleteAction::make(),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
    //                    Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ]);
        }
}
