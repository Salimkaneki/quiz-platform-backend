<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Administrator;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Liste des matières avec filtres
     */
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent voir les matières');
        }

        $query = Subject::with('formation')
                        ->whereHas('formation', function($q) use ($admin) {
                            $q->where('institution_id', $admin->institution_id);
                        });

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        return $query->paginate(15);
    }

    /**
     * Création d'une matière
     */
    public function store(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent créer des matières');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'credits' => 'integer|min:1',
            'coefficient' => 'integer|min:1',
            'type' => 'required|in:cours,td,tp,projet',
            'formation_id' => 'required|exists:formations,id',
            'semester' => 'integer|min:1|max:2',
            'is_active' => 'boolean',
        ]);

        // Vérifier que la formation appartient bien à l'institution de l'admin
        $formation = $admin->institution->formations()->find($data['formation_id']);
        if (!$formation) {
            return $this->forbiddenResponse('Formation non autorisée pour cette institution');
        }

        $subject = Subject::create($data);
        return response()->json($subject->load('formation'), 201);
    }

    /**
     * Affichage d'une matière
     */
    public function show(Subject $subject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin || $subject->formation->institution_id !== $admin->institution_id) {
            return $this->forbiddenResponse('Non autorisé à voir cette matière');
        }

        return $subject->load('formation');
    }

    /**
     * Mise à jour d'une matière
     */
    public function update(Request $request, Subject $subject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin || $subject->formation->institution_id !== $admin->institution_id) {
            return $this->forbiddenResponse('Non autorisé à modifier cette matière');
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'credits' => 'integer|min:1',
            'coefficient' => 'integer|min:1',
            'type' => 'in:cours,td,tp,projet',
            'semester' => 'integer|min:1|max:2',
            'is_active' => 'boolean',
        ]);

        $subject->update($data);
        return $subject->load('formation');
    }

    /**
     * Suppression d'une matière
     */
    public function destroy(Subject $subject)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin || $subject->formation->institution_id !== $admin->institution_id) {
            return $this->forbiddenResponse('Non autorisé à supprimer cette matière');
        }

        $subject->delete();
        return response()->json(['message' => 'Matière supprimée']);
    }

    // ---------------- Méthodes privées ----------------

    private function checkPedagogicalPermissions()
    {
        $currentUser = auth()->user();
        return Administrator::where('user_id', $currentUser->id)
                            ->where('type', 'pedagogique')
                            ->first();
    }

    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }
}
