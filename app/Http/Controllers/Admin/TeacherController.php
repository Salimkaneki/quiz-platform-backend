<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
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

        $query = Teacher::with(['user', 'institution'])
                        ->where('institution_id', $admin->institution_id);

        if ($request->grade) {
            $query->byGrade($request->grade);
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
            'user_id'        => 'required|exists:users,id',
            'specialization' => 'required|string|max:255',
            'grade'          => 'required|in:vacataire,certifié,agrégé,maître_de_conférences,professeur',
            'is_permanent'   => 'boolean',
            'metadata'       => 'nullable|array'
        ]);

        // Vérifier que le user est bien un enseignant
        $user = \App\Models\User::find($data['user_id']);
        if ($user->account_type !== 'teacher') {
            return response()->json(['message' => 'Le compte choisi n’est pas de type enseignant'], 422);
        }

        // Vérifier qu’il n’est pas déjà enregistré comme enseignant
        if (Teacher::where('user_id', $data['user_id'])->exists()) {
            return response()->json(['message' => 'Cet utilisateur est déjà enregistré comme enseignant'], 422);
        }

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
            'grade'          => 'sometimes|required|in:vacataire,certifié,agrégé,maître_de_conférences,professeur',
            'is_permanent'   => 'boolean',
            'metadata'       => 'nullable|array'
        ]);

        // ⚠️ user_id non modifiable pour éviter des incohérences
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

    // ---------------------------------------------------
    // MÉTHODES PRIVÉES
    // ---------------------------------------------------

    /**
     * Vérifie que l’utilisateur connecté est un admin pédagogique
     */
    private function checkPedagogicalPermissions($institutionId = null)
    {
        $currentUser = auth()->user();

        // 1) il doit être "admin" dans la table users
        if ($currentUser->account_type !== 'admin') {
            return null;
        }

        // 2) il doit être dans administrators() avec type = "pedagogique"
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
