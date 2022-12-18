<?php

namespace Tests\Feature;

use App\Models\{Routine, Tag};
use Carbon\Carbon;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    protected static $user = null;
    protected static $routine = null;

    public function setUp(): void
    {
        parent::setUp();

        // 사용자 로그인
        self::$user = $this->getUser();
    }

    /**
     * 루틴 생성 테스트
     *
     * @return void
     */
    public function test_create_routine()
    {
        $tag = Tag::where('user_id', self::$user['user_id'])->latest('position')->first();
        $position = $tag !== null ? $tag->position++ : 0;
        $tag = Tag::factory()->create([
            'user_id' => self::$user['user_id'],
            'position' => $position
        ]);
        (new Tag())->resetPosition(self::$user['user_id']);

        $this->post('api/v1/routines', [
            'contents' => '매주 월요일, 수요일마다 반복되는 일정입니다.',
            'tag_id' => $tag->id,
            'type' => 'week',
            'schedules' => [
                'dates' => [],
                'days_of_week' => ['mon', 'wed']
            ],
            'start_date' => Carbon::now()->addDay()->toDateString(),
            'end_date' => Carbon::now()->addMonth()->toDateString()
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        self::$routine = Routine::firstWhere('user_id', self::$user['user_id']);

        self::assertSame(self::$routine->contents, '매주 월요일, 수요일마다 반복되는 일정입니다.');
        self::assertSame(self::$routine->start_date, Carbon::now()->addDay()->toDateString());
        self::assertSame(self::$routine->end_date, Carbon::now()->addMonth()->toDateString());
        self::assertSame(self::$routine->type, 'week');
    }

    /**
     * 루틴 수정 테스트
     *
     * @return void
     */
    public function test_update_routine()
    {
        $path = 'api/v1/routines/' . self::$routine->id;
        $this->put($path, [
            'contents' => '5, 10일 마다 반복되는 일정입니다.',
            'start_date' => Carbon::now()->addDays(3)->toDateString(),
            'type' => 'month',
            'schedules' => [
                'dates' => [5, 10],
                'days_of_week' => []
            ],
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        self::$routine->refresh();

        self::assertSame(self::$routine->contents, '5, 10일 마다 반복되는 일정입니다.');
        self::assertSame(self::$routine->start_date, Carbon::now()->addDays(3)->toDateString());
        self::assertSame(self::$routine->type, 'month');
    }

    /**
     * 루틴 개별 조회 테스트
     *
     * @return void
     */
    public function test_get_routine()
    {
        $path = 'api/v1/routines/' . self::$routine->id;
        $this->get($path, self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'id', 'contents', 'user_id', 'type', 'schedules' => ['dates', 'days_of_week'],
                'start_date', 'end_date', 'tag'
            ]);
    }

    /**
     * 루틴 리스트 조회 테스트
     *
     * @return void
     */
    public function test_get_list_of_routines()
    {
        $path = 'api/v1/routines';
        $this->get($path, self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'routines' => [
                    '*' => [
                        'id', 'contents', 'user_id', 'type', 'schedules' => ['dates', 'days_of_week'],
                        'start_date', 'end_date', 'tag'
                    ]
                ]
            ]);
    }

    /**
     * 일정 삭제 테스트
     *
     * @return void
     */
    public function test_delete_routine()
    {
        $path = 'api/v1/routines/' . self::$routine->id;
        $this->delete($path, [], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        self::$routine->refresh();

        self::assertNotNull(self::$routine->deleted_at);
        self::assertSoftDeleted(self::$routine);
    }
}
