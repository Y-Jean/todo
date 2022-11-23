<?php

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(
    ['prefix' => '{v}', 'where' => ['v' => 'v1|V1']],
    function () {
        // 로그인
        Route::post('login', [UserController::class, 'login']);

        //로그아웃
        Route::post('logout', [UserController::class, 'logout']);

        // 회원가입
        Route::post('register', [RegisterController::class, 'store']);


        Route::middleware(['jwt'])->group(function () {
            // 사용자 프로필 조회
            Route::get('profile', [UserController::class, 'getProfile']);

            // 회원탈퇴
            Route::delete('withdrawal', [RegisterController::class, 'delete']);

            // 일정 관련
            Route::post('task', [TaskController::class, 'store']);
        });
    }
);
