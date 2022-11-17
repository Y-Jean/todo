<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // user 테이블
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('이름');
            $table->string('email')->comment('계정');
            $table->string('password')->nullable()->comment('비밀번호');
            $table->TinyInteger('fail_count')->default(0)->comment('로그인 실패횟수');
            $table->string('status_message')->nullable()->comment('상태메시지');
            $table->timestampTz('last_login_at')->nullable()->comment('마지막 로그인 시간');

            $table->unique('email');

            $table->index('name');
            $table->index('email');

            $table->timestampsTz();
            $table->softDeletesTz();

            // $table->id();
            // $table->string('name');
            // $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password');
            // $table->rememberToken();
            // $table->timestamps();
        });

        // options 테이블
        Schema::create('options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('사용자번호');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        // tags 테이블
        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('사용자번호');
            $table->string('name', 100)->comment('태그 명');
            $table->unsignedSmallInteger('position')->comment('표시순서');
            $table->string('profile_image_id', 10)->nullable()->comment('태그 RGB값');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        // 할일 테이블
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 500)->default('')->comment('일정');
            $table->unsignedBigInteger('user_id')->comment('사용자번호');
            $table->unsignedBigInteger('tag_id')->nullable()->comment('태그번호');
            $table->boolean('done')->default(false)->comment('완료여부');
            $table->timestampTz('dead_line')->nullable()->comment('제한 시간');
            $table->timestampTz('complete_time')->nullable()->comment('완료시간');
            $table->date('date')->comment('날짜');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        // download log 테이블
        Schema::create('download_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('사용자번호');
            $table->date('date')->nullable()->comment('기준일');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('download_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('options');
        Schema::dropIfExists('users');
    }
};
