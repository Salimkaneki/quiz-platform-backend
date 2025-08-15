<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->requiresAdminAccess();
        
        $query = User::query();
        
        if ($request->account_type) {
            $query->where('account_type', $request->account_type);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        return $query->paginate(15);
    }

    public function store(Request $request)
    {
        // On commente l'accès admin si pas d'auth encore
        // $this->requiresAdminAccess();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'account_type' => 'required|in:admin,teacher,student',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        $this->requiresAdminAccess();
        
        return $user;
    }

    public function update(Request $request, User $user)
    {
        $this->requiresAdminAccess();
        
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'account_type' => 'sometimes|required|in:admin,teacher,student',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update($data);
        return $user;
    }

    public function destroy(User $user)
    {
        $this->requiresAdminAccess();
        
        if ($user->administrators()->exists() || $user->teachers()->exists()) {
            return $this->badRequestResponse('Impossible de supprimer cet utilisateur car il est lié à des rôles actifs');
        }

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }

    public function byAccountType($accountType)
    {
        $this->requiresAdminAccess();
        
        $validTypes = ['admin', 'teacher', 'student'];
        
        if (!in_array($accountType, $validTypes)) {
            return $this->badRequestResponse('Type de compte invalide');
        }
        
        return User::where('account_type', $accountType)->paginate(15);
    }

    // Méthodes privées
    private function requiresAdminAccess()
    {
        $currentUser = auth()->user();
        
        if (!$currentUser || $currentUser->account_type !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent accéder à cette ressource');
        }
    }

    private function badRequestResponse($message)
    {
        return response()->json(['message' => $message], 400);
    }
}