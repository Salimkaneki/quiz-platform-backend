# 📚 API Documentation - Actions Enseignants

## 🎯 Vue d'ensemble

Ce document détaille toutes les actions disponibles pour les enseignants dans la plateforme de quiz, avec les payloads JSON requis pour chaque endpoint.

## 🔐 Authentification

Toutes les routes enseignants nécessitent une authentification via **Bearer Token** (Sanctum).

```bash
Authorization: Bearer {token}
```

---

## 📋 1. GESTION DES QUIZ

### 1.1 Lister les quiz
**GET** `/api/teacher/quizzes`

**Query Parameters (optionnels) :**
```json
{
  "status": "published|draft",
  "subject_id": 1,
  "search": "mathématiques",
  "per_page": 15
}
```

**Réponse :**
```json
{
  "quizzes": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### 1.2 Créer un quiz
**POST** `/api/teacher/quizzes`

**Payload requis :**
```json
{
  "title": "Quiz Mathématiques - Algèbre",
  "description": "Test sur les équations du second degré",
  "subject_id": 1,
  "duration_minutes": 60,
  "total_points": 20,
  "shuffle_questions": true,
  "show_results_immediately": false,
  "allow_review": true,
  "status": "draft",
  "settings": {
    "show_correct_answers": true,
    "allow_multiple_attempts": false
  }
}
```

### 1.3 Voir un quiz
**GET** `/api/teacher/quizzes/{quizId}`

**Réponse :** Quiz complet avec questions et sujet

### 1.4 Modifier un quiz
**PUT** `/api/teacher/quizzes/{quizId}`

**Payload partiel :**
```json
{
  "title": "Quiz Mathématiques - Niveau Avancé",
  "status": "published",
  "duration_minutes": 90
}
```

### 1.5 Supprimer un quiz
**DELETE** `/api/teacher/quizzes/{quizId}`

---

## ❓ 2. GESTION DES QUESTIONS

### 2.1 Lister les questions d'un quiz
**GET** `/api/teacher/quizzes/{quizId}/questions`

### 2.2 Créer une question
**POST** `/api/teacher/quizzes/{quizId}/questions`

**Payload pour QCM :**
```json
{
  "question_text": "Quelle est la capitale de la France ?",
  "type": "multiple_choice",
  "options": [
    {"text": "Paris", "is_correct": true},
    {"text": "Lyon", "is_correct": false},
    {"text": "Marseille", "is_correct": false},
    {"text": "Toulouse", "is_correct": false}
  ],
  "points": 2,
  "order": 1,
  "explanation": "Paris est la capitale et la plus grande ville de France.",
  "time_limit": 30
}
```

**Payload pour Vrai/Faux :**
```json
{
  "question_text": "La Terre est plate.",
  "type": "true_false",
  "correct_answer": "false",
  "points": 1,
  "explanation": "La Terre est ronde selon la théorie scientifique."
}
```

**Payload pour Question ouverte :**
```json
{
  "question_text": "Expliquez le théorème de Pythagore.",
  "type": "open_ended",
  "points": 5,
  "time_limit": 300
}
```

### 2.3 Créer plusieurs questions (Batch)
**POST** `/api/teacher/quizzes/{quizId}/questions/batch`

**Payload :**
```json
{
  "questions": [
    {
      "question_text": "Question 1",
      "type": "multiple_choice",
      "options": [...],
      "points": 2
    },
    {
      "question_text": "Question 2",
      "type": "true_false",
      "correct_answer": "true",
      "points": 1
    }
  ]
}
```

### 2.4 Modifier une question
**PUT** `/api/teacher/quizzes/{quizId}/questions/{questionId}`

**Payload partiel :**
```json
{
  "question_text": "Question modifiée",
  "points": 3,
  "time_limit": 45
}
```

### 2.5 Supprimer une question
**DELETE** `/api/teacher/quizzes/{quizId}/questions/{questionId}`

---

## 📅 3. GESTION DES SESSIONS DE QUIZ

### 3.1 Lister les sessions
**GET** `/api/teacher/sessions`

**Query Parameters :**
```json
{
  "status": "scheduled|active|completed",
  "quiz_id": 1,
  "per_page": 15
}
```

### 3.2 Créer une session
**POST** `/api/teacher/sessions`

**Payload requis :**
```json
{
  "quiz_id": 1,
  "title": "Session Mathématiques - Classe A",
  "starts_at": "2025-09-30 09:00:00",
  "ends_at": "2025-09-30 10:30:00",
  "max_participants": 30,
  "require_student_list": true,
  "allowed_students": [1, 2, 3, 4, 5],
  "settings": {
    "allow_late_join": false,
    "show_progress": true
  }
}
```

### 3.3 Voir une session
**GET** `/api/teacher/sessions/{sessionId}`

### 3.4 Modifier une session
**PUT** `/api/teacher/sessions/{sessionId}`

**Payload partiel :**
```json
{
  "title": "Session modifiée",
  "starts_at": "2025-09-30 10:00:00",
  "ends_at": "2025-09-30 11:00:00",
  "max_participants": 25
}
```

### 3.5 Activer une session
**PATCH** `/api/teacher/sessions/{sessionId}/activate`

### 3.6 Terminer une session
**PATCH** `/api/teacher/sessions/{sessionId}/complete`

### 3.7 Supprimer une session
**DELETE** `/api/teacher/sessions/{sessionId}`

---

## 📊 4. GESTION DES RÉSULTATS

### 4.1 Voir les résultats d'une session
**GET** `/api/teacher/quiz-sessions/{sessionId}/results`


### 4.2 Voir un résultat spécifique
**GET** `/api/teacher/results/{resultId}`

### 4.3 Modifier un résultat
**PUT** `/api/teacher/results/{resultId}`

**Payload :**
```json
{
  "score": 85.5,
  "status": "graded",
  "feedback": "Bon travail, quelques erreurs de calcul.",
  "graded_at": "2025-09-30 11:00:00"
}
```

### 4.4 Modifier une réponse spécifique
**PUT** `/api/teacher/results/{resultId}/responses/{responseId}`

**Payload :**
```json
{
  "score": 2,
  "feedback": "Réponse correcte",
  "is_correct": true
}
```

### 4.5 Marquer comme corrigé
**POST** `/api/teacher/results/{resultId}/mark-graded`

### 4.6 Publier les résultats
**POST** `/api/teacher/results/{resultId}/publish`

### 4.7 Voir tous les résultats d'un quiz
**GET** `/api/teacher/quiz/{quizId}/results`

---

## 📈 5. HISTORIQUE ET STATISTIQUES

### 5.1 Historique général
**GET** `/api/teacher/history`

### 5.2 Historique des quiz
**GET** `/api/teacher/history/quizzes`

### 5.3 Historique des sessions
**GET** `/api/teacher/history/sessions`

### 5.4 Historique des résultats
**GET** `/api/teacher/history/results`

---

## 🔧 6. OUTILS DE MAINTENANCE

### 6.1 Détecter les doublons de sessions
**GET** `/api/teacher/sessions/duplicates`

### 6.2 Nettoyer les doublons
**POST** `/api/teacher/sessions/clean-duplicates`

---

## 📋 7. PROFIL ENSEIGNANT

### 7.1 Informations personnelles
**GET** `/api/teacher/me`

### 7.2 Mes matières
**GET** `/api/teacher/my-subjects`

### 7.3 Changer mot de passe
**POST** `/api/teacher/change-password`

**Payload :**
```json
{
  "current_password": "ancien_mot_de_passe",
  "password": "nouveau_mot_de_passe",
  "password_confirmation": "nouveau_mot_de_passe"
}
```

---

## ⚠️ CODES D'ERREUR COURANTS

| Code | Signification |
|------|---------------|
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé |
| 404 | Ressource non trouvée |
| 409 | Conflit (doublon, session active) |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## 🔄 WORKFLOWS TYPIQUES

### Workflow Création Quiz :
1. **POST** `/quizzes` - Créer quiz (draft)
2. **POST** `/quizzes/{id}/questions` - Ajouter questions
3. **PUT** `/quizzes/{id}` - Publier quiz (status: published)

### Workflow Session :
1. **POST** `/sessions` - Créer session (scheduled)
2. **PATCH** `/sessions/{id}/activate` - Activer session
3. **PATCH** `/sessions/{id}/complete` - Terminer session
4. **GET** `/quiz-sessions/{id}/results` - Consulter résultats

### Workflow Correction :
1. **GET** `/results/{id}` - Voir résultat
2. **PUT** `/results/{id}/responses/{responseId}` - Corriger réponse
3. **POST** `/results/{id}/mark-graded` - Marquer comme corrigé
4. **POST** `/results/{id}/publish` - Publier résultats

---

## 📝 NOTES IMPORTANTES

- **Authentification** : Toutes les routes nécessitent un token Bearer
- **Autorisation** : Un enseignant ne peut accéder qu'à ses propres ressources
- **Validation** : Les dates doivent être dans le futur, les étudiants doivent exister
- **Conflits** : Vérification automatique des conflits d'horaires
- **Statuts** : `scheduled` → `active` → `completed`
- **Pagination** : Utilisez `per_page` pour contrôler le nombre de résultats</content>
<parameter name="filePath">c:\Users\_Salim_mevtr_\_sout_\quiz-platform-backend\TEACHER_API_DOCUMENTATION.md