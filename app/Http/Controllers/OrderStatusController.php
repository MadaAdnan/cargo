<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Enums\OrderStatusEnum;
class OrderStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shipInfo');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show()
    {
        $trackingCode = request('tracking_code');

        $orderNow=Order::where('code',$trackingCode)->first();

        if (!$orderNow) {
            return redirect()->back()->withErrors(['message' => 'كود الطلب غير صحيح.']);
        }

//        dd($orderNow->status->getLabel());



        return redirect()->back()->with('status',$orderNow->status->getLabel());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
