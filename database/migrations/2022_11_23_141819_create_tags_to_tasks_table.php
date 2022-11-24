<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->unsignedBigInteger('user_id')->comment('사용자번호');
            $table->unsignedBigInteger('task_id')->comment('할일번호');
            $table->unsignedBigInteger('tag_id')->comment('태그번호');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('tags', function(Blueprint $table) {
            $table->renameColumn('profile_image_id', 'color');
        });

        Schema::table('tasks', function(Blueprint $table) {
            $table->renameColumn('name', 'contents');
            $table->dropColumn('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function(Blueprint $table) {
            $table->unsignedBigInteger('tag_id')->nullable()->comment('태그번호');
            $table->renameColumn('contents', 'name');
        });

        Schema::table('tags', function(Blueprint $table) {
            $table->renameColumn('color', 'profile_image_id');
        });

        Schema::dropIfExists('tag_to_tasks');
    }
};
