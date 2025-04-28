<?php

namespace App\Http\Controllers\Api;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\ApiHelper;
use App\Helper\HelperBalance;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;

use App\Http\Resources\Api\PaginateResource;
use App\Models\Marker;
use App\Models\Order;
use App\Models\User;
use DB;
use Error;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $status = \request()->get('status');
    //     $qr = \request()->get('qr_code');
    //     $me = \request()->get('only');
    //     $orders = Order::whereHas('markers', fn($query) => $query->where('markers.user_id', auth()->id()))
    //         ->when($me == 'me', fn($query) => $query->where('current_user', auth()->id()))
    //         ->when(!empty($status), fn($query) => $query->where('status', $status))
    //         ->when(!empty($qr), fn($query) => $query->where('qr_code', $qr))
    //         ->latest()
    //         ->with(['citySource', 'branchSource', 'cityTarget', 'branchTarget', 'unit', 'sender', 'createdBy'])
    //         ->paginate(15);
    //     return ApiHelper::apiResponse([
    //         'orders' => OrderResource::collection($orders),
    //         'paginate' => new PaginateResource($orders)
    //     ]);
    // }

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
            $order->update(['given_id' => auth()->id(), 'status' => OrderStatusEnum::SUCCESS->value,'canceled_info'=>$msg]);
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
            $order->update(['status' => OrderStatusEnum::CONFIRM_RETURNED->value,'canceled_info'=>$msg]);
            HelperBalance::confirmReturn($order);
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
