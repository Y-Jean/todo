<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TagToTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * public @method store(Request $request) :: 할 일 저장
 */
class TaskController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/tasks",
     *      tags={"일정"},
     *      summary="일정 생성",
     *      description="새로운 일정 생성",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="contents",
     *                  type="string",
     *                  description="(필수)일정",
     *                  example="병원가기"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="(필수)날짜",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="tag_ids",
     *                  type="array",
     *                  description="(선택)태그 번호(0~3개)",
     *                  @OA\Items(
     *                      type="integer",
     *                      example="1"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="dead_line",
     *                  type="string",
     *                  description="(선택)완료 예정 시간",
     *                  example="2022-11-23 16:30:00"
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
     *          description="기타 오류",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'contents' => 'required|string',
            'date' => 'required|date|date_format:Y-m-d',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|min:0',
            'dead_line' => 'nullable|date_format:Y-m-d H:i:s'
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 할일 내용
        $contents = $request->input('contents');
        // 날짜
        $date = $request->input('date');
        // 태그 아이디
        $tag_ids = collect($request->input('tag_ids'))->unique();
        $deadLine = $request->input('dead_line') !== null ? Carbon::createFromFormat('Y-m-d H:i:s', $request->input('dead_line')) : null;

        // 내용이 비었을 경우
        if ($contents === '') {
            abort(403, __('aborts.do_not_exist_contents'));
        }

        $tags = null;
        if ($tag_ids !== null) {
            $tags = Tag::whereIn('id', $tag_ids->toArray())->where('user_id', $user->id)->get();

            // 태그가 세 개 이상일 경우
            if ($tags !== null && $tags->count() > 3) {
                abort(403, __('aborts.too_much_tags'));
            }
        }

        $task = new Task();
        $task->user_id = $user->id;
        $task->contents = $contents;
        $task->date = $date;
        $task->dead_line = $deadLine;
        $task->save();

        if ($tags !== null) {
            $tags->each(function ($item) use ($user, $task) {
                $tag_to_task = new TagToTask();
                $tag_to_task->tag_id = $item->id;
                $tag_to_task->user_id = $user->id;
                $tag_to_task->task_id = $task->id;

                $tag_to_task->save();
            });
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/tasks/{task_id}",
     *      tags={"일정"},
     *      summary="일정 수정",
     *      description="기존 일정 수정",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="contents",
     *                  type="string",
     *                  description="(필수)일정",
     *                  example="병원가기"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="(필수)날짜",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="tag_ids",
     *                  type="array",
     *                  description="(선택)태그 번호(0~3개)",
     *                  @OA\Items(
     *                      type="integer",
     *                      example="1"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="dead_line",
     *                  type="string",
     *                  description="(선택)완료 예정 시간",
     *                  example="2022-11-23 16:30:00"
     *              ),
     *              @OA\Property(
     *                  property="done",
     *                  type="boolean",
     *                  description="(선택)완료 여부",
     *                  example=false
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
     *          description="기타 오류",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function update(Request $request, $v, $task_id)
    {
        $this->validate($request, [
            'contents' => 'required|string',
            'date' => 'required|date|date_format:Y-m-d',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|min:0',
            'dead_line' => 'nullable|date_format:Y-m-d H:i:s',
            'done' => 'nullable|boolean'
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 할일 내용
        $contents = $request->input('contents');
        // 날짜
        $date = $request->input('date');
        // 태그 아이디
        $tag_ids = collect($request->input('tag_ids'))->unique();
        $deadLine = $request->input('dead_line') !== null ? Carbon::createFromFormat('Y-m-d H:i:s', $request->input('dead_line')) : null;
        $done = $request->input('done');

        // 내용이 비었을 경우
        if ($contents === '') {
            abort(403, __('aborts.do_not_exist_contents'));
        }

        $tags = collect();
        if ($tag_ids !== null) {
            $tags = Tag::whereIn('id', $tag_ids->toArray())->where('user_id', $user->id)->get();

            // 태그가 세 개 이상일 경우
            if ($tags !== null && $tags->count() > 3) {
                abort(403, __('aborts.too_much_tags'));
            }
        }

        $task = Task::with('tagToTasks')
                    ->where('user_id', $user->id)
                    ->whereId($task_id)
                    ->first();

        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        $task->contents = $contents;
        $task->date = $date;
        $task->dead_line = $deadLine;
        if ($done !== null) {
            $done = filter_var($done, FILTER_VALIDATE_BOOLEAN);
            // 일정 미완료에서 완료로 바뀌는 경우 완료 시간 등록
            if ($task->done !== $done && $done) {
                $task->complete_time = Carbon::now();
            } elseif ($task->done !== $done && !$done) {
                $task->complete_time = null;
            }

            $task->done = $done;
        }
        $task->save();

        // 수정에 포함되지 않은 태그 연결 삭제
        TagToTask::where('task_id', $task->id)
                    ->whereNotIn('tag_id', $tags->pluck('id')->toArray())
                    ->delete();

        // 새로 추가된 태그 연결
        if ($tags->isNotEmpty()) {
            $tags->whereNotIn('id', $task->tagToTasks->pluck('tag_id'))
                ->each(function ($item) use ($user, $task) {
                // 태그가 이미 연결된 경우
                if ($task->tagToTasks->where('tag_id', $item->id)->first() !== null) {
                    return ;
                }

                $tag_to_task = new TagToTask();
                $tag_to_task->tag_id = $item->id;
                $tag_to_task->user_id = $user->id;
                $tag_to_task->task_id = $task->id;

                $tag_to_task->save();
            });
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/tasks/{task_id}/done",
     *      tags={"일정"},
     *      summary="일정 완료 등록",
     *      description="기존 일정 완료/미완료 표기 변경",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="done",
     *                  type="boolean",
     *                  description="(필수)완료 여부",
     *                  example=false
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
     *          description="기타 오류",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function updateDone(Request $request, $v, $task_id)
    {
        $this->validate($request, [
            'done' => 'required|boolean'
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        $done = filter_var($request->input('done'), FILTER_VALIDATE_BOOLEAN);

        $task = Task::where('user_id', $user->id)
                    ->whereId($task_id)
                    ->first();

        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        // 일정 미완료에서 완료로 바뀌는 경우 완료 시간 등록
        if ($task->done !== $done && $done) {
            $task->complete_time = Carbon::now();
        } elseif ($task->done !== $done && !$done) {
            $task->complete_time = null;
        }
        $task->done = $done;
        $task->save();

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/tasks/{task_id}",
     *      tags={"일정"},
     *      summary="일정 상세보기",
     *      description="일정 개별 상세보기",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          description="일정 번호",
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
     *                  description="할일 번호"
     *              ),
     *              @OA\Property(
     *                  property="contents",
     *                  type="string",
     *                  description="할일 내용"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="날짜"
     *              ),
     *              @OA\Property(
     *                  property="done",
     *                  type="boolean",
     *                  description="완료 여부"
     *              ),
     *              @OA\Property(
     *                  property="dead_line",
     *                  type="string",
     *                  description="기한"
     *              ),
     *              @OA\Property(
     *                  property="complete_time",
     *                  type="string",
     *                  description="완료시간"
     *              ),
     *              @OA\Property(
     *                  property="open_at",
     *                  type="string",
     *                  description="등록 시간"
     *              ),
     *              @OA\Property(
     *                  property="reserved_at",
     *                  type="string",
     *                  description="예약 시간"
     *              ),
     *              @OA\Property(
     *                  property="created_at",
     *                  type="string",
     *                  description="생성일"
     *              ),
     *              @OA\Property(
     *                  property="tags",
     *                  type="array",
     *                  description="태그",
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
     *                          description="태그 이름"
     *                      ),
     *                      @OA\Property(
     *                          property="position",
     *                          type="integer",
     *                          description="우선순위"
     *                      ),
     *                      @OA\Property(
     *                          property="color",
     *                          type="string",
     *                          description="색깔 hexColor"
     *                      ),
     *                  )
     *              ),
     *              example={
     *                  "id": 1,
     *                  "contents": "오늘의 할일",
     *                  "title": "test, 예약된 공지",
     *                  "date": "2022-11-23",
     *                  "done": false,
     *                  "dead_line": "2022-11-25T01:00:00.000000Z",
     *                  "complete_time": null,
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
     *                          "position": 3,
     *                          "color": "#111111"
     *                      }
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="일정이 존재하지 않음",
     *          @OA\JsonContent(ref="#/components/schemas/ResponseAbort")
     *      )
     * )
     */
    public function show(Request $request, $v, $task_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $task = Task::with('tagToTasks.tag')->where('user_id', $user->id)->find($task_id);

        // 조회하려는 일정이 존재하지 않는 경우
        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        $task->tags = [];
        if ($task->tagToTasks->isNotEmpty()) {
            $task->tags = $task->tagToTasks->map(function ($item) {
                // 태그가 없는 경우
                if ($item->tag === null) {
                    return ;
                }

                return $item->tag->only(['id', 'name', 'position', 'color']);
            })->sortBy('position')->filter();
        }

        return $task->only(['id', 'contents', 'date', 'done', 'dead_line', 'complete_time', 'tags']);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/tasks/{task_id}",
     *      tags={"일정"},
     *      summary="일정 삭제",
     *      description="일정 개별 삭제",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          description="일정 번호",
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
    public function destroy(Request $request, $v, $task_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $task = Task::where('user_id', $user->id)->find($task_id);
        if ($task !== null) {
            $task->delete();
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
