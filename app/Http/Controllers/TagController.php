<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/tags",
     *      tags={"태그"},
     *      summary="태그 리스트 조회",
     *      description="태그 리스트 조회",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="tags",
     *                  type="array",
     *                  description="태그 리스트",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer",
     *                          description="태그 번호"
     *                      ),
     *                      @OA\Property(
     *                          property="name",
     *                          type="string",
     *                          description="이름"
     *                      ),
     *                      @OA\Property(
     *                          property="position",
     *                          type="integer",
     *                          description="우선순위"
     *                      ),
     *                      @OA\Property(
     *                          property="color",
     *                          type="string",
     *                          description="hexColor"
     *                      )
     *                  )
     *              ),
     *              example={
     *                  "tags": {
     *                      {
     *                          "id": 1,
     *                          "name": "운동",
     *                          "position": 0,
     *                          "color": "#5ac7ca"
     *                      },
     *                      {
     *                          "id": 2,
     *                          "name": "독서",
     *                          "position": 1,
     *                          "color": "#111111"
     *                      }
     *                  }
     *              }
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        // 사용자 정보
        $user = $request->get('user');

        $tags = $user->tags()
                    ->orderBy('position')
                    ->get(['id', 'name', 'position', 'color']);

        return response()->json([
            'tags' => $tags
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/tags",
     *      tags={"태그"},
     *      summary="태그 생성",
     *      description="새로운 태그 생성",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="(필수)이름",
     *                  example="공부"
     *              ),
     *              @OA\Property(
     *                  property="color",
     *                  type="string",
     *                  description="(선택)hexColor",
     *                  example="#000000"
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
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'color' => [
                'nullable',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ]
        ], [
            '*' => __('validations.format')
        ]);
        // 사용자 정보
        $user = $request->get('user');

        $name = $request->input('name');
        $color = $request->input('color');

        // 마지막 순서의 태그 조회
        $lastTag = Tag::where('user_id', $user->id)->latest('position')->first();

        // 생성
        $tag = new tag([
            'name' => $name
        ]);
        $tag->color = $color !== null ? $color : $tag->default_color;
        $tag->position = $lastTag !== null ? $lastTag->position + 1 : 0;

        $user->tags()->save($tag);

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/tags/{tag_id}",
     *      tags={"태그"},
     *      summary="태그 상세보기",
     *      description="태그 개별 상세보기",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          description="태그 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="태그 번호"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="태그 이름"
     *              ),
     *              @OA\Property(
     *                  property="position",
     *                  type="integer",
     *                  description="우선순위"
     *              ),
     *              @OA\Property(
     *                  property="color",
     *                  type="string",
     *                  description="hexColor"
     *              ),
     *              example={
     *                  "id": 1,
     *                  "name": "운동",
     *                  "position": 0,
     *                  "color": "#5ac7ca"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="태그가 존재하지 않음",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function show(Request $request, $v, $tag_id)
    {
        $tag = Tag::find($tag_id);
        if ($tag === null) {
            abort(403, __('aborts.do_not_exist_tag'));
        }

        return $tag->only(['id', 'name', 'position', 'color']);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/tags/{tag_id}",
     *      tags={"태그"},
     *      summary="태그 수정",
     *      description="기존 태그 수정",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          description="태그 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="(선택)이름",
     *                  example="공부"
     *              ),
     *              @OA\Property(
     *                  property="position",
     *                  type="integer",
     *                  description="(선택)우선순위",
     *                  example=3
     *              ),
     *              @OA\Property(
     *                  property="color",
     *                  type="string",
     *                  description="(선택)hexColor",
     *                  example="#000000"
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
     *          description="태그가 존재하지 않음",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function update(Request $request, $v, $tag_id)
    {
        $this->validate($request, [
            'name' => 'nullable|string',
            'position' => 'nullable|integer',
            'color' => [
                'nullable',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ]
        ], [
            '*' => __('validations.format')
        ]);

        // 사용자 정보
        $user = $request->get('user');

        $name = $request->input('name');
        $position = $request->input('position');
        $color = $request->input('color');

        $tag = Tag::where('user_id', $user->id)->find($tag_id);
        if ($tag === null) {
            abort(403, __('aborts.do_not_exist_tag'));
        }

        $tag->name = $name !== null ? $name : $tag->name;
        $tag->color = $color !== null ? $color : $tag->color;

        // 중요도가 변경된 경우
        if ($position !== null && $tag->position !== $position) {
            $tag->position = $tag->replacePosition($user, $position);
        }
        $tag->save();

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/tags/{tag_id}",
     *      tags={"태그"},
     *      summary="태그 삭제",
     *      description="태그 개별 삭제",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          description="태그 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
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
     *      )
     * )
     */
    public function destroy(Request $request, $v, $tag_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $tag = Tag::where('user_id', $user->id)->find($tag_id);
        if ($tag !== null) {
            $tag->replacePosition($user, $tag->position, null, true);
            $tag->delete();
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
