<?php
namespace Database\Seeders;
use App\Models\Student;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Sokha', 'Dara', 'Sophea'] as $i => $name) {
            Student::updateOrCreate(['student_number' => 'STU-00'.($i + 1)], [
                'name' => $name.' Demo', 'email' => strtolower($name).'@example.com', 'course' => 'DevOps',
            ]);
        }
    }
}
