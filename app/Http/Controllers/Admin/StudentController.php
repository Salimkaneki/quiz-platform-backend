<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Administrator;
use App\Models\Classes; // Ajout de l'import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Pour le debugging

class StudentController extends Controller
{
    /**
     * Liste des étudiants de l'institution de l'admin
     */
    public function index(Request $request)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé.');
        }

        $query = Student::with(['classe.formation'])
                        ->where('institution_id', $admin->institution_id);

        if ($request->search) {
            // recherche par nom ou email
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('student_number', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Création d'un étudiant
     */
    public function store(Request $request)
    {
        // Log pour debugging
        Log::info('StudentController store method called', $request->all());

        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé.');
        }

        // Validation avec des messages d'erreur personnalisés
        try {
            $data = $request->validate([
                'student_number' => 'required|unique:students,student_number',
                'first_name'     => 'required|string|max:255',
                'last_name'      => 'required|string|max:255',
                'email'          => 'required|email|unique:students,email',
                'birth_date'     => 'required|date',
                'phone'          => 'nullable|string|max:20',
                'class_id'       => 'required|exists:classes,id',
                'metadata'       => 'nullable|array',
            ], [
                'student_number.required' => 'Le numéro étudiant est requis.',
                'student_number.unique' => 'Ce numéro étudiant existe déjà.',
                'first_name.required' => 'Le prénom est requis.',
                'last_name.required' => 'Le nom est requis.',
                'email.required' => 'L\'email est requis.',
                'email.email' => 'L\'email doit être valide.',
                'email.unique' => 'Cet email existe déjà.',
                'birth_date.required' => 'La date de naissance est requise.',
                'birth_date.date' => 'La date de naissance doit être valide.',
                'class_id.required' => 'La classe est requise.',
                'class_id.exists' => 'Cette classe n\'existe pas.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        }

        // Vérifier que la classe appartient à la même institution
        $classe = Classes::find($data['class_id']);
        if (!$classe) {
            return response()->json([
                'message' => 'Classe non trouvée'
            ], 404);
        }

        // Vérifier que la classe a de l'espace (optionnel)
        if (!$classe->hasSpace()) {
            return response()->json([
                'message' => 'La classe est pleine'
            ], 400);
        }

        // Institution automatiquement depuis l'admin
        $data['institution_id'] = $admin->institution_id;

        try {
            $student = Student::create($data);
            
            Log::info('Student created successfully', ['student_id' => $student->id]);
            
            return response()->json([
                'message' => 'Étudiant créé avec succès',
                'data' => $student->load(['classe.formation'])
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la création de l\'étudiant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affichage d'un étudiant
     */
    public function show(Student $student)
    {
        $admin = $this->checkPedagogicalPermissions($student->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé.');
        }

        return response()->json($student->load(['classe.formation']));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Student $student)
    {
        $admin = $this->checkPedagogicalPermissions($student->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé.');
        }

        $data = $request->validate([
            'student_number' => 'sometimes|unique:students,student_number,' . $student->id,
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:students,email,' . $student->id,
            'birth_date'     => 'sometimes|date',
            'phone'          => 'nullable|string|max:20',
            'class_id'       => 'sometimes|exists:classes,id',
            'is_active'      => 'boolean',
            'metadata'       => 'nullable|array',
        ]);

        $student->update($data);

        return response()->json([
            'message' => 'Étudiant mis à jour avec succès',
            'data' => $student->fresh()->load(['classe.formation'])
        ]);
    }

    /**
     * Suppression
     */
    public function destroy(Student $student)
    {
        $admin = $this->checkPedagogicalPermissions($student->institution_id);
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé.');
        }

        $student->delete();
        return response()->json(['message' => 'Étudiant supprimé']);
    }

    // =================== Permissions ===================
    private function checkPedagogicalPermissions($institutionId = null)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser) {
            Log::warning('No authenticated user found');
            return null;
        }

        $query = Administrator::where('user_id', $currentUser->id)
                              ->where('type', 'pedagogique');

        if ($institutionId) {
            $query->where('institution_id', $institutionId);
        }

        $admin = $query->first();
        
        if (!$admin) {
            Log::warning('No pedagogical administrator found for user', ['user_id' => $currentUser->id]);
        }

        return $admin;
    }

    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }
}