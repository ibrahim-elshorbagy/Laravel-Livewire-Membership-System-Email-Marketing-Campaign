<?php

namespace Database\Factories;

use App\Models\EmailListName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailListNameFactory extends Factory
{
    protected $model = EmailListName::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->words(2, true),
        ];
    }
}
