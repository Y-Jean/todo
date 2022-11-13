<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|max:100|email',
            'password' => 'required',
        ], [
            'email.*' => __('validations.email'),
            'password.*' => __('validations.password'),
            '*' => __('validations.format'),
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        // 해당 이메일의 사용자가 없을 경우
        if ($user === null) {
            abort(403, __('aborts.do_not_exist_user'));
        }

        // 비밀번호가 일치하지 않을 경우
        if (!Hash::check($password, $user->password)) {
            abort(403, __('aborts.do_not_match_password'));
        }

        // 토큰발행
        $accessToken = '';

        $privateKey = Storage::disk('local')->get(config('constants.jwt.KEY_PRIVATE'));
        $alg = config('constants.jwt.ALG');
        $iss = config('constants.jwt.ISSUER');

        $payload = [
            // 토큰 발급자
            'iss' => $iss,
            // 토큰이 발급된 시간
            'iat' => time(),
            // 토큰 만료시간
            'exp' => null,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ];

        $accessToken = JWT::encode($payload, $privateKey, $alg);

        return response()->json([
            'user_id' => $user->id,
            'token' => $accessToken,
            'token_type' => 'Bearer',
            'expired_in' => env('SESSION_TIME', 1200),
        ]);
    }
}
