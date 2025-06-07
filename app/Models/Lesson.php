<?php

namespace App\Models;

use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected $table = 'lessons';
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
      'title',
      'description',
      'video_link',
        'duration',
        'order_number',
        'course_id',
    ];

    public function course() : BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessonsCompletions() : HasMany
    {
        return $this->hasMany(LessonCompletion::class, 'lesson_id');

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
