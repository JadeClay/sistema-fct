<?php

namespace Database\Factories;

use App\Models\EmailCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCase>
 */
class EmailCaseFactory extends Factory
{
    protected $model = EmailCase::class;

    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'sender_email' => fake()->email(),
            'body' => fake()->paragraph(),
            'gmail_message_id' => fake()->unique()->sha1(),
            'is_resolved' => false,
        ];
    }
}
