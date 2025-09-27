<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Administrator;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    /**
     * Liste des classes de l'institution de l'admin
     */
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent voir les classes.');
        }

        $query = Classes::whereHas('formation', function ($q) use ($admin) {
            $q->where('institution_id', $admin->institution_id);
        });

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('academic_year', 'like', "%{$request->search}%");
        }

        return $query->with('formation')->paginate(15);
    }

    /**
     * Création d'une classe
     */
    public function store(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent créer une classe.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:1',
            'academic_year' => 'required|string|max:20',
            'formation_id' => 'required|exists:formations,id',
            'max_students' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Vérification que la formation appartient à l'institution de l'admin
        $formation = \App\Models\Formation::find($data['formation_id']);
        if ($formation->institution_id !== $admin->institution_id) {
            return $this->forbiddenResponse('La formation sélectionnée ne vous appartient pas.');
        }

        $classe = Classes::create($data);

        return response()->json($classe->load('formation'), 201);
    }

    /**
     * Affichage d'une classe
     */
    public function show(Classes $classe)
    {
        // Charger la formation pour accéder à l'institution_id
        $classe->load('formation');
        
        $admin = $this->checkPedagogicalPermissions($classe->formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à voir cette classe.');
        }

        return response()->json($classe->load('formation', 'students', 'teachers', 'subjects'));
    }

    /**
     * Mise à jour d'une classe
     */
    public function update(Request $request, Classes $classe)
    {
        // Charger la formation pour accéder à l'institution_id
        $classe->load('formation');
        
        $admin = $this->checkPedagogicalPermissions($classe->formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à modifier cette classe.');
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|integer|min:1',
            'academic_year' => 'sometimes|required|string|max:20',
            'formation_id' => 'sometimes|required|exists:formations,id',
            'max_students' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Vérification formation si changé
        if (isset($data['formation_id'])) {
            $formation = \App\Models\Formation::find($data['formation_id']);
            if ($formation->institution_id !== $admin->institution_id) {
                return $this->forbiddenResponse('La formation sélectionnée ne vous appartient pas.');
            }
        }

        $classe->update($data);
        return response()->json($classe->fresh()->load('formation'));
    }

    /**
     * Suppression d'une classe
     */
    public function destroy(Classes $classe)
    {
        // Charger la formation pour accéder à l'institution_id
        $classe->load('formation');
        
        $admin = $this->checkPedagogicalPermissions($classe->formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à supprimer cette classe.');
        }

        $classe->delete();
        return response()->json(['message' => 'Classe supprimée']);
    }

    // ---------------- Méthodes privées ----------------

    private function checkPedagogicalPermissions($institutionId = null)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser) {
            return null;
        }

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