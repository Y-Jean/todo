<?php

use App\Http\Controllers\{
    RegisterController,
    RoutineController,
    TagController,
    TaskController,
    UserController,
};
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
            // 회원탈퇴
            Route::delete('withdrawal', [RegisterController::class, 'delete']);

            /**
             * 프로필 관련
             */
            Route::controller(UserController::class)->prefix('profile')->group(function () {
                // 프로필 조회
                Route::get('/', 'show');
                // 프로필 수정
                Route::put('/', 'update');
                // 비밀번호 수정
                Route::put('/password', 'updatePassword');
            });

            /**
             * 일정 관련
             */
            Route::controller(TaskController::class)->prefix('tasks')->group(function () {
                // 일정 추가
                Route::post('/', 'store');
                // 일정 수정
                Route::put('/{task_id}', 'update')->where('task_id', '[0-9]+');
                // 일정 조회 (단위기간 기준)
                Route::get('/', 'index');
                // 일정 조회 (리스트)
                Route::get('/list', 'listOfTasks');
                // 일정 완료여부 수정
                Route::patch('/{task_id}/done', 'updateDone')->where('task_id', '[0-9]+');
                // 일정 상세보기
                Route::get('/{task_id}', 'show')->where('task_id', '[0-9]+');
                // 일정 삭제
                Route::delete('/{task_id}', 'destroy')->where('task_id', '[0-9]+');
            });

            /**
             * 태그 관련
             */
            Route::controller(TagController::class)->prefix('tags')->group(function () {
                // 태그 추가
                Route::post('/', 'store');
                // 태그 수정
                Route::put('/{tag_id}', 'update')->where('tag_id', '[0-9]+');
                // 태그 리스트
                Route::get('/', 'index');
                // 태그 조회
                Route::get('/{tag_id}', 'show')->where('tag_id', '[0-9]+');
                // 태그 삭제
                Route::delete('/{tag_id}', 'destroy')->where('tag_id', '[0-9]+');
            });

            /**
             * 반복일정(루틴) 관련
             */
            Route::controller(RoutineController::class)->prefix('routines')->group(function () {
                // 루틴 추가
                Route::post('/', 'store');
                // 루틴 수정
                Route::put('/{routine_id}', 'update')->where('routine_id', '[0-9]+');
                // 루틴 리스트
                Route::get('/', 'index');
                // 루틴 조회
                Route::get('/{routine_id}', 'show')->where('routine_id', '[0-9]+');
                // 루틴 삭제
                Route::delete('/{routine_id}', 'destroy')->where('routine_id', '[0-9]+');
            });
        });
    }
);
