<?php

namespace App\Http\Controllers;

use App\Exports\OrderExport;
use Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportOrder(){
        return Excel::download(new OrderExport(), 'orders.xlsx');
    }
}
