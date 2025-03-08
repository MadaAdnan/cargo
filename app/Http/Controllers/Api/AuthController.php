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


    public function profile(Request $request){
        /**
         * @var $user User
         */

        $user=auth()->user();
        if(empty($request->name)||\Str::length($request->name)<3){
            return ApiHelper::apiResponse([
                'msg'=>'يرجى إدخال اسم صالح',
                'input'=>'name'
            ],401,'error');
        }
        if(empty($request->email)|| !filter_var($request->email,FILTER_VALIDATE_EMAIL)){
            return ApiHelper::apiResponse([
                'msg'=>'يرجى إدخال بريد صالح',
                'input'=>'email'
            ],401,'error');
        }
        if(empty($request->phone)||\Str::length($request->phone)<9){
            return ApiHelper::apiResponse([
                'msg'=>'يرجى إدخال رقم هاتف صالح',
                'input'=>'phone'
            ],401,'error');
        }
        if(!empty($request->password)&& \Str::length($request->phone)<8){
            return ApiHelper::apiResponse([
                'msg'=>'يرجى إدخال كلمة مرور من 8 أحرف على الأقل',
                'input'=>'password'
            ],401,'error');
        }
        if($request->confirm_password!=$request->password){
            return ApiHelper::apiResponse([
                'msg'=>'كلمة المرور غير متطابقة',
                'input'=>'password'
            ],401,'error');
        }
       /* $this->validate($request,[
            'name'=>'required|string',
            'email'=>'required|email|unique:users,email,'.auth()->id(),
            'password'=>'nullable|min:8',
            'confirm_password'=>'same:password',
            'phone'=>'required',
        ]);*/
        $data['name']=$request->name;
        $data['email']=$request->email;
        $data['phone']=$request->phone;
        if(!empty($request->password)){
            $data['password']=bcrypt($request->password);
        }

        $user->update($data);
        return ApiHelper::apiResponse(['user'=>new UserResource($user->refresh())]);
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
