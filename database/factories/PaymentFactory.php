<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(),
            'course_id' => Course::factory()->create(),
            'amount' => $this->faker->randomNumber(),
            'currency' => $this->faker->currencyCode(),
            'payment_method' => $this->faker->randomElement([PaymentMethod::CREDIT_CARD, PaymentMethod::BANK_TRANSFER, PaymentMethod::CASH]),
            'status' => $this->faker->randomElement([StatusPayment::FAILED,StatusPayment::SUCCESS, StatusPayment::PENDING]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
