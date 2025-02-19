<?php

namespace Database\Factories;

use App\Models\EmailList;
use App\Models\User;
use App\Models\EmailListName;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailListFactory extends Factory
{
    protected $model = EmailList::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'list_id' => EmailListName::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $this->faker->randomElement(['FAIL', 'SENT', 'NULL']),
            'send_time' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'sender_email' => $this->faker->optional(0.7)->safeEmail(),
            'log' => $this->faker->optional(0.7)->text(200),
        ];
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'FAIL',
                'send_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'log' => 'Failed to send: ' . $this->faker->sentence(),
            ];
        });
    }

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'SENT',
                'send_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'log' => 'Successfully sent at ' . now(),
            ];
        });
    }

    public function nullStatus()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => null,
                'send_time' => null,
                'sender_email' => null,
                'log' => null,
            ];
        });
    }
}
