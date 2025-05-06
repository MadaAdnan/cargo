<?php

namespace App\Http\Controllers\Api;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\ApiHelper;
use App\Helper\HelperBalance;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;

use App\Http\Resources\Api\PaginateResource;
use App\Http\Resources\Api\SenderOrRecevirOrderInfoResource;
use App\Models\Marker;
use App\Models\Order;
use App\Models\User;
use DB;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Database\Eloquent\Collection;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $status = \request()->get('status');
        $qr = \request()->get('qr_code');
        $me = \request()->get('only');
        $branchSource = \request()->get('branch_source_id'); // الفرع المرسل
        $branchTarget = \request()->get('branch_target_id'); // الفرع المستقبل
        $shipmentDate = \request()->get('shipment_date'); // تاريخ الشحنة
        $orders = Order::whereHas('markers', fn($query) => $query->where('markers.user_id', auth()->id()))
            ->when($me == 'me', fn($query) => $query->where('current_user', auth()->id()))
            ->when(!empty($status), fn($query) => $query->where('status', $status))
            ->when(!empty($qr), fn($query) => $query->where('qr_code', $qr))
            //الفرع المرسل
            ->when(!empty($branchSource), fn($query) => $query->where('branch_source_id', $branchSource))
            // الفرع المستقبل
            ->when(!empty($branchTarget), fn($query) => $query->where('branch_target_id', $branchTarget))
            // تاريخ الشحنة
            ->when(!empty($shipmentDate), fn($query) => $query->where('shipping_date', $shipmentDate))

            ->latest()
            ->with(['citySource', 'branchSource', 'cityTarget', 'branchTarget', 'unit', 'sender', 'createdBy'])
            ->paginate(15);
        return ApiHelper::apiResponse([
            'orders' => OrderResource::collection($orders),
            'paginate' => new PaginateResource($orders)
        ]);
    }

    public function setToSuccess(Request $request)
    {
        $msg=$request->msg;
        if (empty($request->qr_code)) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم الشحنة',
            ], 401, 'error');
        }
        $order = Order::whereNot('status', OrderStatusEnum::SUCCESS->value)
            ->whereNot('status', OrderStatusEnum::CONFIRM_RETURNED->value)
            ->where('qr_code', $request->qr_code)->first();
        if (!$order) {
            return ApiHelper::apiResponse([
                'msg' => 'الشحنة غير موجودة',
            ], 401, 'error');
        }

        DB::beginTransaction();


        try {
            $order->update(['given_id' => auth()->id(), 'status' => OrderStatusEnum::SUCCESS->value,'canceled_info'=>$msg , 'current_user' => $order?->receive_id]);
            Marker::create([
                'user_id' => $order?->receive_id,
                'order_id' => $order->id
            ]);
            HelperBalance::completeOrder($order);
            DB::commit();
            $order->refresh();
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
            ]);


        } catch (\Exception | Error $e) {
            DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage(),
            ], 401, 'error');
        }
    }

    public function setToReturned(Request $request)
    {
        $msg=$request->msg;
        if (empty($request->qr_code)) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم الشحنة',
            ], 401, 'error');
        }
        $order = Order::whereNot('status', OrderStatusEnum::RETURNED->value)
            ->whereNot('status', OrderStatusEnum::CONFIRM_RETURNED->value)
            ->whereNot('status', OrderStatusEnum::SUCCESS->value)
            ->where('qr_code', $request->qr_code)->first();
        if (!$order) {
            return ApiHelper::apiResponse([
                'msg' => 'الشحنة غير موجودة',
            ], 401, 'error');
        }
        DB::beginTransaction();
        try {


            $user = User::where([
                'level' => LevelUserEnum::BRANCH->value,
                'branch_id' => $order->branch_source_id
            ])->first()?->id;
            $dataUpdate['status'] = OrderStatusEnum::RETURNED->value;
            $dataUpdate['given_id'] = $user;
            $dataUpdate['returned_id'] = $order->pick_id;
            $dataUpdate['canceled_info'] = $msg;

            $order->update($dataUpdate);
            DB::commit();
            $order->refresh();
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
            ]);
        } catch (\Exception | Error $e) {
            DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage(),
            ], 401, 'error');
        }
    }

    public function setToConfirmedReturned(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'far'=> 'required',
        ],[
            'far.required' => 'حقل سؤال تحميل الاجور على المرسل مطلوب',
        ]);
         // إذا فشل التحقق
         if ($validator->fails()) {
            return ApiHelper::apiResponse([
                'errors' => $validator->errors(),
                'msg' => 'البيانات المدخلة غير صالحة'
            ], 422, 'error');
        }
        $msg=$request->msg;

        if (empty($request->qr_code)) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم الشحنة',
            ], 401, 'error');
        }
        $order = Order::where('status', OrderStatusEnum::RETURNED->value)->where('qr_code', $request->qr_code)->first();
        if (!$order) {
            return ApiHelper::apiResponse([
                'msg' => 'الشحنة غير موجودة',
            ], 401, 'error');
        }
        DB::beginTransaction();
        try {
            $order->update(['status' => OrderStatusEnum::CONFIRM_RETURNED->value,'canceled_info'=>$msg , 'current_user' => $order?->sender_id]);
            Marker::create([
                'user_id' => $order?->sender_id,
                'order_id' => $order->id
            ]);
            HelperBalance::confirmReturn($order , $request->far);
            DB::commit();
            $order->refresh();
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
            ]);

        } catch (\Exception | Error $e) {
            DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage(),
            ], 401, 'error');
        }
    }

    public function setToCanceled(Request $request)
    {
        if (empty($request->qr_code)) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم الشحنة',
            ], 401, 'error');
        }
        $order = Order:: whereNot('status', OrderStatusEnum::SUCCESS->value)
            ->whereNot('status', OrderStatusEnum::CONFIRM_RETURNED->value)->where('qr_code', $request->qr_code)->first();
        if (!$order) {
            return ApiHelper::apiResponse([
                'msg' => 'الشحنة غير موجودة',
            ], 401, 'error');
        }
        DB::beginTransaction();
        try {
            $order->update(['status' => OrderStatusEnum::CANCELED->value, 'canceled_info' => $request->msg]);
            DB::commit();
            $order->refresh();
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
            ]);

        } catch (\Exception | Error $e) {
            DB::rollBack();
            return ApiHelper::apiResponse([
                'msg' => $e->getMessage(),
            ], 401, 'error');
        }
    }

    public function getSenderOrRecevirUserInfo(){
        $users = User::where('level',LevelUserEnum::USER->value)
        ->active()->get();
        if (!$users) {
            return ApiHelper::apiResponse([
                'msg' =>'لا يوجد مستخدمين',
            ], 401, 'error');
        }
        return ApiHelper::apiResponse([
            'users' =>SenderOrRecevirOrderInfoResource::collection($users),
        ], 200, 'success');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $data = $request->validated();

            // // Set default values similar to the form
            // $data['shipping_date'] = $data['shipping_date'] ?? Carbon::now()->format('Y-m-d');
            // $data['type'] = $data['type'] ?? OrderTypeEnum::HOME->value;

            // // Handle sender information
            // if (isset($data['sender_id'])) {
            //     $sender = User::active()->with('city')->find($data['sender_id']);
            //     if ($sender) {
            //         $data['sender_phone'] = $sender->phone;
            //         $data['sender_address'] = $sender->address;
            //         $data['city_source_id'] = $sender->city_id;
            //         $data['pick_id'] = User::active()
            //             ->where(['level' => LevelUserEnum::BRANCH->value, 'branch_id' => $sender->branch_id])
            //             ->first()?->id;
            //     }
            // }

            // // Handle receiver information
            // if (isset($data['receive_id'])) {
            //     $receiver = User::with('city')->find($data['receive_id']);
            //     if ($receiver) {
            //         $data['receive_phone'] = $receiver->phone;
            //         $data['receive_address'] = $receiver->address;
            //         $data['city_target_id'] = $receiver->city_id;
            //     }
            // }

            // // Generate QR code if allowed
            // if ($data['allow_duplicates'] ?? true) {
            //     $data['qr_code'] = $data['qr_code'] ?? Str::random(10);
            // }

            // // Create the order
            $order = Order::create($data);

            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
            ], 200, 'success');

        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500 );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $userId = \request()->get('userId');
        $order = Order::where('qr_code', $id)->first();


        if (!empty($userId) && $order) {
            $user = User::find($userId);
            if ($user) {
                Marker::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id
                ]);
                $order->update(['current_user' => $userId]);
            }
        }
        if ($order) {
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order->refresh()),
            ]);
        }
        return ApiHelper::apiResponse([
            'msg' => "لم يتم العثور على الشحنة",
        ], 401, 'error');

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
}
