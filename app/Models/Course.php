<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected $table = 'courses';
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'title',
        'thumbnail',
        'description',
        'price',
        'currency',
        'mentor_id'
    ];

    public function mentor() : BelongsTo {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function lessons() : HasMany
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function students() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id')
            ->withTimestamps()
            ->withPivot( 'status');
    }

    public function ratingsUsers() : BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'ratings', 'student_id', 'course_id')
            ->withTimestamps()
            ->withPivot('rating', 'comment');
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
