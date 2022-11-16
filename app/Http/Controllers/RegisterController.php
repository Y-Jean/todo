<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * public @method store(Request $request) :: 회원가입
 */
class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/register",
     *      tags={"회원가입"},
     *      summary="회원가입",
     *      description="이메일을 통한 회원가입",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="(필수)사용자 이름",
     *                  example="변백현"
     *              ),
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
     *              ),
     *              @OA\Property(
     *                  property="password_confirmation",
     *                  type="string",
     *                  format="password",
     *                  description="(필수)사용자 비밀번호 확인",
     *                  example="todo1234!!"
     *              )
     *          )
     *      ),
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
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="이미 존재하는 사용자 이메일",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|max:100|email',
            'password' => 'required|required_with:password_confirmation|same:password_confirmation|regex:/(?=.*\d{1,})(?=.*[~`!@#$%\^&*()-+=]{1,})(?=.*[a-zA-Z]{2,}).{8,16}$/',
            'password_confirmation' => 'required'
        ], [
            'name.*' => __('validations.email'),
            'email.*' => __('validations.email'),
            'password.*' => __('validations.password'),
            '*' => __('validations.format'),
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        // 이미 존재하는 이메일인지 확인
        if (User::where('email', $email)->first() !== null) {
            abort(403, __('aborts.already_exist_email'));
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
