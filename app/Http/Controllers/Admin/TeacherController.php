<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Administrator;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent voir les enseignants');
        }
        
        $query = Teacher::with(['user', 'institution']);
        
        // Tous les filtres en un seul endroit
        if ($request->grade) {
            $query->byGrade($request->grade);
        }
        
        if ($request->institution_id) {
            $query->byInstitution($request->institution_id);
        }
        
        if ($request->specialization) {
            $query->bySpecialization($request->specialization);
        }
        
        if ($request->has('is_permanent')) {
            $query->where('is_permanent', $request->boolean('is_permanent'));
        }
        
        return $query->paginate(15);
    }

    public function store(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent créer des enseignants');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'specialization' => 'required|string|max:255',
            'grade' => 'required|in:vacataire,certifié,agrégé,maître_de_conférences,professeur',
            'is_permanent' => 'boolean',
            'metadata' => 'nullable|array'
        ]);

        $data['institution_id'] = $admin->institution_id;
        $teacher = Teacher::create($data);
        
        return $teacher->load(['user', 'institution']);
    }

    public function show(Teacher $teacher)
    {
        $admin = $this->checkPedagogicalPermissions($teacher->institution_id);
        
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à voir cet enseignant');
        }
        
        return $teacher->load(['user', 'institution']);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $admin = $this->checkPedagogicalPermissions($teacher->institution_id);
        
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à modifier cet enseignant');
        }

        $data = $request->validate([
            'specialization' => 'sometimes|required|string|max:255',
            'grade' => 'sometimes|required|in:vacataire,certifié,agrégé,maître_de_conférences,professeur',
            'is_permanent' => 'boolean',
            'metadata' => 'nullable|array'
        ]);

        $teacher->update($data);
        return $teacher->load(['user', 'institution']);
    }

    public function destroy(Teacher $teacher)
    {
        $admin = $this->checkPedagogicalPermissions($teacher->institution_id);
        
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à supprimer cet enseignant');
        }

        $teacher->delete();
        return response()->json(['message' => 'Enseignant supprimé']);
    }

    // Méthodes privées pour la logique commune
    private function checkPedagogicalPermissions($institutionId = null)
    {
        $currentUser = auth()->user();
        $query = Administrator::where('user_id', $currentUser->id)
                             ->where('type', 'pedagogique');
        
        if ($institutionId) {
            $query->where('institution_id', $institutionId);
        }
        
        return $query->first();
    }

    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }
}