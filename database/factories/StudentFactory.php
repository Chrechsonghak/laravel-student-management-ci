<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_number' => fake()->unique()->numerify('STU-#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'course' => 'DevOps',
        ];
    }
}
