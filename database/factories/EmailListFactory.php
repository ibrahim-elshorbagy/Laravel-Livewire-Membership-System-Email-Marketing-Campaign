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
            'name' => $this->faker->name(),
        ];
    }


}
