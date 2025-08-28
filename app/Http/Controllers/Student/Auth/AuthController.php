<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login étudiant
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('email', $request->email)
                    ->where('account_type', 'student')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe invalide'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Compte inactif, contactez l\'administration'
            ], 403);
        }

        $token = $user->createToken('student-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    /**
     * Logout étudiant (révoque le token actuel)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Info utilisateur connecté
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
