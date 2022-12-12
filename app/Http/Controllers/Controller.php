<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      title="Todo API",
 *      version="0.1"
 * )
 * @OA\Server(
 *      url="http://localhost",
 *      description="로컬"
 * )
 * @OA\Tag(
 *      name="로그인",
 *      description="로그인/로그아웃"
 * )
 * @OA\Tag(
 *      name="회원가입",
 *      description="회원가입"
 * )
 * @OA\Tag(
 *      name="사용자",
 *      description="사용자 정보"
 * )
 * @OA\Tag(
 *      name="일정",
 *      description="일정 관련 api"
 * )
 * @OA\Tag(
 *      name="태그",
 *      description="태그 관련 api"
 * )
 * @OA\Tag(
 *      name="루틴",
 *      description="루틴 관련 api"
 * )
 * @OA\Schema(
 *      schema="ResponseAbort",
 *      description="잘못된 요청",
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          description="에러 메시지",
 *          example="잘못된 요청입니다."
 *      )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
