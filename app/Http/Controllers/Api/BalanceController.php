<?php

namespace App\Http\Controllers\Api;

use App\Enums\BalanceTypeEnum;
use App\Enums\LevelUserEnum;
use App\Helper\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BalanceResource;
use App\Http\Resources\Api\PaginateResource;
use App\Models\Balance;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Balance::where("user_id", auth()->id());
        // $balances=Balance::where('user_id',auth()->id())->latest()->paginate(30);
        if($request->pending !== null){
            $query->where("pending", $request->pending);
        }
        $balances = $query->latest()->paginate(30);
        return ApiHelper::apiResponse([
            'balance' => BalanceResource::collection($balances),
            'paginate' => new PaginateResource($balances)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function push(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = User::find($request->userId);
            $isUser=$user->level==LevelUserEnum::USER->value;
            if (!in_array($request->currencyId, [1,2,3])) {
                return ApiHelper::apiResponse([
                    'msg' =>  '  يجب تحديد العملة 1 للدولار , 2 للتركي , 3 للسوري'
                ], 401, 'error');
            }
            // amount
            if ((double)$request->amount <= 0) {
                return ApiHelper::apiResponse([
                    'msg' => 'يرجى تحديد قيمة الدفعة'
                ], 401, 'error');
            }
            if ($user == null) {
                return ApiHelper::apiResponse([
                    'msg' => 'يرجى تحديد الطرف المقابل'
                ], 401, 'error');
            }
            if ($user?->id == auth()->id()) {

                return ApiHelper::apiResponse([
                    'msg' => 'لا يمكنك عمل سند لنفسك'
                ], 401, 'error');
            }
            $uuid = \Str::uuid()->toString();
            // currency
            Balance::create([
                'uuid' => $uuid,
                'type' => BalanceTypeEnum::PUSH->value,
                'user_id' => $user->id,
                'debit' => 0,
                'credit' => $request->amount,
                'info' => $request->info,
                'currency_id' => $request->currencyId,
                'is_complete' => $isUser,
                'pending' => !$isUser,
                'customer_name' => auth()->user()->name,
            ]);
            $balance = Balance::create([
                'uuid' => $uuid,
                'type' => BalanceTypeEnum::CATCH->value,
                'user_id' => auth()->id(),
                'debit' => $request->amount,
                'credit' => 0,
                'info' => $request->info,
                'currency_id' => $request->currencyId,
                'is_complete' => $isUser,
                'pending' => false,
                'customer_name' => $user?->name,
            ]);

            \DB::commit();
            return ApiHelper::apiResponse([
                'msg' => 'تم إنشاء السند بإنتظار موافقة الطرف الآخر',
                'balance' => new BalanceResource($balance),
            ]);
        } catch (\Exception | \Error $e) {
            \DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage()
            ], 401, 'error');
        }
    }



    public function pushConfirmed(string $id)
    {
        $balance = Balance::find($id);
        if ($balance == null) {
            return ApiHelper::apiResponse([
                'msg' => 'لم يتم العثور على القيد'
            ], 401, 'error');
        }
        if(!empty($balance->uuid)){
            Balance::where('uuid',$balance->uuid)->update([
                'is_complete'=>true,
                'pending'=>false
            ]);
        }else{
            $balance->update([
                'is_complete'=>true,
                'pending'=>false
            ]);
        }
        $balance->refresh();
        return ApiHelper::apiResponse([
            'msg' => 'تم تاكيد الدفعة',
            'balance' => new BalanceResource($balance),
        ]);
    }


    public function pushCancel(string $id)
    {
        $balance = Balance::find($id);
        if ($balance == null) {
            return ApiHelper::apiResponse([
                'msg' => 'لم يتم العثور على القيد'
            ], 401, 'error');
        }
        if($balance->pending == false || $balance->is_complete == 1){
            return ApiHelper::apiResponse([
                'msg' => 'لا يمكن حذف قيد تمت الموافقة عليه'
            ], 401, 'error');
        }
        if(!empty($balance->uuid)){
            Balance::where('uuid',$balance->uuid)->delete();
        }else{
            $balance->delete();
        }
        return ApiHelper::apiResponse([
            'msg' => 'تم تاكيد الدفعة',
            // 'balance' => new BalanceResource($balance),
        ],200 ,'success');
    }




    public function pull(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = User::find($request->userId);
            $isUser=$user->level==LevelUserEnum::USER->value;
            if (!in_array($request->currencyId, [1, 2])) {
                return ApiHelper::apiResponse([
                    'msg' => 'يجب تحديد العملة 1 للدولار , 2 للتركي'
                ], 401, 'error');
            }
            // amount
            if ((double)$request->amount <= 0) {
                return ApiHelper::apiResponse([
                    'msg' => 'يرجى تحديد قيمة الدفعة'
                ], 401, 'error');
            }
            if ($user == null) {
                return ApiHelper::apiResponse([
                    'msg' => 'يرجى تحديد الطرف المقابل'
                ], 401, 'error');
            }
            if ($user?->id == auth()->id()) {

                return ApiHelper::apiResponse([
                    'msg' => 'لا يمكنك عمل سند لنفسك'
                ], 401, 'error');
            }
            $uuid = \Str::uuid()->toString();
            // currency
            Balance::create([
                'uuid' => $uuid,
                'type' => BalanceTypeEnum::PUSH->value,
                'user_id' => $user->id,
                'debit' => $request->amount,
                'credit' => 0,
                'info' => $request->info,
                'currency_id' => $request->currencyId,
                'is_complete' => true,
                'pending' =>false,
                'customer_name' => auth()->user()->name,
            ]);
            $balance = Balance::create([
                'uuid' => $uuid,
                'type' => BalanceTypeEnum::CATCH->value,
                'user_id' => auth()->id(),
                'debit' => 0,
                'credit' => $request->amount,
                'info' => $request->info,
                'currency_id' => $request->currencyId,
                'is_complete' => true,
                'pending' => false,
                'customer_name' => $user?->name,
            ]);

            \DB::commit();
            return ApiHelper::apiResponse([
                'msg' => 'تم إنشاء السند بنجاح',
                'balance' => new BalanceResource($balance),
            ]);
        } catch (\Exception | \Error $e) {
            \DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage()
            ], 401, 'error');
        }
    }

    public function pendingBalancesCount(){
        $user = auth()->user();

        if (!$user) {
            return ApiHelper::apiResponse([], 401, 'Unauthorized');
        }

        // $balanceCount = Balance::where('user_id', $user->id)
        // ->where('pending',true)->count();
        $balanceCount = $user->pendingBalances->count();
        return ApiHelper::apiResponse([
            'pendingBalancesCount'=>$balanceCount
        ],200 , 'success');
    }
}
