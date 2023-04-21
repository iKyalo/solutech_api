<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\UserTask;
use App\Models\User;
use App\Models\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UserTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'due_date' => Carbon::now()->addDays(rand(1, 30)),
            'start_time' => Carbon::now()->addHours(rand(1, 12)),
            'end_time' => Carbon::now()->addHours(rand(13, 24)),
            'remarks' => $this->faker->sentence,
            'status_id' => rand(1, 3),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ];
    }
}
