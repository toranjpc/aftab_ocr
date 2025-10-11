<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Auth\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'resetPassword', 'verification']]);
    }

    public function login()
    {
        $rules = ['captcha' => 'required|captcha_api:' . request('key') . ',flat'];
        if (!empty(request('nocapt'))) $rules = [];//حذف

        $validator = validator()->make(request()->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'راستی ازمایی با شکست مواجه شد'], 401);
        }

        $credentials = request(['username', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'مشخصات کاربری اشتباه وارد شده است!'], 401);
        }

        // if (!auth()->user()->activated) {
        //     return response()->json(['message' => 'حساب کاربری شما مسدود شده است'], 401);
        // }

        event(new Login('api', auth('api')->user(), false));

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return ['user' => auth()->user()];
    }

    public function logout(Request $request)
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ];

        \DB::table('logs')->insert([
            'user_id' => auth('api')->user()->id,
            'log_date' => date('Y-m-d H:i:s'),
            'table_name' => '',
            'log_type' => 'lockout',
            'data' => json_encode($data)
        ]);

        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function resetPassword(Request $request)
    {
        $user = User::firstOrCreate($request->only(['phone']));

        if ($user && cache('auth-' . $user->id) && $user->phone == $request['phone']) {
            return response([
                'message' => 'تنها هر دو دقیقه امکان ارسال پیامک وجود دارد'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->sendCode($user);

        return response()->json([
            'message' => 'کد تایید برای شما ارسال شد.'
        ], Response::HTTP_OK);
    }

    public function verification(Request $request)
    {
        $user = User::firstOrCreate($request->only(['phone']));

        if (!$user || cache('auth-' . $user->id) != $request->code) {
            return response([
                'message' => 'کد وارد شده اشتباه است.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->password !== $request->password_confirm) {
            return response([
                'message' => 'پسور با تکرار آن برابر نیست.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return ['message' => 'رمز با موفقیت تغییر کرد'];
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function changePass(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'pass.pass' => 'required|string',
            'pass.new_pass' => 'required|string|min:8|same:pass.new_pass_rep',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده معتبر نیستند.',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // پیدا کردن کاربر
        $user = User::find($request->user_id);

        // بررسی صحت کلمه عبور فعلی
        if (!Hash::check($request->input('pass.pass'), $user->password)) {
            return response()->json([
                'message' => 'کلمه عبور فعلی نادرست است.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // به‌روزرسانی کلمه عبور جدید
        $user->password = Hash::make($request->input('pass.new_pass'));
        $user->last_pass_change = Carbon::now();
        $user->save();

        return response()->json([
            'message' => 'کلمه عبور با موفقیت تغییر یافت.'
        ], Response::HTTP_OK);
    }
}
