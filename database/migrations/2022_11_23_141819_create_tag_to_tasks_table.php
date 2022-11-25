<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_to_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->comment('사용자번호')->constrained('users', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('task_id')->comment('일정번호')->constrained('tasks', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tag_id')->comment('태그번호')->constrained('tags', 'id')->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->renameColumn('profile_image_id', 'color');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('name', 'contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('contents', 'name');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->renameColumn('color', 'profile_image_id');
        });

        Schema::dropIfExists('tag_to_tasks');
    }
};
