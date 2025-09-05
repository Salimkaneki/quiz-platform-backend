<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Administrator;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentController extends Controller
{
    /**
     * Liste des étudiants
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
     * Création d'un étudiant et son User associé
     */
    public function store(Request $request)
    {
        // Log des données reçues pour debugging
        Log::info('StudentController@store - Début', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        // Vérification des permissions
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            Log::warning('StudentController@store - Permission refusée', [
                'user_id' => auth()->id()
            ]);
            return $this->forbiddenResponse('Non autorisé.');
        }

        Log::info('StudentController@store - Permissions validées', [
            'admin_id' => $admin->id,
            'institution_id' => $admin->institution_id
        ]);

        // Validation avec gestion d'erreurs explicite
        try {
            $data = $request->validate([
                'student_number' => 'required|unique:students,student_number',
                'first_name'     => 'required|string|max:255',
                'last_name'      => 'required|string|max:255',
                'email'          => 'required|email|unique:students,email|unique:users,email',
                'birth_date'     => 'required|date',
                'phone'          => 'nullable|string|max:20',
                'class_id'       => 'required|exists:classes,id',
                'metadata'       => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('StudentController@store - Erreur de validation', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        Log::info('StudentController@store - Validation réussie', $data);

        // Vérification de la classe
        $classe = Classes::find($data['class_id']);
        if (!$classe) {
            Log::error('StudentController@store - Classe non trouvée', [
                'class_id' => $data['class_id']
            ]);
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        Log::info('StudentController@store - Classe trouvée', [
            'class_id' => $classe->id,
            'class_name' => $classe->name ?? 'N/A'
        ]);

        // Institution automatique depuis l'admin
        $data['institution_id'] = $admin->institution_id;

        // Transaction pour assurer la cohérence
        DB::beginTransaction();
        
        try {
            Log::info('StudentController@store - Début de la transaction');
            
            // Création du User avec mot de passe par défaut
            $defaultPassword = 'Motdepasse123';
            $userData = [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($defaultPassword),
                'account_type' => 'student',
                'is_active' => true
            ];

            Log::info('StudentController@store - Création du User', $userData);
            
            $user = User::create($userData);
            
            if (!$user) {
                throw new Exception('Échec de la création du User');
            }

            Log::info('StudentController@store - User créé', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Création de l'étudiant et liaison au User
            $studentData = array_merge($data, ['user_id' => $user->id]);
            
            Log::info('StudentController@store - Création du Student', $studentData);
            
            $student = Student::create($studentData);
            
            if (!$student) {
                throw new Exception('Échec de la création du Student');
            }

            Log::info('StudentController@store - Student créé', [
                'student_id' => $student->id,
                'student_number' => $student->student_number
            ]);

            // Commit de la transaction
            DB::commit();

            Log::info('StudentController@store - Transaction commitée avec succès');

            // Chargement des relations pour la réponse
            $student->load(['classe.formation', 'user']);

            $response = [
                'message' => 'Étudiant créé avec succès',
                'student' => $student,
                'user' => $user,
                'default_password' => $defaultPassword
            ];

            Log::info('StudentController@store - Réponse préparée', [
                'student_id' => $student->id,
                'user_id' => $user->id
            ]);

            return response()->json($response, 201);

        } catch (Exception $e) {
            // Rollback de la transaction
            DB::rollBack();
            
            Log::error('StudentController@store - Erreur lors de la création', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la création de l\'étudiant',
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
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

        return response()->json($student->load(['classe.formation', 'user']));
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
            'email'          => 'sometimes|email|unique:students,email,' . $student->id . '|unique:users,email,' . ($student->user_id ?? 0),
            'birth_date'     => 'sometimes|date',
            'phone'          => 'nullable|string|max:20',
            'class_id'       => 'sometimes|exists:classes,id',
            'is_active'      => 'boolean',
            'metadata'       => 'nullable|array',
        ]);

        DB::beginTransaction();
        
        try {
            $student->update($data);

            // Mettre à jour l'email et le nom du User associé si besoin
            if ($student->user) {
                $userUpdates = [];
                
                if (isset($data['email'])) {
                    $userUpdates['email'] = $data['email'];
                }
                
                if (isset($data['first_name']) || isset($data['last_name'])) {
                    $firstName = $data['first_name'] ?? $student->first_name;
                    $lastName = $data['last_name'] ?? $student->last_name;
                    $userUpdates['name'] = $firstName . ' ' . $lastName;
                }
                
                if (!empty($userUpdates)) {
                    $student->user->update($userUpdates);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Étudiant mis à jour avec succès',
                'data' => $student->fresh()->load(['classe.formation', 'user'])
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('StudentController@update - Erreur lors de la mise à jour', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'étudiant',
                'error' => $e->getMessage()
            ], 500);
        }
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

        DB::beginTransaction();
        
        try {
            // Supprimer d'abord l'étudiant, puis l'utilisateur
            $userId = $student->user_id;
            
            $student->delete();
            
            if ($userId) {
                User::find($userId)?->delete();
            }

            DB::commit();
            
            return response()->json(['message' => 'Étudiant supprimé']);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('StudentController@destroy - Erreur lors de la suppression', [
                'error' => $e->getMessage(),
                'student_id' => $student->id
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'étudiant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =================== Permissions ===================
    private function checkPedagogicalPermissions($institutionId = null)
    {
        $currentUser = auth()->user();
        if (!$currentUser) return null;

        $query = Administrator::where('user_id', $currentUser->id)
                              ->where('type', 'pedagogique');
        if ($institutionId) $query->where('institution_id', $institutionId);

        return $query->first();
    }

    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }

    /**
 * Récupérer les étudiants par classe
 */
public function getByClass(Request $request, $classId = null)
{
    $admin = $this->checkPedagogicalPermissions();
    if (!$admin) {
        return $this->forbiddenResponse('Non autorisé.');
    }

    // Si classId est fourni dans l'URL, on l'utilise, sinon on vérifie le paramètre de requête
    $classId = $classId ?? $request->input('class_id');
    
    if (!$classId) {
        return response()->json([
            'message' => 'Le paramètre class_id est requis'
        ], 400);
    }

    // Vérifier que la classe appartient à la même institution que l'admin
    // Modification : vérification via la relation formation->institution_id
    $classe = Classes::with('formation')
                    ->where('id', $classId)
                    ->whereHas('formation', function ($query) use ($admin) {
                        $query->where('institution_id', $admin->institution_id);
                    })
                    ->first();

    if (!$classe) {
        return response()->json([
            'message' => 'Classe non trouvée ou non autorisée'
        ], 404);
    }

    $query = Student::with(['user', 'classe.formation'])
                    ->where('class_id', $classId)
                    ->where('institution_id', $admin->institution_id);

    // Filtre par recherche si fourni
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('first_name', 'like', "%{$request->search}%")
              ->orWhere('last_name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%")
              ->orWhere('student_number', 'like', "%{$request->search}%");
        });
    }

    // Filtre par statut actif/inactif
    if ($request->has('is_active')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('is_active', $request->is_active);
        });
    }

    // Tri personnalisable
    $sortBy = $request->sort_by ?? 'last_name';
    $sortOrder = $request->sort_order ?? 'asc';
    $query->orderBy($sortBy, $sortOrder);

    // Pagination ou retour de tous les résultats
    if ($request->has('per_page')) {
        $perPage = $request->per_page ?? 15;
        $students = $query->paginate($perPage);
    } else {
        $students = $query->get();
    }

    return response()->json([
        'class' => $classe,
        'students' => $students
    ]);
}

/**
 * Récupérer les étudiants par formation (toutes les classes d'une formation)
 */
public function getByFormation(Request $request, $formationId)
{
    $admin = $this->checkPedagogicalPermissions();
    if (!$admin) {
        return $this->forbiddenResponse('Non autorisé.');
    }

    // Vérifier d'abord que la formation appartient à l'institution
    $formation = \App\Models\Formation::where('id', $formationId)
                                     ->where('institution_id', $admin->institution_id)
                                     ->first();

    if (!$formation) {
        return response()->json([
            'message' => 'Formation non trouvée ou non autorisée'
        ], 404);
    }

    // Récupérer les classes de la formation
    $classes = Classes::where('formation_id', $formationId)
                     ->pluck('id');

    if ($classes->isEmpty()) {
        return response()->json([
            'message' => 'Aucune classe trouvée pour cette formation'
        ], 404);
    }

    $query = Student::with(['user', 'classe.formation'])
                    ->whereIn('class_id', $classes)
                    ->where('institution_id', $admin->institution_id);

    // Filtres (même logique que getByClass)
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('first_name', 'like', "%{$request->search}%")
              ->orWhere('last_name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%")
              ->orWhere('student_number', 'like', "%{$request->search}%");
        });
    }

    if ($request->has('is_active')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('is_active', $request->is_active);
        });
    }

    $sortBy = $request->sort_by ?? 'last_name';
    $sortOrder = $request->sort_order ?? 'asc';
    $query->orderBy($sortBy, $sortOrder);

    if ($request->has('per_page')) {
        $perPage = $request->per_page ?? 15;
        $students = $query->paginate($perPage);
    } else {
        $students = $query->get();
    }

    return response()->json([
        'formation_id' => $formationId,
        'students_count' => $students->count(),
        'students' => $students
    ]);
}
}