<?php

namespace Tests\Feature;

use App\Models\{Tag, Task};
use Carbon\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    protected static $user = null;

    public function setUp(): void
    {
        parent::setUp();

        // 사용자 로그인
        self::$user = $this->getUser();
    }

    /**
     * 일정 생성 테스트
     *
     * @return void
     */
    public function test_create_task()
    {
        $tag = Tag::where('user_id', self::$user['user_id'])->latest('position')->first();
        $position = $tag !== null ? $tag->position++ : 0;
        $tag = Tag::factory()->create([
            'user_id' => self::$user['user_id'],
            'position' => $position
        ]);
        (new Tag())->resetPosition(self::$user['user_id']);

        $this->post('api/v1/tasks', [
            'contents' => '오늘의 일정입니다.',
            'date' => Carbon::now()->toDateString(),
            'tag_id' => $tag->id
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $task = Task::firstWhere('user_id', self::$user['user_id']);

        self::assertSame($task->contents, '오늘의 일정입니다.');
        self::assertSame($task->date, Carbon::now()->toDateString());
    }

    /**
     * 일정 수정 테스트
     *
     * @return void
     */
    public function test_update_task()
    {
        $task = Task::firstWhere('user_id', self::$user['user_id']);

        $path = 'api/v1/tasks/' . $task->id;
        $this->put($path, [
            'contents' => '내일의 일정입니다.',
            'date' => Carbon::now()->addDay()->toDateString(),
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $task->refresh();

        self::assertSame($task->contents, '내일의 일정입니다.');
        self::assertSame($task->date, Carbon::now()->addDay()->toDateString());
    }

    /**
     * 일정 완료로 변경 테스트
     *
     * @return void
     */
    public function test_update_task_done()
    {
        $task = Task::firstWhere('user_id', self::$user['user_id']);

        $path = 'api/v1/tasks/' . $task->id . '/done';
        $this->put($path, [
            'done' => true
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $task->refresh();

        self::assertSame($task->done, true);
    }

    /**
     * 일정 개별 조회 테스트
     *
     * @return void
     */
    public function test_get_task()
    {
        $task = Task::firstWhere('user_id', self::$user['user_id']);

        $path = 'api/v1/tasks/' . $task->id;
        $this->get($path, self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'id', 'contents', 'date', 'done', 'dead_line', 'complete_time', 'tag'
            ]);
    }

    /**
     * 일정 삭제 테스트
     *
     * @return void
     */
    public function test_delete_task()
    {
        $task = Task::firstWhere('user_id', self::$user['user_id']);

        $path = 'api/v1/tasks/' . $task->id;
        $this->delete($path, [], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $task->refresh();

        self::assertNotNull($task->deleted_at);
        self::assertSoftDeleted($task);
    }
}
