<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;

class AdministratorController extends Controller
{
    public function index(Request $request)
    {
        // $this->requiresAdminAccess();
        
        $query = Administrator::with(['user', 'institution']);
        
        if ($request->type) {
            $query->byType($request->type);
        }
        
        if ($request->institution_id) {
            $query->byInstitution($request->institution_id);
        }
        
        return $query->paginate(15);
    }

    public function store(Request $request)
    {
        // $this->requiresAdminAccess();
        
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'institution_id' => 'required|exists:institutions,id',
            'type' => 'required|in:pedagogique,scolarite,direction',
            'permissions' => 'nullable|array'
        ]);

        // Vérifier que l'utilisateur est bien un admin
        $user = User::findOrFail($data['user_id']);
        if ($user->account_type !== 'admin') {
            return $this->badRequestResponse('L\'utilisateur doit avoir le type admin');
        }

        // Vérifier qu'il n'est pas déjà admin de ce type dans cette institution
        $existing = Administrator::where('user_id', $data['user_id'])
                                ->where('institution_id', $data['institution_id'])
                                ->where('type', $data['type'])
                                ->first();

        if ($existing) {
            return $this->conflictResponse('Cet utilisateur est déjà administrateur de ce type dans cette institution');
        }

        $administrator = Administrator::create($data);
        return $administrator->load(['user', 'institution']);
    }

    public function show(Administrator $administrator)
    {
        // $this->requiresAdminAccess();
        
        return $administrator->load(['user', 'institution']);
    }

    public function update(Request $request, Administrator $administrator)
    {
        // $this->requiresAdminAccess();
        
        $data = $request->validate([
            'type' => 'sometimes|in:pedagogique,scolarite,direction',
            'permissions' => 'nullable|array'
        ]);

        $administrator->update($data);
        
        return $administrator->load(['user', 'institution']);
    }

    public function destroy(Administrator $administrator)
    {
        // $this->requiresAdminAccess();
        
        $administrator->delete();
        return response()->json(['message' => 'Administrateur supprimé']);
    }

    public function byInstitution($institutionId)
    {
        // $this->requiresAdminAccess();
        
        $administrators = Administrator::byInstitution($institutionId)
                                     ->with(['user', 'institution'])
                                     ->get();
        
        return response()->json($administrators);
    }

    public function byType($type)
    {
        // $this->requiresAdminAccess();
        
        $validTypes = ['pedagogique', 'scolarite', 'direction'];
        
        if (!in_array($type, $validTypes)) {
            return $this->badRequestResponse('Type invalide');
        }
        
        $administrators = Administrator::byType($type)
                                     ->with(['user', 'institution'])
                                     ->paginate(15);
        
        return $administrators;
    }

    // Méthodes privées
    private function requiresAdminAccess()
    {
        // $currentUser = auth()->user();
        
        if ($currentUser->account_type !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent accéder à cette ressource');
        }
    }

    private function badRequestResponse($message)
    {
        return response()->json(['message' => $message], 400);
    }

    private function conflictResponse($message)
    {
        return response()->json(['message' => $message], 409);
    }
}