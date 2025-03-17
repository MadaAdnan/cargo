<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Helper\ApiHelper;
use App\Helper\HelperBalance;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;

use App\Models\Order;
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
        //
    }

    public function SetToSuccess(Request $request)
    {
        if ((int)$request->order_id <= 0) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم الشحنة',
            ], 401, 'error');
        }
        $order = Order::whereNot('status', OrderStatusEnum::SUCCESS->value)->find($request->order_id);
        if (!$order) {
            return ApiHelper::apiResponse([
                'msg' => 'الشحنة غير موجودة',
            ], 401, 'error');
        }
        DB::beginTransaction();
        try {
            HelperBalance::completeOrder($order);
            $order->update(['status' => OrderStatusEnum::SUCCESS->value]);
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
}
