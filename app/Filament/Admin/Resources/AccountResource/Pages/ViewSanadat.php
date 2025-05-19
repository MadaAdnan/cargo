<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Filament\Admin\Resources\AccountResource;
use Filament\Resources\Pages\Page;
use App\Models\Balance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
class ViewSanadat extends ListRecords
{
    protected static string $resource = AccountResource::class;
    protected static ?string $title = 'سندات القيد';

    protected static string $view = 'filament.admin.resources.account-resource.pages.view-sanadat';

  public  function table(Table $table): Table
    {
        return $table
           ->query(Balance::where('type', 'sanadquid')->orderBy('created_at', 'desc'))
           ->defaultGroup('uuid') // التجميع التلقائي عند فتح الصفحة
            ->groups([
                Tables\Grouping\Group::make('uuid')
                    ->label('مجموعة السندات')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(function (Balance $record) {
                        static $counter = 1;
                         static $uuids = [];
                          if (!isset($uuids[$record->uuid])) {
                             $uuids[$record->uuid] = $counter++;
                                 }

                             return "سندات قيد #{$uuids[$record->uuid]}";
                    })
                    ->getDescriptionFromRecordUsing(function (Balance $record) {
                        $totalCredit = Balance::where('uuid', $record->uuid)->sum('credit');
                        $totalDebit = Balance::where('uuid', $record->uuid)->sum('debit');
                        return "المدين: {$totalCredit} | الدائن: {$totalDebit}";
                    }),
            ])
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('الحساب'),
                Tables\Columns\TextColumn::make('credit')
                    ->label('مدين'),
                Tables\Columns\TextColumn::make('debit')
                    ->label('دائن'),
                Tables\Columns\TextColumn::make('currency.name')
                    ->label('العملة'),
                Tables\Columns\TextColumn::make('info')
                    ->label('البيان'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime(),
            ])
           ->filters([
            // فلتر اسم الحساب (المستخدم)
            Tables\Filters\SelectFilter::make('user')
                ->relationship('user', 'name')
                ->searchable()
                ->label('فلترة حسب الحساب'),

            // فلتر العملة
            Tables\Filters\SelectFilter::make('currency')
                ->relationship('currency', 'name')
                ->searchable()
                ->label('فلترة حسب العملة'),

            // فلتر التاريخ
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('من تاريخ'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('إلى تاريخ'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when($data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
                ->label('فلترة حسب التاريخ'),
        ]);

    }


    }



