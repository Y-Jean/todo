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
        Schema::create('routines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contents', 500)->default('')->comment('일정');
            $table->foreignId('user_id')->comment('사용자번호')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('type', ['month', 'week'])->default('week')->comment('반복 유형(달 단위의 날짜 기반, 주 단위의 요일 기반)');
            $table->json('schedules')->default('[{"dates":[],"days_of_week":[]}]')->comment('반복 일정');
            $table->date('start_date')->nullable()->comment('루틴 시작일');
            $table->date('end_date')->nullable()->comment('루틴 종료일');
            $table->foreignId('tag_id')->nullable()->comment('태그번호')->constrained('tags')->cascadeOnUpdate();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routines');
    }
};
