<?php
namespace App\Http\Controllers;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
class StudentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return StudentResource::collection(Student::orderBy('id')->paginate(10));
    }
    public function store(StudentRequest $request): JsonResponse
    {
        return (new StudentResource(Student::create($request->validated())))
            ->response()->setStatusCode(201);
    }
    public function show(Student $student): StudentResource { return new StudentResource($student); }
    public function update(StudentRequest $request, Student $student): StudentResource
    {
        $student->update($request->validated());
        return new StudentResource($student->refresh());
    }
    public function destroy(Student $student): Response
    {
        $student->delete();
        return response()->noContent();
    }
}
