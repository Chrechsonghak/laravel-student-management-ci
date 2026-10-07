<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $student = $this->route('student');
        return [
            'student_number' => [$required, 'required', 'string', 'max:30', Rule::unique('students')->ignore($student)],
            'name' => [$required, 'required', 'string', 'max:100'],
            'email' => [$required, 'required', 'email', 'max:255', Rule::unique('students')->ignore($student)],
            'course' => [$required, 'required', 'string', 'max:100'],
        ];
    }
}
