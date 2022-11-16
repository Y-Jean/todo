<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EnsureJWTTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken('Authorization');
        if (!$token) {
            abort(410, __('aborts.token_is_not_valid'));
        }

        $publicKey = Storage::disk('local')->get(config('constants.jwt.KEY_PUBLIC'));

        $explodedToken = explode('.', $token);

        if (count($explodedToken) !== 3) {
            abort(410, __('aborts.token_is_not_valid'));
        }

        $jwtHeader = JWT::jsonDecode(JWT::urlsafeB64Decode($explodedToken[0]));
        $data = JWT::decode($token, new Key($publicKey, $jwtHeader->alg));

        $user = User::where('email', $data->email)->find($data->user_id);
        if ($user === null) {
            abort(403, __('aborts.do_not_exist_user'));
        }

        $key = sprintf(config('constants.cache.LOGIN_USER'), $user->id);

        // 로그아웃된 사용자
        if (!Cache::has($key)) {
            abort(410, __('aborts.logout_user'));
        }

        // 다른 pc에서 로그인되서 새 토큰이 발행된 경우
        if (Cache::get($key) !== $token) {
            abort(410, __('aborts.login_another_browser'));
        }

        // 세션타임 갱신
        Cache::put($key, $token, Carbon::now()->addMinutes(env('SESSION_MINUTES', 60)));

        $request->attributes->set('user', $user);
        $request->attributes->set('data', $data);

        return $next($request);
    }
}
