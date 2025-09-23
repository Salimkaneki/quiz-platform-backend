<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Student, 
    Teacher, 
    Formation, 
    Classes,
    Subject,
    Administrator,
    User
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Récupère toutes les données du dashboard admin
     */
    public function index()
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Accès réservé aux administrateurs pédagogiques');
        }

        $institutionId = $admin->institution_id;

        try {
            // Récupération en parallèle pour optimiser les performances
            $data = [
                'kpis' => $this->getKPIs($institutionId),
                'metrics' => $this->getPerformanceMetrics($institutionId),
                'recent_events' => $this->getRecentEvents($institutionId),
                'quick_stats' => $this->getQuickStats($institutionId),
                'charts_data' => $this->getChartsData($institutionId)
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('DashboardController error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du chargement du dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * KPIs principaux
     */
    private function getKPIs($institutionId)
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        // Comptages actuels
        $currentTeachers = Teacher::where('institution_id', $institutionId)->count();
        $currentStudents = Student::where('institution_id', $institutionId)->count();
        $activeFormations = Formation::where('institution_id', $institutionId)
            ->where('is_active', true)
            ->count();

        // Comptages du mois précédent pour les tendances
        $previousTeachers = Teacher::where('institution_id', $institutionId)
            ->where('created_at', '<', $currentMonth)
            ->count();
        
        $previousStudents = Student::where('institution_id', $institutionId)
            ->where('created_at', '<', $currentMonth)
            ->count();

        return [
            [
                'label' => 'Personnel Académique',
                'value' => $currentTeachers,
                'period' => 'vs mois précédent',
                'trend' => $this->calculateTrend($previousTeachers, $currentTeachers)
            ],
            [
                'label' => 'Programmes Actifs',
                'value' => $activeFormations,
                'period' => 'vs mois précédent',
                'trend' => 'stable' // À implémenter selon votre logique métier
            ],
            [
                'label' => 'Effectif Étudiant',
                'value' => $currentStudents,
                'period' => 'vs mois précédent',
                'trend' => $this->calculateTrend($previousStudents, $currentStudents)
            ],
            [
                'label' => 'Taux d\'Occupation',
                'value' => $this->calculateOccupationRate($institutionId),
                'period' => 'vs mois précédent',
                'trend' => $this->calculateOccupationTrend($institutionId)
            ]
        ];
    }

    /**
     * Métriques de performance détaillées
     */
    private function getPerformanceMetrics($institutionId)
    {
        // FIXME: Implémenter le calcul réel basé sur vos données
        // Ces valeurs sont des exemples - remplacez par vos calculs métier
        
        $teacherStudentRatio = $this->calculateTeacherStudentRatio($institutionId);
        $budgetExecution = $this->calculateBudgetExecution($institutionId);
        
        return [
            ['label' => 'Taux de réussite global', 'value' => '94.2%', 'change' => '+1.8', 'unit' => 'pt'],
            ['label' => 'Note satisfaction (NPS)', 'value' => '72', 'change' => '+5', 'unit' => ''],
            ['label' => 'Ratio encadrement', 'value' => $teacherStudentRatio, 'change' => '-0.3', 'unit' => ''],
            ['label' => 'Budget exécuté', 'value' => $budgetExecution . '%', 'change' => '+3.2', 'unit' => 'pt'],
            ['label' => 'Publications scientifiques', 'value' => '157', 'change' => '+12', 'unit' => ''],
            ['label' => 'Partenariats actifs', 'value' => '23', 'change' => '+2', 'unit' => '']
        ];
    }

    /**
     * Événements récents/agenda
     */
    private function getRecentEvents($institutionId)
    {
        // TODO: Remplacer par votre modèle Event/Calendar réel
        return [
            [
                'date' => now()->format('Y-m-d'),
                'time' => '09:30',
                'title' => 'Conseil d\'administration',
                'location' => 'Salle du conseil',
                'status' => 'En cours'
            ],
            [
                'date' => now()->addDay()->format('Y-m-d'),
                'time' => '14:00',
                'title' => 'Commission pédagogique',
                'location' => 'Amphi A',
                'status' => 'Programmé'
            ],
            [
                'date' => now()->addDays(2)->format('Y-m-d'),
                'time' => '10:00',
                'title' => 'Réunion direction',
                'location' => 'Bureau direction',
                'status' => 'Programmé'
            ]
        ];
    }

    /**
     * Statistiques rapides pour les cartes d'action
     */
    private function getQuickStats($institutionId)
    {
        return [
            'total_teachers' => Teacher::where('institution_id', $institutionId)->count(),
            'total_students' => Student::where('institution_id', $institutionId)->count(),
            'active_formations' => Formation::where('institution_id', $institutionId)
                ->where('is_active', true)
                ->count(),
            'total_classes' => $this->getClassCount($institutionId),
            'total_subjects' => Subject::whereHas('formation', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->count()
        ];
    }

    /**
     * Données pour les graphiques
     */
    private function getChartsData($institutionId)
    {
        return [
            'student_evolution' => $this->getStudentEvolutionChart($institutionId),
            'formation_distribution' => $this->getFormationDistribution($institutionId),
            'teacher_grade_distribution' => $this->getTeacherGradeDistribution($institutionId)
        ];
    }

    /**
     * Évolution des effectifs étudiants sur 6 mois
     */
    private function getStudentEvolutionChart($institutionId)
    {
        $data = [];
        $runningTotal = Student::where('institution_id', $institutionId)
            ->where('created_at', '<', now()->subMonths(5)->startOfMonth())
            ->count();
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            
            // Nouveaux étudiants ce mois-ci
            $newStudents = Student::where('institution_id', $institutionId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $runningTotal += $newStudents;
            
            $data[] = [
                'month' => $month->format('M Y'),
                'new_students' => $newStudents,
                'total_students' => $runningTotal
            ];
        }

        return $data;
    }

    /**
     * Répartition des étudiants par formation
     */
    private function getFormationDistribution($institutionId)
    {
        try {
            // Méthode sans withCount pour éviter l'ambiguïté
            return Formation::where('institution_id', $institutionId)
                ->with(['classes' => function($query) {
                    $query->with(['students' => function($subQuery) {
                        $subQuery->where('students.is_active', true);
                    }]);
                }])
                ->get()
                ->map(function($formation) {
                    $studentCount = $formation->classes->sum(function($class) {
                        return $class->students->count();
                    });

                    return [
                        'formation' => $formation->name,
                        'code' => $formation->code ?? 'N/A',
                        'count' => $studentCount,
                        'percentage' => 0 // Calculé côté frontend
                    ];
                })
                ->filter(function($formation) {
                    return $formation['count'] > 0;
                })
                ->values();

        } catch (\Exception $e) {
            \Log::error('Formation distribution error: ' . $e->getMessage());
            
            // Fallback avec requête directe
            return collect(DB::select("
                SELECT 
                    f.name as formation,
                    f.code,
                    COUNT(s.id) as count
                FROM formations f
                LEFT JOIN classes c ON c.formation_id = f.id  
                LEFT JOIN students s ON s.class_id = c.id AND s.is_active = true
                WHERE f.institution_id = ?
                GROUP BY f.id, f.name, f.code
                HAVING COUNT(s.id) > 0
                ORDER BY count DESC
            ", [$institutionId]))->map(function($item) {
                return [
                    'formation' => $item->formation,
                    'code' => $item->code ?? 'N/A',
                    'count' => (int)$item->count,
                    'percentage' => 0
                ];
            })->toArray();
        }
    }

    /**
     * Répartition des enseignants par grade
     */
    private function getTeacherGradeDistribution($institutionId)
    {
        return Teacher::where('institution_id', $institutionId)
            ->select('grade', DB::raw('count(*) as count'))
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'grade' => $item->grade ?: 'Non spécifié',
                    'count' => $item->count
                ];
            });
    }

    /**
     * Calcul du taux d'occupation
     */
    private function calculateOccupationRate($institutionId)
    {
        // FIXME: Adapter selon votre modèle de données
        // Utiliser le bon nom de modèle selon votre choix
        $classModel = class_exists('App\Models\Classe') ? 'App\Models\Classe' : 'App\Models\Classes';
        $totalCapacity = $classModel::whereHas('formation', function($q) use ($institutionId) {
            $q->where('institution_id', $institutionId);
        })->sum('max_students') ?: 1; // Éviter division par zéro
        
        $currentStudents = Student::where('institution_id', $institutionId)
            ->where('is_active', true)
            ->count();
        
        return round(($currentStudents / $totalCapacity) * 100, 1) . '%';
    }

    /**
     * Tendance du taux d'occupation
     */
    private function calculateOccupationTrend($institutionId)
    {
        // Logique simplifiée - à adapter selon vos besoins
        $currentRate = (float) str_replace('%', '', $this->calculateOccupationRate($institutionId));
        return $currentRate > 75 ? 'positive' : ($currentRate > 50 ? 'stable' : 'negative');
    }

    /**
     * Calcul du ratio enseignant/étudiant
     */
    private function calculateTeacherStudentRatio($institutionId)
    {
        $teacherCount = Teacher::where('institution_id', $institutionId)->count();
        $studentCount = Student::where('institution_id', $institutionId)->count();
        
        if ($teacherCount == 0) return '0:0';
        
        $ratio = round($studentCount / $teacherCount, 1);
        return "1:{$ratio}";
    }

    /**
     * Obtenir le nombre de classes pour l'institution
     */
    private function getClassCount($institutionId)
    {
        // Support pour les deux noms de modèles
        if (class_exists('App\Models\Classe')) {
            return \App\Models\Classe::whereHas('formation', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->count();
        } else {
            return Classes::whereHas('formation', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })->count();
        }
    }

    /**
     * Calcul d'exécution budgétaire (exemple)
     */
    private function calculateBudgetExecution($institutionId)
    {
        // TODO: Implémenter selon votre modèle budgétaire
        return 78.4; // Valeur d'exemple
    }

    /**
     * Calcul de tendance entre deux périodes
     */
    private function calculateTrend($previous, $current)
    {
        if ($previous == 0 && $current == 0) return 'stable';
        if ($previous == 0) return 'positive';
        
        $percentChange = (($current - $previous) / $previous) * 100;
        
        if (abs($percentChange) < 2) return 'stable';
        return ($percentChange > 0) ? 'positive' : 'negative';
    }

    /**
     * Vérification des permissions
     */
    private function checkPedagogicalPermissions()
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->account_type !== 'admin') {
            return null;
        }

        return Administrator::where('user_id', $currentUser->id)
            ->where('type', 'pedagogique')
            ->first();
    }

    /**
     * Réponse d'interdiction
     */
    private function forbiddenResponse($message)
    {
        return response()->json(['message' => $message], 403);
    }

    /**
     * Endpoint pour les données de graphique spécifique
     */
    public function chartData(Request $request, $chartType)
    {
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return $this->forbiddenResponse('Non autorisé');
        }

        $institutionId = $admin->institution_id;

        try {
            switch ($chartType) {
                case 'student-evolution':
                    $data = $this->getStudentEvolutionChart($institutionId);
                    break;
                case 'formation-distribution':
                    $data = $this->getFormationDistribution($institutionId);
                    break;
                case 'teacher-grade-distribution':
                    $data = $this->getTeacherGradeDistribution($institutionId);
                    break;
                default:
                    return response()->json(['message' => 'Type de graphique non supporté'], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error("Chart data error for {$chartType}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données du graphique'
            ], 500);
        }
    }
}