<?php

namespace App\Filament\Admin\Pages;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard // التوريث الصحيح
{
use HasFiltersForm;


public function filtersForm(Form $form): Form
{
    return $form->schema([
            DatePicker::make('date')
            ->default(now()),

    ]) ->statePath('filters');
}


}
