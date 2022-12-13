<?php

namespace App\Jobs;

use App\Models\Routine;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssignRoutineScheduleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now()->startOfDay();

        $routines = Routine::with('tag')
                        ->whereDate('start_date', '<=', $now->toDateString())
                        ->where(function ($query) use ($now) {
                            $query
                                ->whereDate('end_date', '>=', $now->toDateString())
                                ->orWhere('end_date', null);
                        })
                        ->where(function ($query) use ($now) {
                            $query
                                ->where('type', 'month')
                                ->whereJsonContains('schedules->dates', $now->day);
                        })
                        ->orWhere(function ($query) use ($now) {
                            $query
                                ->where('type', 'week')
                                ->whereJsonContains('schedules->days_of_week', strtolower($now->shortEnglishDayOfWeek));
                        })
                        ->get();

        $routines->each(function ($routine) use ($now) {
            $task = new Task();
            $task->user_id = $routine->user_id;
            $task->contents = $routine->contents;
            $task->date = $now->toDateString();
            $task->tag_id = $routine->tag !== null ? $routine->tag_id : null;
            $task->save();
        });
    }
}
