<?php

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    protected static $user = null;

    public function setUp(): void
    {
        parent::setUp();

        // 사용자 로그인
        self::$user = $this->getUser();
    }

    /**
     * 태그 생성 테스트
     *
     * @return void
     */
    public function test_create_tag()
    {
        $hex = fake()->hexColor();
        $this->post('api/v1/tags', [
            'name' => '운동',
            'color' => $hex,
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $tag = Tag::firstWhere('user_id', self::$user['user_id']);

        self::assertSame($tag->name, '운동');
        self::assertSame($tag->color, $hex);
        self::assertSame($tag->position, 0);
    }

    /**
     * 태그 수정 테스트
     *
     * @return void
     */
    public function test_update_tag()
    {
        $tag = Tag::firstWhere('user_id', self::$user['user_id']);
        $hex = fake()->hexColor();

        $path = 'api/v1/tags/' . $tag->id;
        $this->put($path, [
            'name' => '학습',
            'color' => $hex,
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $tag->refresh();

        self::assertSame($tag->name, '학습');
        self::assertSame($tag->color, $hex);
        self::assertSame($tag->position, 0);
    }

    /**
     * 태그 개별 조회 테스트
     *
     * @return void
     */
    public function test_get_tag()
    {
        $position = Tag::where('user_id', self::$user['user_id'])->latest('position')->first()->position;
        $tag = Tag::factory()->create([
            'user_id' => self::$user['user_id'],
            'position' => $position++
        ]);
        (new Tag)->resetPosition(self::$user['user_id']);

        $path = 'api/v1/tags/' . $tag->id;
        $this->get($path, self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'position', 'color'
            ]);
    }

    /**
     * 태그 리스트 조회 테스트
     *
     * @return void
     */
    public function test_get_list_of_tags()
    {
        $this->get('api/v1/tags', self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'tags' => ['*' => ['id', 'name', 'position', 'color']]
            ]);
    }

    /**
     * 태그 삭제 테스트
     *
     * @return void
     */
    public function test_delete_tag()
    {
        $position = Tag::where('user_id', self::$user['user_id'])->latest('position')->first()->position;
        $tag = Tag::factory()->create([
            'user_id' => self::$user['user_id'],
            'position' => $position++
        ]);

        $path = 'api/v1/tags/' . $tag->id;
        $this->delete($path, [], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);
    }
}
