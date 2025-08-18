<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Administrator;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    /**
     * Liste des formations de l'institution de l'admin
     */
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent voir les formations');
        }

        $query = Formation::where('institution_id', $admin->institution_id);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        return $query->paginate(15);
    }

    /**
     * Création d'une formation
     */
    public function store(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Seuls les administrateurs pédagogiques peuvent créer une formation');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:formations,code',
            'description' => 'nullable|string',
            'duration_years' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['institution_id'] = $admin->institution_id;
        $formation = Formation::create($data);

        return response()->json($formation, 201);
    }

    /**
     * Affichage d'une formation
     */
    public function show(Formation $formation)
    {
        $admin = $this->checkPedagogicalPermissions($formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à voir cette formation');
        }

        return $formation;
    }

    /**
     * Mise à jour d'une formation
     */
    public function update(Request $request, Formation $formation)
    {
        $admin = $this->checkPedagogicalPermissions($formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à modifier cette formation');
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:formations,code,' . $formation->id,
            'description' => 'nullable|string',
            'duration_years' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $formation->update($data);
        return $formation;
    }

    /**
     * Suppression d'une formation
     */
    public function destroy(Formation $formation)
    {
        $admin = $this->checkPedagogicalPermissions($formation->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé à supprimer cette formation');
        }

        $formation->delete();
        return response()->json(['message' => 'Formation supprimée']);
    }

    // ---------------- Méthodes privées ----------------

    /**
     * Vérifie que l'utilisateur connecté est un admin pédagogique
     */
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

    /**
     * Réponse JSON 403
     */
    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }
}
