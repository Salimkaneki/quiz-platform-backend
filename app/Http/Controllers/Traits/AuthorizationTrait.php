<?php

namespace App\Http\Controllers\Traits;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

trait AuthorizationTrait
{
    /**
     * Récupère l'enseignant connecté avec vérification
     */
    protected function getAuthenticatedTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(response()->json([
                'error' => 'Accès réservé aux enseignants',
                'message' => 'Vous devez être connecté en tant qu\'enseignant'
            ], 403));
        }

        return $teacher;
    }

    /**
     * Vérifie que l'enseignant est propriétaire de la ressource
     */
    protected function authorizeTeacherResource($resource, string $resourceName = 'ressource'): void
    {
        $teacher = $this->getAuthenticatedTeacher();

        if ($resource->teacher_id !== $teacher->user_id) {
            abort(response()->json([
                'error' => 'Accès non autorisé',
                'message' => "Vous n'êtes pas autorisé à accéder à cette {$resourceName}"
            ], 403));
        }
    }

    /**
     * Valide que les étudiants appartiennent à la même institution
     */
    protected function validateStudentsInstitution(array $studentIds, Teacher $teacher): void
    {
        if (empty($studentIds)) {
            return;
        }

        $validStudents = \App\Models\Student::whereIn('id', $studentIds)
            ->where('is_active', true)
            ->where('institution_id', $teacher->institution_id)
            ->get();

        if ($validStudents->count() !== count($studentIds)) {
            $foundIds = $validStudents->pluck('id')->toArray();
            $missingIds = array_diff($studentIds, $foundIds);

            abort(response()->json([
                'error' => 'Étudiants invalides',
                'message' => 'Les étudiants suivants sont introuvables ou n\'appartiennent pas à votre institution: ' . implode(', ', $missingIds)
            ], 422));
        }
    }

    /**
     * Vérifie les conflits d'horaires pour les sessions
     */
    protected function checkScheduleConflicts(Teacher $teacher, $startsAt, $endsAt, $excludeSessionId = null): void
    {
        $query = \App\Models\QuizSession::where('teacher_id', $teacher->user_id)
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->whereBetween('starts_at', [$startsAt, $endsAt])
                  ->orWhereBetween('ends_at', [$startsAt, $endsAt])
                  ->orWhere(function ($q2) use ($startsAt, $endsAt) {
                      $q2->where('starts_at', '<=', $startsAt)
                         ->where('ends_at', '>=', $endsAt);
                  });
            })
            ->where('status', '!=', 'completed');

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $conflictingSession = $query->first();

        if ($conflictingSession) {
            abort(response()->json([
                'error' => 'Conflit d\'horaire',
                'message' => "Vous avez déjà une session planifiée '{$conflictingSession->title}' " .
                           "du {$conflictingSession->starts_at->format('d/m/Y H:i')} " .
                           "au {$conflictingSession->ends_at->format('d/m/Y H:i')}",
                'conflicting_session' => [
                    'id' => $conflictingSession->id,
                    'title' => $conflictingSession->title,
                    'starts_at' => $conflictingSession->starts_at,
                    'ends_at' => $conflictingSession->ends_at
                ]
            ], 422));
        }
    }
}