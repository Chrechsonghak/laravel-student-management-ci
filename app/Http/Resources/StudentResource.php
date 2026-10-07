<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'student_number' => $this->student_number,
            'name' => $this->name, 'email' => $this->email, 'course' => $this->course,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
