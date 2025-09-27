<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Login d'un admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'institution_id' => 'nullable|integer' 
        ]);

        $user = User::where('email', $request->email)
                    ->where('account_type', 'admin')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Vérifier l'institution si fournie
        $adminRecord = $user->administrators()
                            ->when($request->institution_id, function ($query) use ($request) {
                                $query->where('institution_id', $request->institution_id);
                            })
                            ->first();

        if (!$adminRecord) {
            return response()->json(['message' => 'Pas d’accès à cette institution'], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'admin' => $adminRecord,
            'institution' => $adminRecord->institution,
            'token' => $token
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user(); // utilisateur authentifié
        $adminRecord = $user->administrators()->first(); // tu peux filtrer par institution si besoin

        return response()->json([
            'user' => $user,
            'admin' => $adminRecord,
            'institution' => $adminRecord?->institution
        ]);
    }


    /**
     * Logout (révoque le token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
