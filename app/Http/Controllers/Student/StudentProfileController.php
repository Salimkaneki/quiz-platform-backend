<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StudentProfileController extends Controller
{
    /**
     * Voir le profil de l'étudiant connecté
     * GET /api/student/profile
     */
    public function show()
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        return response()->json([
            'student' => $student->load(['user', 'class', 'class.formation']),
            'user' => $student->user,
            'class' => $student->class,
            'formation' => $student->class?->formation,
            'institution' => $student->class?->formation?->institution
        ]);
    }

    /**
     * Modifier le profil de l'étudiant
     * PUT /api/student/profile
     */
    public function update(Request $request)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date|before:today',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'emergency_contact' => 'sometimes|nullable|string|max:255',
            'emergency_phone' => 'sometimes|nullable|string|max:20',
            'medical_info' => 'sometimes|nullable|string|max:1000',
            'preferences' => 'sometimes|array',
            'preferences.theme' => 'sometimes|in:light,dark',
            'preferences.language' => 'sometimes|in:fr,en',
            'preferences.notifications' => 'sometimes|boolean'
        ]);

        // Mettre à jour les informations de l'utilisateur (User model)
        if ($request->has(['first_name', 'last_name'])) {
            $student->user->update([
                'name' => trim($request->first_name . ' ' . $request->last_name)
            ]);
        }

        // Mettre à jour les informations spécifiques à l'étudiant
        $studentData = $request->only([
            'first_name', 'last_name', 'date_of_birth', 'phone',
            'address', 'emergency_contact', 'emergency_phone',
            'medical_info', 'preferences'
        ]);

        $student->update($studentData);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'student' => $student->fresh()->load(['user', 'class', 'class.formation'])
        ]);
    }

    /**
     * Changer le mot de passe
     * POST /api/student/change-password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'Le mot de passe actuel est incorrect'
            ], 400);
        }

        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Le nouveau mot de passe doit être différent de l\'actuel'
            ], 400);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès'
        ]);
    }

    /**
     * Télécharger la photo de profil
     * POST /api/student/profile-picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('profile_picture')) {
            // Supprimer l'ancienne photo si elle existe
            if ($student->profile_picture && file_exists(public_path($student->profile_picture))) {
                unlink(public_path($student->profile_picture));
            }

            $file = $request->file('profile_picture');
            $filename = 'profile_' . $student->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('uploads/profiles'), $filename);

            $student->update([
                'profile_picture' => 'uploads/profiles/' . $filename
            ]);

            return response()->json([
                'message' => 'Photo de profil mise à jour',
                'profile_picture_url' => asset('uploads/profiles/' . $filename)
            ]);
        }

        return response()->json(['error' => 'Aucun fichier reçu'], 400);
    }

    /**
     * Supprimer la photo de profil
     * DELETE /api/student/profile-picture
     */
    public function deleteProfilePicture()
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Accès réservé aux étudiants'], 403);
        }

        if ($student->profile_picture && file_exists(public_path($student->profile_picture))) {
            unlink(public_path($student->profile_picture));
        }

        $student->update(['profile_picture' => null]);

        return response()->json([
            'message' => 'Photo de profil supprimée'
        ]);
    }
}