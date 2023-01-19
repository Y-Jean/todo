<?php

namespace App\Models;

use App\Enums\RefeatType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Routine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contents',
        'user_id'
    ];

    protected $casts = [
        'schedules' => 'array',
        'type' => RefeatType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
