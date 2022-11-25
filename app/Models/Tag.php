<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CascadeSoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'position',
    ];

    protected $dates = ['deleted_at'];

    protected $cascadeDeletes = ['tagToTasks'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tagToTasks()
    {
        return $this->hasMany(TagToTask::class);
    }
}
