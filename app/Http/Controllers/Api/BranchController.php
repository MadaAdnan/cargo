<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Helper\ApiHelper;
use App\Helper\HelperBalance;
class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
        {
            $branches = Branch::select('id', 'name')->get();

            if($branches->isEmpty()) {
                return ApiHelper::apiResponse([
                    'msg' => 'لا يوجد أفرع للعرض',
                ], 404, 'error');
            }

            return ApiHelper::apiResponse([
                'branches' => $branches,
                'msg' => 'تم جلب البيانات بنجاح'
            ], 200, 'success');
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
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        //
    }
}
