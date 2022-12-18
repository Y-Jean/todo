<?php

namespace Tests\Unit\Models;

use App\Models\{Routine, Tag, Task, User};
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * User 생성 테스트
     *
     * @return void
     */
    public function test_create_user_with_option()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email' => $user->email, 'id' => $user->id
        ]);
        $this->assertModelExists($user);

        // created option in user observer
        $this->assertDatabaseHas('options', [
            'user_id' => $user->id
        ]);
        $this->assertModelExists($user->option);
    }

    /**
     * Tag와 hasMany 관계 테스트
     *
     * @return void
     */
    public function test_has_many_tags()
    {
        // $user = User::factory()
        //             ->has(Tag::factory()->count(3))
        //             ->create();

        $user = User::factory()->create();

        Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertSame($user->tags()->count(), 3);
    }

    /**
     * Task와 hasMany 관계 테스트
     *
     * @return void
     */
    public function test_has_many_tasks()
    {
        $user = User::factory()->has(Tag::factory())->create();

        Task::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
                'tag_id' => $user->tags()->first()->id
            ]);

        $this->assertSame($user->tasks()->count(), 3);
    }

    /**
     * Routine과 hasMany 관계 테스트
     *
     * @return void
     */
    public function test_has_many_routines()
    {
        $user = User::factory()->has(Tag::factory())->create();

        Routine::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
                'tag_id' => $user->tags()->first()->id
            ]);

        $this->assertSame($user->routines()->count(), 3);
    }
}
