<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        if ($user->account_type !== 'teacher') {
            return response()->json(['message' => 'Ce compte n’est pas un enseignant'], 403);
        }

        $token = $user->createToken('teacher_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->load('teacher') // inclut ses infos Teacher
        ]);
    }

    public function me(Request $request)
    {
        return $request->user()->load('teacher');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    // App\Http\Controllers\Auth\TeacherAuthController.php

    public function mySubjects(Request $request)
    {
        // Récupérer le modèle Teacher lié à l'utilisateur connecté
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Cet utilisateur n’est pas un enseignant valide.'], 403);
        }

        // Récupérer les attributions avec les matières et classes
        $subjects = $teacher->teacherSubjects()->with(['subject', 'classe'])->get();

        // Formater la réponse
        $result = $subjects->map(function($ts) {
            return [
                'id' => $ts->id,
                'subject_id' => $ts->subject->id,
                'subject_name' => $ts->subject->name,
                'classe_id' => $ts->classe?->id,
                'classe_name' => $ts->classe?->name,
                'academic_year' => $ts->academic_year,
                'is_active' => $ts->is_active,
            ];
        });

        return response()->json($result);
    }
}
