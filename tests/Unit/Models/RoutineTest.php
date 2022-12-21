<?php

namespace Tests\Unit\Models;

use App\Models\{Routine, Tag, User};
use Tests\TestCase;

class RoutineTest extends TestCase
{
    /**
     * Routine 생성 테스트
     *
     * @return void
     */
    public function test_create_routine()
    {
        $routine = Routine::factory()->create();

        $this->assertDatabaseHas('routines', [
            'contents' => $routine->contents, 'id' => $routine->id
        ]);
        $this->assertModelExists($routine);
    }

    /**
     * user와 belongsTo 관계 테스트
     *
     * @return void
     */
    public function test_belongs_to_user()
    {
        $routine = Routine::factory()->create();

        $this->assertInstanceOf(User::class, $routine->user);
    }

    /**
     * Tag와 belongsTo 관계 테스트
     *
     * @return void
     */
    public function test_belongs_to_tag()
    {
        $routine = Routine::factory()->create();

        $this->assertInstanceOf(Tag::class, $routine->tag);
    }
}
