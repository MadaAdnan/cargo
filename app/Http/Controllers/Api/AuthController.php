<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login .
     */
    public function login(Request $request)
    {
        if (empty($request->email) || empty($request->password)) {
            return ApiHelper::apiResponse([
                'msg' => 'تأكد من صحة البيانات المدخلة'
            ], 401, 'error');
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiHelper::apiResponse([
                'msg' => 'البريد غير مسجل في النظام'
            ], 401, 'error');
        }
        if (!\Hash::check($request->password, $user->password)) {
            return ApiHelper::apiResponse([
                'msg' => 'تأكد من صحة البيانات المدخلة'
            ], 401, 'error');
        }
        $token = $user->createToken('token')->plainTextToken;
        return ApiHelper::apiResponse([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
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
