<?php

namespace App\Filament\Employ\Pages;

use App\Models\Balance;
use App\Models\Currency;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExchangeUserBay extends Page  implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='شراء دولار';
     protected ?string $heading='شراء دولار';

    protected static string $view = 'filament.admin.pages.exchange-user-bay';
     protected static ?string $model = Balance::class;
    protected static ?string $navigationGroup = 'الحسابات المالية';
    protected static ?string $label = 'إنشاء سند';
    protected static ?string $pluralLabel = 'إنشاء سند';
    public static function canAccess(): bool
    {
        return false; // TODO: Change the autogenerated stub
    }
    public $data = [
        'from' => '',
        'to' => '',
        'value' => '',
        'info' => '',
        'type',
        'price',
    ];

    public function mount(): void
    {
        $this->data['price']=Currency::find(2)->up_value;
        $this->form->fill($this->data);
    }

    public function getFormSchema(): array
    {
        //$accounts = User::pluck('name', 'id');
        return [

          /*  Select::make('to')->options($accounts)->label('الحساب الدائن')->required()->searchable(),
            Select::make('from')->options($accounts)->label('الحساب المدين')->required()->searchable(),*/

            TextInput::make('price')->label('سعر التصريف')->required()->readOnly()->dehydrated(false),
            TextInput::make('value')->numeric()->label('القيمة بالتركي ')->rules([
                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                    if ($value <= 0) {
                        $fail('يجب ان تكون القيمة أكبر من 0');
                    }
                },
            ])->required()->live(),
            Textarea::make('info')->label('بيانات'),
            Placeholder::make('message')->dehydrated(false)->content(fn($get)=>"سيتم تحويل مبلغ {$get('value')} من صندوق التركي  إلى صندوق الدولار بقيمة ". (float)$get('value')/ (float)$get('price'))->label('تحذير')->extraAttributes(['style'=>'color:red']),

        ];
    }


    public function submit()
    {
        $data = $this->form->getState();
$currency=Currency::find(2)?->up_value;
        \DB::beginTransaction();
        try {

                if(auth()->user()->total_balance_tr < $data['value']){
                    throw  new \Exception('لا تملك رصيد كافي');
                }
                Balance::create([
                    'credit' => 0,
                    'debit' => $data['value'],
                    'is_complete' => true,
                    'pending' => false,
                    'user_id' => auth()->user()->id,
                    'currency_id' => 2,
                    'ex_cur' => $data['price'],
                    'info' => 'تحويل من حساب تركي إلى حساب دولار  #'. ' - ' . $data['info'],
                ]);
                Balance::create([
                    'credit' => $data['value'] / $currency,
                    'debit' => 0,
                    'is_complete' => true,
                    'pending' => false,
                    'user_id' => auth()->user()->id,
                    'currency_id' =>1,
                    'ex_cur' => $currency,
                    'info' => 'تحويل من حساب تركي إلى حساب دولار  #'. ' - ' . $data['info'],
                ]);



            \DB::commit();
            Notification::make('success')->success()->title('نجاح العملية')->body('تم التحويل')->send();
        } catch (\Exception | \Error $e) {
            \DB::rollBack();
            Notification::make('error')->danger()->title('فشل العملية')->body($e->getMessage())->send();
        }

    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }
}
