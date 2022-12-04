<?php

namespace Tests\Unit\Controllers;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    protected static $name = null;
    protected static $email = null;
    protected static $commute_id = null;

    /**
     * 회원가입 테스트
     *
     * @return void
     */
    public function test_register()
    {
        self::$name = fake()->name();
        self::$email = fake()->unique()->safeEmail();

        $this->post('api/v1/register', [
            'name' => self::$name,
            'email' => self::$email,
            'password' => 'todo1234!!',
            'password_confirmation' => 'todo1234!!'
        ])
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);

        $user = User::firstWhere('email', self::$email);

        self::assertTrue(Hash::check('todo1234!!', $user->password));
        self::assertSame(self::$name, $user->name);
        self::assertSame(self::$email, $user->email);
    }

    /**
     * 중복 email로 인한 회원가입 실패 테스트
     *
     * @return void
     */
    public function test_register_same_email()
    {
        $this->post('api/v1/register', [
            'name' => fake()->name(),
            'email' => self::$email,
            'password' => 'todo1234!!',
            'password_confirmation' => 'todo1234!!'
        ])
            ->assertStatus(403);
    }

    /**
     * 회원탈퇴 테스트
     *
     * @return void
     */
    public function test_withdrawal()
    {
        $data = $this->post('api/v1/login', [
            'email' => self::$email,
            'password' => 'todo1234!!',
        ])
            ->original;
        $token = ['Authorization' => $data['token_type']. ' ' . $data['token']];

        $this->delete('api/v1/withdrawal', [
            'password' => 'todo1234!!',
        ], $token)
            ->assertStatus(201)
            ->assertJson(['result'=>'success']);
    }
}
