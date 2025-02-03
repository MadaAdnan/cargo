<?php

namespace App\Http\Controllers;

use App\Exports\BalanceReport;
use App\Exports\OrderExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportOrder(Request $request)
    {
        $filters = $request->input('filters') != null ? $request->input('filters')['created_at'] : [];
        return Excel::download(new OrderExport($filters), 'orders.xlsx');
    }

    public function exportBalanceReport(Request $request){
        $filters = $request->input('filters') != null ? $request->input('filters')['user_id'] : [];
        return Excel::download(new BalanceReport($filters), 'balanceReport.xlsx');
    }
}
