<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizSessionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Auth via middleware
    }

    public function rules()
    {
        return [
            'quiz_id' => 'required|exists:quizzes,id',
            'title' => 'required|string|max:255',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'max_participants' => 'nullable|integer|min:1',
            'require_student_list' => 'boolean',
            'allowed_students' => 'nullable|array|max:100',
            'allowed_students.*' => 'required|integer|exists:students,id',
            'settings' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'allowed_students.*.exists' => 'Un ou plusieurs étudiants sélectionnés n\'existent pas.',
            'allowed_students.*.integer' => 'Les identifiants d\'étudiants doivent être des nombres.',
            'starts_at.after' => 'La date de début doit être dans le futur.',
            'ends_at.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}
