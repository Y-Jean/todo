<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\KEY;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * public @method login(Request $request) :: 로그인
 * public @method logout(Request $request) :: 로그아웃
 * public @method getProfile(Request $request) :: 사용자 정보 조회
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/login",
     *      tags={"로그인"},
     *      summary="로그인",
     *      description="이메일을 통한 로그인",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="(필수)사용자 이메일",
     *                  example="example@jiran.com"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  format="password",
     *                  description="(필수)사용자 비밀번호",
     *                  example="todo1234!!"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="user_id",
     *                  type="integer",
     *                  description="사용자 번호"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="JWT 토큰"
     *              ),
     *              @OA\Property(
     *                  property="token_type",
     *                  type="string",
     *                  description="토큰 유형"
     *              ),
     *              @OA\Property(
     *                  property="expired_at",
     *                  type="string",
     *                  description="토큰 만료시간"
     *              ),
     *              example={
     *                  "user_id": 1,
     *                  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJpc3MiOiJvZmZpY2V3YXZlLWFwaSIsImlhdCI6MTY2ODM5MDk3NSwiZXhwIjpudWxsLCJ1c2VyX2lkIjoxMSwiZW1haWwiOiJ0ZXN0QGV4YW1wbGUuY29tIiwibmFtZSI6ImplYW4ifQ.9M_yhzpY86QBg57yF3AfqxjfHkMPmps9ukzcNbfXEP0JLlM4dD5VDqm-HU1JHE0guWCAiCOIbUpm0nhccN5yPw",
     *                  "token_type": "Bearer",
     *                  "expired_at": "2022-11-16 11:44:51"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="유효하지 않은 요청",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|max:100|email',
            'password' => 'required|regex:/(?=.*\d{1,})(?=.*[~`!@#$%\^&*()-+=]{1,})(?=.*[a-zA-Z]{2,}).{8,16}$/',
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

        $user->last_login_at = Carbon::now();
        $user->save();

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

        // 로그인 사용자 캐시에 저장
        $expiredAt = Carbon::now()->addMinutes(env('SESSION_MINUTES', 60));
        Cache::put(sprintf(config('constants.cache.LOGIN_USER'), $user->id), $accessToken, $expiredAt);

        return response()->json([
            'user_id' => $user->id,
            'token' => $accessToken,
            'token_type' => 'Bearer',
            'expired_at' => $expiredAt,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/logout",
     *      tags={"로그인"},
     *      summary="로그아웃",
     *      description="로그아웃",
     *      @OA\Response(
     *          response="201",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="result",
     *                  type="string",
     *                  description="성공 여부"
     *              ),
     *              example={
     *                  "result": "success",
     *              }
     *          )
     *      )
     * )
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token !== null) {
            $publicKey = Storage::disk('local')->get(config('constants.jwt.KEY_PUBLIC'));
    
            $explodedToken = explode('.', $token);
            if (count($explodedToken) !== 3) {
                abort(410, __('aborts.token_is_not_valid'));
            }

            $jwtHeader = JWT::jsonDecode(JWT::urlsafeB64Decode($explodedToken[0]));
            $data = JWT::decode($token, new Key($publicKey, $jwtHeader->alg));

            // 캐시 삭제
            $key = sprintf(config('constants.cache.LOGIN_USER'), $data->user_id);
            if (Cache::get($key) == $request->bearerToken()) {
                Cache::forget($key);
            }
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/profile",
     *      tags={"사용자"},
     *      summary="사용자 프로필 조회",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="이름",
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="이메일",
     *              ),
     *              @OA\Property(
     *                  property="status_message",
     *                  type="string",
     *                  description="상태메세지",
     *              ),
     *              example={
     *                  "name": "jean",
     *                  "email": "test@example.com",
     *                  "status_message": null
     *              }
     *          )
     *      )
     * )
     */
    public function show(Request $request)
    {
        return $request->get('user')->only(['name', 'email', 'status_message']);
    }


    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'nullable|string|max:100',
            'status_message' => 'nullable|string|max:300',
            'delete_status_message' => 'required|boolean',
        ], [
            '*' => __('validations.format')
        ]);

        // 사용자 정보
        $user = $request->get('user');

        $name = $request->input('name') ?? $user->name;
        $statusMessage = $request->input('status_message') ?? $user->status_message;
        $deleteMessage = filter_var($request->input('delete_status_message'), FILTER_VALIDATE_BOOLEAN);

        // 상태메세지 삭제일 경우 입력받은 값이 있더라도 빈값으로 변경
        if ($deleteMessage) {
            $statusMessage = '';
        }

        User::whereId($user->id)->update([
            'name' => $name,
            'status_message' => $statusMessage
        ]);

        return response()->json([
            'result' => 'success'
        ], 201);
    }


    public function editPassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|confirmed|regex:/(?=.*\d{1,})(?=.*[~`!@#$%\^&*()-+=]{1,})(?=.*[a-zA-Z]{2,}).{8,16}$/',
            'new_password_confirmation' => 'required'
        ], [
            'new_password.*' => __('validations.password'),
            '*' => __('validations.format')
        ]);

        // 사용자 정보
        $user = $request->get('user');

        $oldPassword = $request->input('current_password');
        $password = $request->input('new_password');

        // 비밀번호가 일치하지 않을 경우
        if (!Hash::check($oldPassword, $user->password)){
            abort(403, __('aborts.do_not_match_password'));
        }

        // 이전 비밀번호와 새 비밀번호가 같은 경우
        if ($oldPassword === $password) {
            abort(403, __('aborts.do_not_upsate_same_password'));
        }

        User::whereId($user->id)->update([
            'password' => Hash::make($password)
        ]);

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
