<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $table = 'payments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'course_id',
        'amount',
        'currency',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'status' => StatusPayment::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');

    }
    public function course() : BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    protected static function boot() :void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
            if (empty($model->status)) {
                $model->status = StatusPayment::PENDING;
            }
        });
    }

}
