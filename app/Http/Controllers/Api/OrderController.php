<?php

namespace App\Http\Controllers\Api;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\ApiHelper;
use App\Helper\HelperBalance;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;

use App\Http\Resources\Api\PaginateResource;
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
    public function index()
    {
        $status = \request()->get('status');
        $qr = \request()->get('qr');
        $orders = Order::
        when(!empty($status), fn($query) => $query->where('status', $status))
            ->when(!empty($qr), fn($query) => $query->where('qr_code', $qr))
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
            $order->update(['given_id' => auth()->id(),'status' => OrderStatusEnum::SUCCESS->value]);
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
            $order->update(['status' => OrderStatusEnum::CONFIRM_RETURNED->value]);
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
        $order = Order::where('qr_code', $id)->first();
        if ($order) {
            return ApiHelper::apiResponse([
                'order' => new OrderResource($order),
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
