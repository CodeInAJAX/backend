<?php

namespace App\Models;

use App\Enums\Status;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    protected $table = 'enrollments';
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'student_id',
        'enrolled_at',
        'status',
    ];

    protected static function boot() :void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    protected $casts = [
        'status' => Status::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function calculateProgress(): float|int
    {
        $totalLessons = $this->course->lessons()->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = LessonCompletion::query()->where('student_id', $this->student_id)
            ->whereIn('lesson_id', $this->course->lessons()->pluck('id'))
            ->count();

        $progressPercentage = ($completedLessons / $totalLessons) * 100;

        if ($progressPercentage >= 100) {
            $this->status = Status::COMPLETED;
            $this->save();
        }

        return $progressPercentage;
    }
}
