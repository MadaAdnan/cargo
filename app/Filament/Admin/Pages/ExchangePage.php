<?php

namespace App\Filament\Admin\Pages;

use App\Models\Balance;
use App\Models\Currency;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class ExchangePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $model = Balance::class;
    protected static ?string $navigationGroup = 'الحسابات المالية';
    protected static ?string $label = 'إنشاء سند';
    protected static ?string $pluralLabel = 'إنشاء سند';
    protected static ?string $navigationLabel='مناقلات الحسابات المالية';
    protected ?string $heading='مناقلات الحسابات المالية';

    public $data = [
        'from' => '',
        'to' => '',
        'value' => '',
        'info' => ''
    ];

    protected static string $view = 'filament.admin.pages.exchange-page';

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function getFormSchema(): array
    {
        $accounts = User::accounts()->pluck('name', 'id');


        return [
            Select::make('from')->options($accounts)->label('الحساب الرئيسي')->required(),
            Select::make('to')->options($accounts)->label('الحساب المقابل')->required(),
            TextInput::make('value')->label('القيمة بعملة الحساب الرئيسي')->rules([
                fn (): Closure => function (string $attribute, $value, Closure $fail) {
                    if ($value <=0) {
                        $fail('يجب ان تكون القيمة أكبر من 0');
                    }
                },
            ])->required()->numeric(),
            Textarea::make('info')->label('بيانات'),
        ];
    }


    public function submit()
    {
        $data = $this->form->getState();
        $accountSource = User::accounts()->find($data['from']);

        $accountTarget = User::accounts()->find($data['to']);
    \DB::beginTransaction();
    try{
      if($accountSource==null || $accountTarget==null){
          throw new \Exception('تأكد من تحديد الحسابات بشكل صحيح');
      }
        $currencySource=$accountSource->currency;
        $currencyTarget=$accountTarget->currency;
        $value=$data['value']/$currencySource->value;
      Balance::create([
          'credit'=>0,
          'debit'=>$data['value'],
          'is_complete'=>true,
          'pending'=>false,
          'user_id'=>$accountSource->id,
          'currency_id'=>$accountSource->currency_id,
          'ex_cur'=>$accountSource->currency?->value,
          'info'=>'تحويل إلى حساب #'.$accountTarget->name.' - '. $data['info'],
      ]);
        Balance::create([
            'credit'=>$value*$currencyTarget->value,
            'debit'=>0,
            'is_complete'=>true,
            'pending'=>false,
            'user_id'=>$accountTarget->id,
            'currency_id'=>$accountTarget->currency_id,
            'ex_cur'=>$accountTarget->currency?->value,
            'info'=>'تحويل من حساب #'.$accountSource->name.' - '. $data['info'],
        ]);
        \DB::commit();
        Notification::make('success')->success()->title('نجاح العملية')->body('تم التحويل')->send();
    }catch (\Exception | \Error $e){
        \DB::rollBack();
        Notification::make('error')->danger()->title('فشل العملية')->body($e->getMessage())->send();
    }

    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }
}
