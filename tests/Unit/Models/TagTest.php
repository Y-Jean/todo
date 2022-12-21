<?php

namespace Tests\Unit\Models;

use App\Models\{Routine, Tag, Task, User};
use Tests\TestCase;

class TagTest extends TestCase
{
    /**
     * Tag 생성 테스트
     *
     * @return void
     */
    public function test_create_tag()
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas('tags', [
            'name' => $tag->name, 'id' => $tag->id
        ]);
        $this->assertModelExists($tag);
    }

    /**
     * user와 belongsTo 관계 테스트
     *
     * @return void
     */
    public function test_belongs_to_user()
    {
        $tag = Tag::factory()->create();

        $this->assertInstanceOf(User::class, $tag->user);
    }

    /**
     * Task와 hasMany 관계 테스트
     *
     * @return void
     */
    public function test_has_many_tasks()
    {
        $tag = Tag::factory()->create();

        Task::factory()
            ->count(2)
            ->create([
                'user_id' => $tag->user->id,
                'tag_id' => $tag->id
            ]);

        $this->assertSame($tag->tasks->count(), 2);
    }

    /**
     * Routine과 hasMany 관계 테스트
     *
     * @return void
     */
    public function test_has_many_routines()
    {
        $tag = Tag::factory()->create();

        Routine::factory()
            ->count(2)
            ->create([
                'user_id' => $tag->user->id,
                'tag_id' => $tag->id
            ]);

        $this->assertSame($tag->routines->count(), 2);
    }
}
