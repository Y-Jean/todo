<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Routine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 사용자 정보
        $user = $request->get('user');

        $routines = Routine::where('user_id', $user->id)->get();

        return response()->json([
            'routines' => $routines
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        $tag = Tag::where('user_id', $user->id)->find($tag_id);

        if ($type === 'month' && (empty($schedules['dates']) || $schedules['dates'] === null)) {
            abort(403, __(111));
        }
        if ($type === 'week' && (empty($schedules['days_of_week']) || $schedules['days_of_week'] === null)) {
            abort(403, __(222));
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
