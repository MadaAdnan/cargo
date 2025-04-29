<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['me']);
    }

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
            'user' => new UserResource($user ),
            'token' => $token
        ]);
    }

    public function me()
    {
        return ApiHelper::apiResponse([
            'me'=>new UserResource(auth()->user()),
        ]);
    }

    public function profile(Request $request)
    {
        /**
         * @var $user User
         */

        $user = auth()->user();
        if (empty($request->name) || \Str::length($request->name) < 3) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال اسم صالح',
                'input' => 'name'
            ], 401, 'error');
        }
        if (empty($request->email) || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال بريد صالح',
                'input' => 'email'
            ], 401, 'error');
        }
        if (empty($request->phone) || \Str::length($request->phone) < 9) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال رقم هاتف صالح',
                'input' => 'phone'
            ], 401, 'error');
        }
        if (!empty($request->password) && \Str::length($request->phone) < 8) {
            return ApiHelper::apiResponse([
                'msg' => 'يرجى إدخال كلمة مرور من 8 أحرف على الأقل',
                'input' => 'password'
            ], 401, 'error');
        }
        if ($request->confirm_password != $request->password) {
            return ApiHelper::apiResponse([
                'msg' => 'كلمة المرور غير متطابقة',
                'input' => 'password'
            ], 401, 'error');
        }
        /* $this->validate($request,[
             'name'=>'required|string',
             'email'=>'required|email|unique:users,email,'.auth()->id(),
             'password'=>'nullable|min:8',
             'confirm_password'=>'same:password',
             'phone'=>'required',
         ]);*/
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['phone'] = $request->phone;
        if (!empty($request->password)) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        return ApiHelper::apiResponse(['user' => new UserResource($user->refresh())]);
    }

    /**
     * Display a listing of the resource.
     */
   /**
 * Get list of users with optional level filter
 *
 * @queryParam level string Filter users by level (admin,user,manager). No-example
 */
public function index(Request $request)
{
    $validator = Validator::make($request->all(), [
        'level' => 'nullable|in:admin,user'
    ], [
        'level.in' => 'قيمة الحقل level يجب أن تكون admin أو user'
    ]);

    if ($validator->fails()) {
        return ApiHelper::apiResponse([
            'msg' => $validator->errors()->first(), // أول رسالة خطأ
        ], 422, 'error'); // 422 هو رمز خطأ التحقق
    }
    $validated = $validator->validated();
    return User::query()
        ->select('id', 'name')
        ->when($validated['level'] ?? null, function ($query, $level) {
            $query->where('level', $level);
        })
        ->get()
        ->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name
        ]);
        // return User::select('id','name')->get()->map(fn($el)=>['id'=>$el->id,'name'=>$el->name]);
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
