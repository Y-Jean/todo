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

    public $default_color = '#ff6600';

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

    public function replacePosition($user, $position, $delete = false)
    {
        // 태그가 삭제되는 경우, 뒷번호를 앞으로 옮기고 return
        if ($delete) {
            Tag::where([
                    ['user_id', $user->id],
                    ['position', '>', $position]
                ])
                ->decrement('position', 1);

            return ;
        }

        // 마지막 순서보다 높은 순서를 입력받은 경우
        $lastTag = Tag::where('user_id', $user->id)->latest('position')->first();
        if ($position > $lastTag->position) {
            $position = $lastTag->position;
        }

        if ($this->position < $position) {
            // 태그의 위치가 앞에서 뒤로 바뀌는 경우
            Tag::where([
                ['user_id', $user->id],
                ['position', '>', $this->position],
                ['position', '<=', $position]
            ])->decrement('position', 1);
        } elseif ($this->position > $position) {
            // 태그의 위치가 뒤에서 앞으로 바뀌는 경우
            Tag::where([
                ['user_id', $user->id],
                ['position', '>=', $position],
                ['position', '<', $this->position]
            ])->increment('position', 1);
        }

        return $position;
    }

    /**
     * 테스트 시 팩토리로 생성한 태그의 포지션 정리
     */
    public function resetPosition($user_id)
    {
        $tags = Tag::where('user_id', $user_id)->orderBy('created_at')->get();

        $position = 0;
        foreach ($tags as $tag) {
            $tag->position = $position;
            $tag->save();
            $position++;
        }
    }
}
