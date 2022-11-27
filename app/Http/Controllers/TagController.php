<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 사용자 정보
        $user = $request->get('user');

        return $user->tags()
                    ->orderBy('position')
                    ->get(['id', 'name', 'position', 'color']);
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
            'name' => 'required|string',
            'position' => 'nullable|integer|min:0',
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
        $tag->position = $lastTag !== null ? $lastTag->position+1 : 0;

        $user->tags()->save($tag);

        return response()->json([
            'result' => 'success'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $v
     * @param  int  $tag_id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $v, $tag_id)
    {
        return Tag::find($tag_id)->only(['id', 'name', 'position', 'color']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $v
     * @param  int  $tag_id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $v, $tag_id)
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
            abort(403, __(''));
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
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $v
     * @param  int  $tag_id
     * @return \Illuminate\Http\Response
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
