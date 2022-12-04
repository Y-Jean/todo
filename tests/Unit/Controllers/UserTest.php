<?php

namespace Tests\Unit\Controllers;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    protected static $user = null;

    /**
     * 로그인 테스트
     *
     * @return void
     */
    public function test_login()
    {
        User::factory()->create([
            'name' => 'test',
            'email' => 'logintest@example.com',
        ]);

        // 브라우저 admin 계정 로그인
        self::$user = $this->post('api/v1/login', [
            'email' => 'logintest@example.com',
            'password' => 'todo1234!!'
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'user_id', 'token', 'token_type', 'expired_at'
            ])
            ->original;

        self::$user['token'] = ['Authorization' => self::$user['token_type']. ' ' . self::$user['token']];
    }

    /**
     * 로그인 비밀번호를 틀린 경우 테스트
     *
     * @return void
     */
    public function test_wrong_password_login()
    {
        $this->post('api/v1/login', [
            'email' => 'logintest@example.com',
            'password' => 'todo1234!!!!'
        ])
            ->assertStatus(403);
    }

    /**
     * 로그인 존재하지 않는 이메일인 경우 테스트
     *
     * @return void
     */
    public function test_do_not_exist_email_login()
    {
        $this->post('api/v1/login', [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'todo1234!!'
        ])
            ->assertStatus(403);
    }

    /**
     * 사용자 프로필 조회 테스트
     *
     * @return void
     */
    public function test_show_profile()
    {
        $this->get('api/v1/profile', self::$user['token'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'name', 'email', 'status_message'
            ]);
    }

    /**
     * 사용자 프로필 수정 테스트
     *
     * @return void
     */
    public function test_update_profile()
    {
        $this->put('api/v1/profile', [
            'name' => 'gildong',
            'status_message' => '',
            'delete_status_message' => true
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result' => 'success']);

        $user = User::find(self::$user['user_id']);

        self::assertSame($user->name, 'gildong');
        self::assertSame($user->status_message, '');
    }

    /**
     * 사용자 비밀번호 수정 현재 비밀번호 다른 경우 테스트
     *
     * @return void
     */
    public function test_update_password_wrong_current_password()
    {
        $this->put('api/v1/profile/password', [
            'current_password' => 'todo1234!!!',
            'new_password' => 'gildong1234!!',
            'new_password_confirmation' => 'gildong1234!!'
        ], self::$user['token'])
            ->assertStatus(403);
    }

    /**
     * 사용자 비밀번호 수정 새 비밀번호가 현재 비밀번호와 같은 경우 테스트
     *
     * @return void
     */
    public function test_update_same_current_password()
    {
        $this->put('api/v1/profile/password', [
            'current_password' => 'todo1234!!',
            'new_password' => 'todo1234!!',
            'new_password_confirmation' => 'todo1234!!'
        ], self::$user['token'])
            ->assertStatus(403);
    }

    /**
     * 사용자 비밀번호 수정 테스트
     *
     * @return void
     */
    public function test_update_password()
    {
        $this->put('api/v1/profile/password', [
            'current_password' => 'todo1234!!',
            'new_password' => 'gildong1234!!',
            'new_password_confirmation' => 'gildong1234!!'
        ], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result' => 'success']);

        $user = User::find(self::$user['user_id']);

        self::assertTrue(Hash::check('gildong1234!!', $user->password));
    }

    /**
     * 로그아웃 테스트
     *
     * @return void
     */
    public function test_logout()
    {
        $this->post('api/v1/logout', [], self::$user['token'])
            ->assertStatus(201)
            ->assertJson(['result' => 'success']);
    }
}
