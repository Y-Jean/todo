<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'done',
    ];

    protected $dates = [
        'dead_line',
        'complete_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
