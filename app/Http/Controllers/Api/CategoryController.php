<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helper\ApiHelper;
use App\Enums\OrderTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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


    public function getCategoreisByType(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:size,weight',
        ]);

        if ($validator->fails()) {
            return ApiHelper::apiResponse([
                'errors' => $validator->errors(),
                'msg' => 'البيانات المدخلة غير صالحة',
            ], 422, 'error');
        }

        // جلب الفئات حسب النوع
        $categories = Category::where('type', $request->type)->get();

        if ($categories->isEmpty()) {
            return ApiHelper::apiResponse([
                'msg' => 'لا توجد فئات متاحة لهذا النوع',
            ], 404, 'error');
        }

        return ApiHelper::apiResponse([
            'categories' => $categories,
        ], 200, 'success');
    }

}
