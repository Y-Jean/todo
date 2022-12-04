<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $user = null;

    public function getUser()
    {
        if (self::$user === null) {
            self::$user = $this->post('api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'todo1234!!',
            ])
                ->original;

            self::$user['token'] = ['Authorization' => self::$user['token_type']. ' ' . self::$user['token']];
        }

        return self::$user;
    }
}
