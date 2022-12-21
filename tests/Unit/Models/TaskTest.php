<?php

namespace Tests\Unit\Models;

use App\Models\{Tag, Task, User};
use Tests\TestCase;

class TaskTest extends TestCase
{
    /**
     * Task 생성 테스트
     *
     * @return void
     */
    public function test_create_routine()
    {
        $task = Task::factory()->create();

        $this->assertDatabaseHas('tasks', [
            'contents' => $task->contents, 'id' => $task->id
        ]);
        $this->assertModelExists($task);
    }

    /**
     * user와 belongsTo 관계 테스트
     *
     * @return void
     */
    public function test_belongs_to_user()
    {
        $task = Task::factory()->create();

        $this->assertInstanceOf(User::class, $task->user);
    }

    /**
     * Tag와 belongsTo 관계 테스트
     *
     * @return void
     */
    public function test_belongs_to_tag()
    {
        $task = Task::factory()->create();

        $this->assertInstanceOf(Tag::class, $task->tag);
    }
}
