<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Routine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/routines",
     *      tags={"루틴"},
     *      summary="루틴 리스트 조회",
     *      description="루틴 리스트 조회",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Response(
     *          response="200",
     *          description="성공",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="routines",
     *                  type="array",
     *                  description="루틴 리스트",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer",
     *                          description="루틴 번호"
     *                      ),
     *                      @OA\Property(
     *                          property="contents",
     *                          type="string",
     *                          description="일정 내용"
     *                      ),
     *                      @OA\Property(
     *                          property="user_id",
     *                          type="integer",
     *                          description="사용자 번호"
     *                      ),
     *                      @OA\Property(
     *                          property="type",
     *                          type="string",
     *                          enum={"month", "week"},
     *                          description="월간(날짜기준)반복, 주간(요일기준)반복 구분"
     *                      ),
     *                      @OA\Property(
     *                          property="schedules",
     *                          type="object",
     *                          description="날짜, 요일 정보",
     *                          @OA\Property(
     *                              property="dates",
     *                              type="array",
     *                              description="날짜 목록",
     *                              @OA\Items(
     *                                  type="integer",
     *                              )
     *                          ),
     *                          @OA\Property(
     *                              property="days_of_week",
     *                              type="array",
     *                              description="요일 목록",
     *                              @OA\Items(
     *                                  type="string",
     *                                  enum={"mon", "tue", "wed", "thu", "fri", "sat", "sun"}
     *                              )
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="start_date",
     *                          type="string",
     *                          description="시작 일자"
     *                      ),
     *                      @OA\Property(
     *                          property="end_date",
     *                          type="string",
     *                          description="종료 일자"
     *                      ),
     *                      @OA\Property(
     *                          property="tags",
     *                          type="object",
     *                          description="태그",
     *                          @OA\Property(
     *                              property="id",
     *                              type="integer",
     *                              description="태그 번호"
     *                          ),
     *                          @OA\Property(
     *                              property="name",
     *                              type="string",
     *                              description="태그 이름"
     *                          ),
     *                          @OA\Property(
     *                              property="position",
     *                              type="integer",
     *                              description="우선순위"
     *                          ),
     *                          @OA\Property(
     *                              property="color",
     *                              type="string",
     *                              description="색깔 hexColor"
     *                          )
     *                      )
     *                  )
     *              ),
     *              example={
     *                  "routines": {
     *                      {
     *                          "id": 1,
     *                          "contents": "5일마다 할일",
     *                          "user_id": 11,
     *                          "type": "month",
     *                          "schedules": {
     *                              "dates": {
     *                                  5, 10, 15, 20, 25, 30
     *                              },
     *                              "days_of_week": {}
     *                          },
     *                          "tag_id": 3,
     *                          "start_date": "2022-12-09",
     *                          "end_date": "2022-12-20",
     *                          "tag": {
     *                              "id": 1,
     *                              "name": "dolorum",
     *                              "position": 0,
     *                              "color": "#5f9d13"
     *                          }
     *                      },
     *                      {
     *                          "id": 7,
     *                          "contents": "월, 화요일에 할 일",
     *                          "user_id": 11,
     *                          "type": "week",
     *                          "schedules": {
     *                              "dates": {},
     *                              "days_of_week": {"mon", "tue"}
     *                          },
     *                          "tag_id": 1,
     *                          "start_date": "2022-12-09",
     *                          "end_date": null,
     *                          "tag": {
     *                              "id": 1,
     *                              "name": "dolorum",
     *                              "position": 0,
     *                              "color": "#5f9d13"
     *                          }
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

        $routines = Routine::with('tag:id,name,position,color')
                            ->where('user_id', $user->id)
                            ->get();

        return response()->json([
            'routines' => $routines
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/routines",
     *      tags={"루틴"},
     *      summary="루틴 생성",
     *      description="새로운 루틴 생성",
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
     *                  property="start_date",
     *                  type="string",
     *                  description="(필수)시작 일자(루틴을 시작할 일자)",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="string",
     *                  description="(선택)종료 일자",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="tag_id",
     *                  type="integer",
     *                  description="(선택)태그 번호",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  enum={"month", "week"},
     *                  description="(필수)월간(날짜기준)반복, 주간(요일기준)반복 구분",
     *                  example="week"
     *              ),
     *              @OA\Property(
     *                  property="schedules",
     *                  type="object",
     *                  description="(필수)날짜, 요일 정보",
     *                  @OA\Property(
     *                      property="dates",
     *                      type="array",
     *                      description="(선택)날짜 목록",
     *                      @OA\Items(
     *                          type="integer",
     *                          example=1
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="days_of_week",
     *                      type="array",
     *                      description="(선택)요일 목록",
     *                      @OA\Items(
     *                          type="string",
     *                          enum={"mon", "tue", "wed", "thu", "fri", "sat", "sun"},
     *                          example="mon"
     *                      )
     *                  ),
     *              ),
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
            'tag_id' => 'integer|min:0',
            'start_date' => 'required|date|date_format:Y-m-d|after:now',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'type' => 'required|string|in:month,week',
            'schedules' => 'required|array',
            'schedules.dates' => 'required_if:schedules.days_of_week,null|array',
            'schedules.dates.*' => 'nullable|integer|min:1|max:31',
            'schedules.days_of_week' => 'required_if:schedules.dates,null|array',
            'schedules.days_of_week.*' => 'nullable|string|in:mon,tue,wed,thu,fri,sat,sun',
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 일정 내용
        $contents = $request->input('contents');
        // 태그 아이디
        $tag_id = $request->input('tag_id');
        // 날짜
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type');
        $schedules = $request->input('schedules');

        $tag = null;
        if ($tag_id !== null) {
            $tag = Tag::where('user_id', $user->id)->find($tag_id);
        }

        if ($type === 'month' && (empty($schedules['dates']) || $schedules['dates'] === null)) {
            abort(403, __('aborts.enter_dates'));
        }
        if ($type === 'week' && (empty($schedules['days_of_week']) || $schedules['days_of_week'] === null)) {
            abort(403, __('aborts.enter_days_of_week'));
        }

        $routine = new Routine();
        $routine->user_id = $user->id;
        $routine->contents = $contents;
        $routine->start_date = $startDate;
        $routine->end_date = $endDate;
        $routine->type = $type;
        $routine->schedules = [
            'dates' => $schedules['dates'] !== null && $type === 'month' ? $schedules['dates'] : [],
            'days_of_week' => $schedules['days_of_week'] !== null && $type === 'week' ? $schedules['days_of_week'] : [],
        ];
        $routine->tag_id = $tag !== null ? $tag->id : null;
        $routine->save();

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/routines/{routine_id}",
     *      tags={"루틴"},
     *      summary="루틴 상세보기",
     *      description="루틴 개별 상세보기",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="routine_id",
     *          in="path",
     *          description="루틴 번호",
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
     *                  description="루틴 번호"
     *              ),
     *              @OA\Property(
     *                  property="contents",
     *                  type="string",
     *                  description="루틴 내용"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="integer",
     *                  description="사용자 번호"
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="string",
     *                  description="시작 일자"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="string",
     *                  description="종료 일자"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  enum={"month", "week"},
     *                 description="월간(날짜기준)반복, 주간(요일기준)반복 구분"
     *              ),
     *              @OA\Property(
     *                  property="schedules",
     *                  type="object",
     *                  description="날짜, 요일 정보",
     *                  @OA\Property(
     *                      property="dates",
     *                      type="array",
     *                      description="날짜 목록",
     *                      @OA\Items(
     *                          type="integer",
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="days_of_week",
     *                      type="array",
     *                      description="요일 목록",
     *                      @OA\Items(
     *                          type="string",
     *                          enum={"mon", "tue", "wed", "thu", "fri", "sat", "sun"}
     *                      )
     *                  )
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
    public function show(Request $request, $v, $routine_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $routine = Routine::with('tag:id,name,position,color')
                        ->where('user_id', $user->id)
                        ->find($routine_id);

        // 조회하려는 루틴이 존재하지 않는 경우
        if ($routine === null) {
            abort(403, __('aborts.do_not_exist_routine'));
        }

        return $routine->only(['id', 'contents', 'user_id', 'type', 'schedules', 'start_date', 'end_date', 'tag']);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/routines/{routine_id}",
     *      tags={"루틴"},
     *      summary="루틴 수정",
     *      description="기존 루틴 수정",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="routine_id",
     *          in="path",
     *          description="루틴 번호",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="start_date",
     *                  type="string",
     *                  description="(필수)시작 일자(루틴을 시작할 일자)",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="string",
     *                  description="(선택)종료 일자",
     *                  example="2022-11-23"
     *              ),
     *              @OA\Property(
     *                  property="tag_id",
     *                  type="integer",
     *                  description="(선택)태그 번호",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  enum={"month", "week"},
     *                  description="(필수)월간(날짜기준)반복, 주간(요일기준)반복 구분",
     *                  example="week"
     *              ),
     *              @OA\Property(
     *                  property="schedules",
     *                  type="object",
     *                  description="(필수)날짜, 요일 정보",
     *                  @OA\Property(
     *                      property="dates",
     *                      type="array",
     *                      description="(선택)날짜 목록",
     *                      @OA\Items(
     *                          type="integer",
     *                          example=1
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="days_of_week",
     *                      type="array",
     *                      description="(선택)요일 목록",
     *                      @OA\Items(
     *                          type="string",
     *                          enum={"mon", "tue", "wed", "thu", "fri", "sat", "sun"},
     *                          example="mon"
     *                      )
     *                  ),
     *              ),
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
    public function update(Request $request, $v, $routine_id)
    {
        $this->validate($request, [
            'tag_id' => 'integer|min:0',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date|after:now',
            'type' => 'required|string|in:month,week',
            'schedules' => 'required|array',
            'schedules.dates' => 'required_if:schedules.days_of_week,null|array',
            'schedules.dates.*' => 'nullable|integer|min:1|max:31',
            'schedules.days_of_week' => 'required_if:schedules.dates,null|array',
            'schedules.days_of_week.*' => 'nullable|string|in:mon,tue,wed,thu,fri,sat,sun',
        ], [
            '*' => __('validations.format'),
        ]);

        // 사용자 정보
        $user = $request->get('user');

        // 태그 아이디
        $tag_id = $request->input('tag_id');
        // 날짜
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type');
        $schedules = $request->input('schedules');

        $routine = Routine::with('tag')->where('user_id', $user->id)->find($routine_id);
        if ($routine === null) {
            abort(403, __('aborts.do_not_exist_routine'));
        }

        // 시작일 이후면 시작일 변경 불가
        if ($routine->start_date !== $startDate && $routine->start_date <= Carbon::now()->toDateString()) {
            abort(403, __('aborts.already_start_routine'));
        }
        // 종료일 이후면 루틴 변경 불가
        if ($routine->end_date !== null && $routine->end_date <= Carbon::now()->toDateString()) {
            abort(403, __('aborts.ended_routine'));
        }

        if ($tag_id === null) {
            $routine->tag_id = null;
        } else if ($routine->tag_id !== $tag_id) {
            $tag = Tag::where('user_id', $user->id)->find($tag_id);
            if ($tag !== null) {
                $routine->tag_id = $tag->id;
            }
        }

        if ($type === 'month' && (empty($schedules['dates']) || $schedules['dates'] === null)) {
            abort(403, __('aborts.enter_dates'));
        }
        if ($type === 'week' && (empty($schedules['days_of_week']) || $schedules['days_of_week'] === null)) {
            abort(403, __('aborts.enter_days_of_week'));
        }

        $routine->start_date = $startDate;
        $routine->end_date = $endDate;
        $routine->type = $type;
        $routine->schedules = [
            'dates' => $schedules['dates'] !== null && $type === 'month' ? $schedules['dates'] : [],
            'days_of_week' => $schedules['days_of_week'] !== null && $type === 'week' ? $schedules['days_of_week'] : [],
        ];
        $routine->tag_id = $tag !== null ? $tag->id : null;
        $routine->save();

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/routines/{routine_id}",
     *      tags={"루틴"},
     *      summary="루틴 삭제",
     *      description="루틴 개별 삭제",
     *      security={
     *          {"auth":{}}
     *      },
     *      @OA\Parameter(
     *          name="routine_id",
     *          in="path",
     *          description="루틴 번호",
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
    public function destroy(Request $request, $v, $routine_id)
    {
        // 사용자 정보
        $user = $request->get('user');

        $routine = Routine::where('user_id', $user->id)->find($routine_id);
        if ($routine !== null) {
            $routine->delete();
        }

        return response()->json([
            'result' => 'success'
        ], 201);
    }
}
