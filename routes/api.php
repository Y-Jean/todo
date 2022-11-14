<?php

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
        Route::post('login', [UserController::class, 'login']);

        Route::middleware(['jwt'])->group(function () {
                Route::get('profile', [UserController::class, 'getProfile']);
            }
        );
    }
);
