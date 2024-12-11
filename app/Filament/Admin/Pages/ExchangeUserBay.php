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
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExchangeUserBay extends Page  implements HasForms
{
    use InteractsWithForms;

    //H : Hidden (شراء دولار) Page button from sidenav in admin panel
    public static function shouldRegisterNavigation(): bool { return false; }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='شراء دولار';
     protected ?string $heading='شراء دولار';

    protected static string $view = 'filament.admin.pages.exchange-user-bay';
     protected static ?string $model = Balance::class;
    protected static ?string $navigationGroup = 'الحسابات المالية';
    protected static ?string $label = 'إنشاء سند';
    protected static ?string $pluralLabel = 'إنشاء سند';

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
        $accounts = User::pluck('name', 'id');
        return [

            Select::make('to')->options($accounts)->label('الحساب الدائن')->required()->searchable(),
            Select::make('from')->options($accounts)->label('الحساب المدين')->required()->searchable(),

            TextInput::make('price')->label('سعر صرف الدولار بالنسبة للتركي')->required(),
            TextInput::make('value')->label('القيمة بالتركي ')->rules([
                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                    if ($value <= 0) {
                        $fail('يجب ان تكون القيمة أكبر من 0');
                    }
                },
            ])->required(),
            Textarea::make('info')->label('بيانات'),
        ];
    }


    public function submit()
    {
        $data = $this->form->getState();
        $accountSource = User::find($data['to']);

        $accountTarget = User::find($data['from']);
        \DB::beginTransaction();
        try {
            if ($accountSource == null || $accountTarget == null) {
                throw new \Exception('تأكد من تحديد الحسابات بشكل صحيح');
            }

                if($accountSource->total_balance_tr < $data['value']){
                    throw  new \Exception('لا تملك رصيد كافي');
                }
                Balance::create([
                    'credit' => 0,
                    'debit' => $data['value'],
                    'is_complete' => true,
                    'pending' => false,
                    'user_id' => $accountSource->id,
                    'currency_id' => 2,
                    'ex_cur' => $data['price'],
                    'info' => 'تحويل إلى حساب #' . $accountTarget->name . ' - ' . $data['info'],
                ]);
                Balance::create([
                    'credit' => $data['value'] / $data['price'],
                    'debit' => 0,
                    'is_complete' => true,
                    'pending' => false,
                    'user_id' => $accountTarget->id,
                    'currency_id' =>1,
                    'ex_cur' => $data['price'],
                    'info' => 'تحويل من حساب #' . $accountSource->name . ' - ' . $data['info'],
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
