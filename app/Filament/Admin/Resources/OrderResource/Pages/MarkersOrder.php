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
                //    Tables\Columns\TextColumn::make('updated_at')
                //             ->label(' مدة البقاء في العهدة')
                //             ->formatStateUsing(function ($state, $record) {
                //                 $nextMarker = $record->order->markers()
                //                     ->where('created_at', '>', $record->created_at)
                //                     ->orderBy('created_at', 'asc')
                //                     ->first();

                //                 $start = $record->created_at;
                //                 $end = $nextMarker ? $nextMarker->created_at : now();
                //                 $diff = $start->diff($end);

                //                 // التنسيق المحسن
                //                 $parts = [];
                //                 if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                //                 if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                //                 if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                //                 if ($diff->h > 0) $parts[] = $diff->h . ' ساعة';
                //                 if ($diff->i > 0) $parts[] = $diff->i . ' دقيقة';

                //                 // إذا كانت المدة أقل من دقيقة
                //                 if (empty($parts)) {
                //                     return 'أقل من دقيقة';
                //                 }

                //                 return implode(' و ', $parts);
                //             })
                //             ->description(fn ($record) => 'من ' . $record->created_at->format('Y-m-d H:i'))
                //             ->tooltip('المدة بين هذه النقطة والنقطة التالية في التتبع'),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label('مدة البقاء في العهدة')
                //     ->formatStateUsing(function ($state, $record) {
                //         $markers = $record->order->markers()
                //         ->orderBy('created_at')->get();
                //         $index = $markers->search(fn($m) => $m->id === $record->id);

                //         // حدد بداية ونهاية الفترة
                //         $start = $record->created_at;
                //         $end = $markers->get($index + 1)?->created_at ?? now();

                //         if (!$start || !$end) {
                //             return 'غير متوفرة';
                //         }

                //         $diff = $start->diff($end);

                //         $parts = [];
                //         if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                //         if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                //         if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                //         if ($diff->h > 0) $parts[] = $diff->h . ' ساعة';
                //         if ($diff->i > 0) $parts[] = $diff->i . ' دقيقة';

                //         return empty($parts) ? 'أقل من دقيقة' : implode(' و ', $parts);
                //     })
                //     ->description(fn ($record) => 'من ' . optional($record->created_at)->format('Y-m-d H:i'))
                //     ->tooltip('المدة بين هذه النقطة والنقطة التالية في التتبع'),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->label('مدة البقاء في العهدة')
                        ->formatStateUsing(function ($state, $record) {
                            try {
                                $markers = $record->order->markers()
                                    ->orderBy('created_at')
                                    ->get();

                                $currentIndex = $markers->search(fn($m) => $m->id === $record->id);

                                 if ($currentIndex === false) {
                                    return '--';
                                }

                                // إذا كانت النقطة الأولى، نحسب من وقت الاستلام حتى الآن
                                if ($currentIndex === 0) {
                                    $start = $record->created_at;
                                    $end = now();
                                    $diff = $start->diff($end);

                                    $parts = [];
                                    if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                                    if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                                    if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                                    if ($diff->h > 0) $parts[] = $diff->h . ' ساعة';
                                    if ($diff->i > 0) $parts[] = $diff->i . ' دقيقة';

                                    return empty($parts) ? 'أقل من دقيقة' : implode(' و ', $parts);
                                }

                                // للنقاط الأخرى، نحسب المدة بين النقطة الحالية والسابقة
                                // الحصول على النقطة السابقة
                                $previousMarker = $markers->get($currentIndex - 1);

                                $start = $previousMarker->created_at;
                                $end = $record->created_at;

                                $diff = $start->diff($end);

                                // تنسيق المدة
                                $parts = [];
                                if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                                if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                                if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                                if ($diff->h > 0) $parts[] = $diff->h . ' ساعة';
                                if ($diff->i > 0) $parts[] = $diff->i . ' دقيقة';

                                return empty($parts) ? 'أقل من دقيقة' : implode(' و ', $parts);

                            } catch (\Exception $e) {
                                return 'خطأ في الحساب';
                            }
                        })
                        ->tooltip(function ($record) {
                            $markers = $record->order->markers ?? collect();
                            $currentIndex = $markers->search(fn($m) => $m->id === $record->id);
                            if ($currentIndex === 0) {
                                        return "المدة من {$record->created_at->format('Y-m-d H:i')} حتى الآن";
                                    }
                            if ($currentIndex > 0) {
                                $previousMarker = $markers->get($currentIndex - 1);
                                return "المدة من {$previousMarker->created_at->format('Y-m-d H:i')} إلى {$record->created_at->format('Y-m-d H:i')}";
                            }

                            return "غير متاح";
                        })
                        ->badge()
                        ->color('success')
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
