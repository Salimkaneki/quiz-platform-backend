<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\TeacherSubject;
use App\Models\Administrator;
use Illuminate\Http\Request;

class TeacherSubjectController extends Controller
{
    // ========== Lister toutes les attributions ==========
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $query = TeacherSubject::with(['teacher', 'subject', 'classe'])
            ->whereHas('teacher', fn($q) => $q->where('institution_id', $admin->institution_id));

        foreach (['teacher_id','subject_id','classe_id','academic_year'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json($query->paginate(15));
    }

    // ========== Afficher une attribution ==========
    public function show(TeacherSubject $teacherSubject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $teacherSubject->load(['teacher', 'subject', 'classe']);
        if ($teacherSubject->teacher->institution_id !== $admin->institution_id) {
            return $this->forbidden('Non autorisé à voir cette attribution.');
        }

        return response()->json($teacherSubject);
    }

    // ========== Créer une attribution ==========
    public function store(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $data = $request->validate([
            'teacher_id'    => 'required|exists:teachers,id',
            'subject_id'    => 'required|exists:subjects,id',
            'classe_id'     => 'nullable|exists:classes,id',
            'academic_year' => 'required|string|max:10',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        $teacher = Teacher::findOrFail($data['teacher_id']);
        $subject = Subject::with('formation')->findOrFail($data['subject_id']);
        $classe = $data['classe_id'] ? Classes::with('formation')->findOrFail($data['classe_id']) : null;

        foreach ([$teacher, $subject, $classe] as $item) {
            if ($item && !$this->belongsToInstitution($item, $admin, $item === $subject || $item === $classe ? 'formation' : null)) {
                return $this->forbidden("Cet élément n'appartient pas à votre institution.");
            }
        }

        // Vérifier si l'attribution existe déjà
        if (TeacherSubject::where([
            'teacher_id' => $data['teacher_id'],
            'subject_id' => $data['subject_id'],
            'classe_id' => $data['classe_id'] ?? null,
            'academic_year' => $data['academic_year'],
        ])->exists()) {
            return response()->json([
                'message' => 'Cette matière est déjà attribuée à cet enseignant pour cette année académique.'
            ], 409);
        }

        $attribution = TeacherSubject::create($data)->load(['teacher', 'subject', 'classe']);

        return response()->json([
            'message' => 'Attribution créée avec succès.',
            'data' => $attribution
        ], 201);
    }

    // ========== Mettre à jour ==========
    public function update(Request $request, TeacherSubject $teacherSubject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $teacherSubject->load('teacher');
        if ($teacherSubject->teacher->institution_id !== $admin->institution_id) {
            return $this->forbidden('Non autorisé à modifier cette attribution.');
        }

        $data = $request->validate([
            'classe_id'     => 'nullable|exists:classes,id',
            'academic_year' => 'sometimes|required|string|max:10',
            'is_active'     => 'boolean',
        ]);

        if (isset($data['classe_id']) && $data['classe_id']) {
            $classe = Classes::with('formation')->findOrFail($data['classe_id']);
            if (!$this->belongsToInstitution($classe, $admin, 'formation')) {
                return $this->forbidden("Cette classe n'appartient pas à votre institution.");
            }
        }

        $teacherSubject->update($data);
        return response()->json($teacherSubject->fresh()->load(['teacher','subject','classe']));
    }

    // ========== Supprimer ==========
    public function destroy(TeacherSubject $teacherSubject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $teacherSubject->load('teacher');
        if ($teacherSubject->teacher->institution_id !== $admin->institution_id) {
            return $this->forbidden('Non autorisé à supprimer cette attribution.');
        }

        $teacherSubject->delete();
        return response()->json(['message' => 'Attribution supprimée.']);
    }

    // ========== Helpers ==========
    private function checkPedagogicalPermissions()
    {
        $user = auth()->user();
        if (!$user) return null;
        return Administrator::where('user_id',$user->id)->where('type','pedagogique')->first();
    }

    private function forbidden($message)
    {
        return response()->json(['message'=>$message],403);
    }

    private function belongsToInstitution($model, $admin, $relation = null)
    {
        $institutionId = $relation ? $model->{$relation}->institution_id : $model->institution_id;
        return $institutionId === $admin->institution_id;
    }

    public function teachersWithSubjects()
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) return $this->forbidden('Non autorisé.');

        $teachers = Teacher::with([
            'teacherSubjects.subject',
            'teacherSubjects.classe'
        ])->where('institution_id', $admin->institution_id)->get();

        $result = $teachers->map(function($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->getFullName(),
                'grade' => $teacher->getGradeLabel(),
                'status' => $teacher->getStatusLabel(),
                'subjects' => $teacher->teacherSubjects->map(function($ts) {
                    return [
                        'id' => $ts->subject->id,
                        'name' => $ts->subject->name,
                        'classe' => $ts->classe?->name,
                        'academic_year' => $ts->academic_year,
                        'is_active' => $ts->is_active,
                    ];
                })
            ];
        });

        return response()->json($result);
    }


}
