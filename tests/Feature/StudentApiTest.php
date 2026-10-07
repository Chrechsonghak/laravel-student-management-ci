<?php
namespace Tests\Feature;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
class StudentApiTest extends TestCase
{
    use RefreshDatabase;
    private function payload(array $changes = []): array
    {
        return array_merge(['student_number' => 'STU-001', 'name' => 'Sokha Demo',
            'email' => 'sokha@example.com', 'course' => 'DevOps'], $changes);
    }
    public function test_lists_students_with_pagination(): void
    {
        Student::factory()->count(12)->create();
        $this->getJson('/api/students')->assertOk()->assertJsonCount(10, 'data')->assertJsonPath('meta.total', 12);
        $this->getJson('/api/students?page=2')->assertOk()->assertJsonCount(2, 'data');
    }
    public function test_empty_list_is_successful(): void
    {
        $this->getJson('/api/students')->assertOk()->assertJsonCount(0, 'data');
    }
    public function test_creates_student_and_persists_it(): void
    {
        $this->postJson('/api/students', $this->payload())->assertCreated()->assertJsonPath('data.name', 'Sokha Demo');
        $this->assertDatabaseHas('students', $this->payload());
    }
    public function test_shows_a_student(): void
    {
        $student = Student::factory()->create();
        $this->getJson('/api/students/'.$student->id)->assertOk()->assertJsonPath('data.email', $student->email);
    }
    public function test_replaces_a_student(): void
    {
        $student = Student::factory()->create();
        $this->putJson('/api/students/'.$student->id, $this->payload())->assertOk()->assertJsonPath('data.name', 'Sokha Demo');
        $this->assertDatabaseHas('students', ['id' => $student->id] + $this->payload());
    }
    public function test_patch_changes_one_field_and_keeps_the_others(): void
    {
        $student = Student::factory()->create();
        $this->patchJson('/api/students/'.$student->id, ['course' => 'Laravel'])->assertOk()->assertJsonPath('data.course', 'Laravel');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'email' => $student->email, 'course' => 'Laravel']);
    }
    public function test_update_allows_own_unique_values(): void
    {
        $student = Student::factory()->create($this->payload());
        $this->putJson('/api/students/'.$student->id, $this->payload(['name' => 'Updated Demo']))->assertOk();
    }
    public function test_deletes_a_student(): void
    {
        $student = Student::factory()->create();
        $this->deleteJson('/api/students/'.$student->id)->assertNoContent();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->getJson('/api/students/'.$student->id)->assertNotFound();
    }
    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/api/students', [])->assertUnprocessable()->assertJsonValidationErrors(['student_number', 'name', 'email', 'course']);
        $this->assertDatabaseCount('students', 0);
    }
    public function test_invalid_email_is_rejected(): void
    {
        $this->postJson('/api/students', $this->payload(['email' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('students', 0);
    }
    public function test_duplicate_values_cannot_be_created(): void
    {
        Student::factory()->create($this->payload());
        $this->postJson('/api/students', $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['email', 'student_number']);
        $this->assertDatabaseCount('students', 1);
    }
    public function test_update_cannot_take_another_students_unique_values(): void
    {
        Student::factory()->create($this->payload());
        $other = Student::factory()->create();
        $this->patchJson('/api/students/'.$other->id, $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['email', 'student_number']);
        $this->assertDatabaseHas('students', ['id' => $other->id, 'email' => $other->email]);
    }
    public function test_patch_cannot_clear_required_name(): void
    {
        $student = Student::factory()->create();
        $this->patchJson('/api/students/'.$student->id, ['name' => null])->assertUnprocessable()->assertJsonValidationErrors('name');
    }
    public function test_put_requires_full_payload(): void
    {
        $student = Student::factory()->create();
        $this->putJson('/api/students/'.$student->id, ['name' => 'Demo'])->assertUnprocessable()->assertJsonValidationErrors(['student_number', 'email', 'course']);
    }
    public function test_unknown_fields_are_not_persisted(): void
    {
        $this->postJson('/api/students', $this->payload(['id' => 999]))->assertCreated();
        $this->assertDatabaseMissing('students', ['id' => 999]);
    }
    public function test_missing_students_return_404(): void
    {
        $this->getJson('/api/students/999')->assertNotFound();
        $this->putJson('/api/students/999', $this->payload())->assertNotFound();
        $this->patchJson('/api/students/999', ['name' => 'Demo'])->assertNotFound();
        $this->deleteJson('/api/students/999')->assertNotFound();
    }
    public function test_overlong_fields_are_rejected(): void
    {
        $this->postJson('/api/students', $this->payload(['name' => str_repeat('a', 101), 'course' => str_repeat('a', 101), 'student_number' => str_repeat('a', 31)]))->assertUnprocessable()->assertJsonValidationErrors(['name', 'course', 'student_number']);
    }
}
