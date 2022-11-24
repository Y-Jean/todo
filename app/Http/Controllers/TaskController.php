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
     *      path="/api/v1/task",
     *      tags={"일정"},
     *      summary="일정 추가",
     *      description="일정 추가",
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
     *                  description="(필수)완료 예정 시간",
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

    public function show(Request $request, $v, $task_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $task = Task::with('tagToTasks.tag')->where('user_id', $user->id)->find($task_id);

        // 조회하려는 일정이 존재하지 않는 경우
        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        $task->tag = [];
        if ($task->tagToTasks->isNotEmpty()) {
            $task->tag = $task->tagToTasks->map(function ($item) {
                // 태그가 없는 경우
                if ($item->tag === null) {
                    return ;
                }

                return $item->tag->only(['id', 'name', 'position', 'color']);
            })->sortBy('position')->filter();
        }

        return $task->only(['id', 'contents', 'date', 'done', 'dead_line', 'complete_time', 'tag']);
    }

    public function delete(Request $request, $v, $task_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        Task::where('user_id', $user->id)->delete($task_id);

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
