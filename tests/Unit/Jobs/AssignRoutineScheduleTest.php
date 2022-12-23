<?php

namespace Tests\Unit\Jobs;

use App\Models\{User, Routine};
use App\Jobs\AssignRoutineScheduleJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AssignRoutineScheduleTest extends TestCase
{
    /**
     * routine을 task로 등록하는 job 테스트
     *
     * @return void
     */
    public function test_assign_routine_to_task()
    {
        Queue::fake();

        $user = User::factory()->create();

        $routine = Routine::factory()->create([
            'user_id' => $user->id,
            'tag_id' => null,
            'contents' => 'Job 테스트를 위해 생성한 오늘 날짜에 반복되는 루틴',
            'type' => 'month',
            'schedules' => [
                'dates' => [Carbon::now()->day],
                'days_of_week' => []
            ],
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString()
        ]);

        $job = new AssignRoutineScheduleJob();
        $job->dispatch();

        Queue::assertPushed(AssignRoutineScheduleJob::class);

        $job->handle();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'contents' => $routine->contents,
            'date' => Carbon::now()->toDateString()
        ]);
    }
}
