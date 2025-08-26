<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizSessionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'starts_at' => 'sometimes|date|after:now',
            'ends_at' => 'sometimes|date|after:starts_at',
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
        ];
    }
}
