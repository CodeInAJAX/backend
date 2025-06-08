<?php

namespace App\Models;

use Database\Factories\LessonCompletionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LessonCompletion extends Model
{
    /** @use HasFactory<LessonCompletionFactory> */
    use HasFactory;

    protected $table = 'lesson_completions';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'lesson_id',
        'student_id',
        'watch_duration',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function student() : BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    protected static function boot() :void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

}
