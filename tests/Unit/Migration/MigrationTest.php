<?php

namespace Tests\Unit\Migration;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    /**
     * migrate refresh test
     *
     * @return void
     */
    public function test_migrate_refresh()
    {
        $exitCode = Artisan::call('migrate:refresh');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * migrate rollback test
     *
     * @return void
     */
    public function test_migrate_rollback()
    {
        $exitCode = Artisan::call('migrate:rollback');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * migrate test
     *
     * @return void
     */
    public function test_migrate()
    {
        $exitCode = Artisan::call('migrate');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * db seed test
     *
     * @return void
     */
    public function test_seed()
    {
        $exitCode = Artisan::call('db:seed');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * db TaskSeeder test
     *
     * @return void
     */
    public function test_task_seed()
    {
        $exitCode = Artisan::call('db:seed --class=TaskSeeder');
        $this->assertEquals(0, $exitCode);
    }
}
