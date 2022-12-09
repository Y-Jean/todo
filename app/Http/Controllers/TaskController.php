<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * public @method store(Request $request) :: 할 일 저장
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/tags?period={period}&date={date}",
     *      tags={"알정"},
     *      summary="단위기간별 일정 조회",
     *      description="일간, 주간 월간 단위기간별 일정 조회",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="period",
     *          in="path",
     *          description="단위기간",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              enum={"agent_name", "os", "ip", "version"},
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="path",
     *          description="조회 기준일(기준일이 포함된 일/주/월 조회)",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="tag_ids",
     *          in="path",
     *          description="태그 번호",
     *          required=false,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items()
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="start_date",
     *                  type="string",
     *                  description="단위 기간 시작일"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="string",
     *                  description="단위 기간 종료일"
     *              ),
     *              @OA\Property(
     *                  property="tasks",
     *                  type="array",
     *                  description="일정 리스트",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer",
     *                          description="일정 번호"
     *                      ),
     *                      @OA\Property(
     *                          property="contents",
     *                          type="string",
     *                          description="일정 내용"
     *                      ),
     *                      @OA\Property(
     *                          property="done",
     *                          type="boolean",
     *                          description="완료 여부"
     *                      ),
     *                      @OA\Property(
     *                          property="dead_line",
     *                          type="string",
     *                          description="기한"
     *                      ),
     *                      @OA\Property(
     *                          property="complete_time",
     *                          type="string",
     *                          description="완료 시간"
     *                      ),
     *                      @OA\Property(
     *                          property="date",
     *                          type="string",
     *                          description="날짜"
     *                      ),
     *                      @OA\Property(
     *                          property="tag_id",
     *                          type="string",
     *                          description="태그 번호"
     *                      ),
     *                      @OA\Property(
     *                          property="tag_name",
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
     *                          description="hexColor"
     *                      )
     *                  )
     *              ),
     *              example={
     *                  "tags": {
     *                      {
     *                          "id": 1,
     *                          "contents": "오늘의 할일",
     *                          "done": false,
     *                          "dead_line": "2022-12-10T01:00:00.000000Z",
     *                          "complete_time": null,
     *                          "date": "2022-12-05",
     *                          "tag_id": 3,
     *                          "tag_name": "운동",
     *                          "position": 1,
     *                          "color": "#355921"
     *                      },
     *                      {
     *                          "id": 7,
     *                          "contents": "12월 8일의 할일",
     *                          "done": true,
     *                          "dead_line": null,
     *                          "complete_time": null,
     *                          "date": "2022-12-08",
     *                          "tag_id": 2,
     *                          "tag_name": "어학",
     *                          "position": 0,
     *                          "color": "#70b0eb"
     *                      }
     *                  }
     *              }
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'period' => 'required|string|in:day,week,month',
            'date' => 'required|date_format:Y-m-d',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|min:0'
        ], [
            '*' => __('validations.format')
        ]);

        // 사용자 정보
        $user = $request->get('user');

        $period = $request->get('period');
        $tag_ids = collect($request->input('tag_ids'))->unique();
        $date = Carbon::parse($request->input('date'));
        $startDate = $date->toDateString();
        $endDate = $date->toDateString();

        if ($period === 'week') {
            $startDate = $date->copy()->startOfWeek()->toDateString();
            $endDate = $date->copy()->endOfWeek()->toDateString();
        } elseif ($period === 'month') {
            $startDate = $date->copy()->startOfMonth()->toDateString();
            $endDate = $date->copy()->endOfMonth()->toDateString();
        }

        $tasks = Task::leftjoin('tags', function ($join){
                        $join->on('tags.id', '=', 'tasks.tag_id')
                            ->whereNull('tags.deleted_at');
                    })
                    ->whereBetween('tasks.date', [$startDate, $endDate])
                    ->where('tasks.user_id', $user->id)
                    ->when($tag_ids->isNotEmpty(), function ($query) use ($tag_ids) {
                        $query->whereIn('tasks.tag_id', $tag_ids);
                    })
                    ->orderBy('tasks.date')
                    ->orderBy('tags.position')
                    ->select([
                        'tasks.id', 'tasks.contents', 'tasks.done', 'tasks.dead_line', 'tasks.complete_time', 'tasks.date', 'tasks.tag_id',
                        'tags.name as tag_name', 'tags.position', 'tags.color'
                    ])
                    ->get();

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'tasks' => $tasks
        ]);
    }

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
     *                  property="tag_id",
     *                  type="integer",
     *                  description="(선택)태그 번호",
     *                  example=1
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
            'tag_id' => 'integer|min:0',
            'dead_line' => 'nullable|date_format:Y-m-d H:i:s'
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 일정 내용
        $contents = $request->input('contents');
        // 날짜
        $date = $request->input('date');
        // 태그 아이디
        $tag_id = $request->input('tag_id');
        $deadLine = $request->input('dead_line') !== null ? Carbon::createFromFormat('Y-m-d H:i:s', $request->input('dead_line')) : null;

        // 내용이 비었을 경우
        if ($contents === '') {
            abort(403, __('aborts.do_not_exist_contents'));
        }

        $tag = Tag::where('user_id', $user->id)->find($tag_id);

        $task = new Task();
        $task->user_id = $user->id;
        $task->contents = $contents;
        $task->date = $date;
        $task->dead_line = $deadLine;
        $task->tag_id = $tag !== null ? $tag->id : null;
        $task->save();

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
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          description="일정 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
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
     *                  property="tag_id",
     *                  type="integer",
     *                  description="(선택)태그 번호",
     *                  example=1
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
            'tag_id' => 'integer|min:0',
            'dead_line' => 'nullable|date_format:Y-m-d H:i:s',
            'done' => 'nullable|boolean'
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 일정 내용
        $contents = $request->input('contents');
        // 날짜
        $date = $request->input('date');
        // 태그 아이디
        $tag_id = $request->input('tag_id');
        $deadLine = $request->input('dead_line') !== null ? Carbon::createFromFormat('Y-m-d H:i:s', $request->input('dead_line')) : null;
        $done = $request->input('done');

        // 내용이 비었을 경우
        if ($contents === '') {
            abort(403, __('aborts.do_not_exist_contents'));
        }

        $task = Task::where('user_id', $user->id)->find($task_id);

        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        $tag = Tag::where('user_id', $user->id)->find($tag_id);

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
        $task->tag_id = $tag !== null ? $tag->id : null;
        $task->save();

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
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          description="일정 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
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
     *                  description="일정 번호"
     *              ),
     *              @OA\Property(
     *                  property="contents",
     *                  type="string",
     *                  description="일정 내용"
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
     *                  property="tags",
     *                  type="object",
     *                  description="태그",
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="태그 번호"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="태그 이름"
     *                  ),
     *                  @OA\Property(
     *                      property="position",
     *                      type="integer",
     *                      description="우선순위"
     *                  ),
     *                  @OA\Property(
     *                      property="color",
     *                      type="string",
     *                      description="색깔 hexColor"
     *                  )
     *              ),
     *              example={
     *                  "id": 1,
     *                  "contents": "오늘의 일정",
     *                  "date": "2022-11-23",
     *                  "done": false,
     *                  "dead_line": "2022-11-25T01:00:00.000000Z",
     *                  "complete_time": null,
     *                  "tags": {
     *                      "id": 1,
     *                      "name": "운동",
     *                      "position": 0,
     *                      "color": "#5ac7ca"
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

        $task = Task::with([
                        'tag' => function ($query) {
                            $query->select(['id', 'name', 'position', 'color']);
                        }
                    ])
                    ->where('user_id', $user->id)
                    ->find($task_id);

        // 조회하려는 일정이 존재하지 않는 경우
        if ($task === null) {
            abort(403, __('aborts.do_not_exist_task'));
        }

        return $task->only(['id', 'contents', 'date', 'done', 'dead_line', 'complete_time', 'tag']);
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
