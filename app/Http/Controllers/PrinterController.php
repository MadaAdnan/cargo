<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function printer(string $id){
        $order=Order::findOrFail($id);
        return view('print',compact('order'));
    }
}
