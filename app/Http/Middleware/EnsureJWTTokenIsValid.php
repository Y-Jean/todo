<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
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

        $data = explode('.', $token);

        if (count($data) !== 3) {
            abort(410, __('aborts.token_is_not_valid'));
        }

        $jwtHeader = JWT::jsonDecode(JWT::urlsafeB64Decode($data[0]));
        $decoded = JWT::decode($token, new Key($publicKey, $jwtHeader->alg));

        $user = User::where('email', $decoded->email)->find($decoded->user_id);
        if ($user === null) {
            abort(403, __('aborts.do_not_exist_user'));
        }

        $request->attributes->set('user', $user);
        $request->attributes->set('decoded', $decoded);

        return $next($request);
    }
}
