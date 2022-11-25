<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CascadeSoftDeletes;

    protected $fillable = [
        'contents',
        'user_id',
        'done',
    ];

    protected $dates = [
        'dead_line',
        'complete_time',
        'deleted_at'
    ];

    protected $cascadeDeletes = ['tagToTasks'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tagToTasks()
    {
        return $this->hasMany(TagToTask::class, 'task_id');
    }
}
