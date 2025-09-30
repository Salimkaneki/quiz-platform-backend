# 📚 API Documentation - Actions Étudiants

## 🎯 Vue d'ensemble

Ce document détaille toutes les actions disponibles pour les étudiants dans la plateforme de quiz, avec les payloads JSON requis pour chaque endpoint.

## 🔐 Authentification

Toutes les routes étudiants nécessitent une authentification via **Bearer Token** (Sanctum).

```bash
Authorization: Bearer {token}
```

---

## 📋 1. AUTHENTIFICATION ÉTUDIANT

### 1.1 Connexion
**POST** `/api/student/auth/login`

**Payload requis :**
```json
{
  "email": "etudiant@ecole.com",
  "password": "mot_de_passe"
}
```

**Réponse :**
```json
{
  "user": {
    "id": 1,
    "name": "Jean Dupont",
    "email": "etudiant@ecole.com"
  },
  "token": "bearer_token_here",
  "token_type": "Bearer"
}
```

### 1.2 Informations personnelles
**GET** `/api/student/auth/me`

### 1.3 Déconnexion
**POST** `/api/student/auth/logout`

---

## 📅 2. GESTION DES SESSIONS DE QUIZ

### 2.1 Rejoindre une session
**POST** `/api/student/session/join`

**Payload requis :**
```json
{
  "session_code": "ABC123XY"
}
```

**Réponse de succès :**
```json
{
  "message": "Session rejointe avec succès",
  "session": {
    "id": 1,
    "title": "Quiz Mathématiques - Classe A",
    "quiz": {
      "id": 1,
      "title": "Algèbre Linéaire",
      "questions_count": 20,
      "shuffle_questions": true
    },
    "status": "active",
    "started_at": "2025-09-30 09:00:00",
    "ends_at": "2025-09-30 10:30:00"
  },
  "result_id": 123
}
```

**Erreurs possibles :**
- `404` : Session introuvable
- `400` : Session non disponible (terminée/annulée)
- `403` : Étudiant non autorisé
- `400` : Nombre maximum de participants atteint

### 2.2 Récupérer les questions d'une session
**GET** `/api/student/session/{sessionId}/questions`

**Réponse :**
```json
{
  "session": {
    "id": 1,
    "title": "Quiz Mathématiques",
    "status": "active",
    "starts_at": "2025-09-30 09:00:00",
    "ends_at": "2025-09-30 10:30:00",
    "duration_minutes": 60
  },
  "questions": [
    {
      "id": 1,
      "question_text": "Quelle est la capitale de la France ?",
      "type": "multiple_choice",
      "points": 2,
      "order": 1,
      "image_url": null,
      "time_limit": 30,
      "options": [
        {"id": 0, "text": "Paris"},
        {"id": 1, "text": "Lyon"},
        {"id": 2, "text": "Marseille"},
        {"id": 3, "text": "Toulouse"}
      ]
    }
  ],
  "total_questions": 20,
  "result_id": 123
}
```

### 2.3 Récupérer une question spécifique
**GET** `/api/student/session/{sessionId}/questions/{questionId}`

**Réponse :**
```json
{
  "question": {
    "id": 1,
    "question_text": "Quelle est la capitale de la France ?",
    "type": "multiple_choice",
    "points": 2,
    "order": 1,
    "image_url": null,
    "time_limit": 30,
    "options": [
      {"id": 0, "text": "Paris"},
      {"id": 1, "text": "Lyon"}
    ]
  },
  "has_answered": false,
  "student_answer": null,
  "result_id": 123
}
```

### 2.4 Voir la progression
**GET** `/api/student/session/{sessionId}/progress`

**Réponse :**
```json
{
  "progress": {
    "total_questions": 20,
    "answered_questions": 15,
    "remaining_questions": 5,
    "percentage_complete": 75.0,
    "is_completed": false,
    "time_elapsed": 45,
    "session_duration": 60
  },
  "result": {
    "id": 123,
    "status": "in_progress",
    "total_points": 28,
    "max_points": 40,
    "percentage": 70.0
  }
}
```

---

## ✏️ 3. SOUMISSION DES RÉPONSES

### 3.1 Soumettre des réponses
**POST** `/api/student/results/{resultId}/responses`

**Payload pour QCM :**
```json
{
  "responses": [
    {
      "question_id": 1,
      "answer": 0
    },
    {
      "question_id": 2,
      "answer": 1
    }
  ]
}
```

**Payload pour Vrai/Faux :**
```json
{
  "responses": [
    {
      "question_id": 3,
      "answer": "true"
    }
  ]
}
```

**Payload pour Question ouverte :**
```json
{
  "responses": [
    {
      "question_id": 4,
      "answer": "La réponse développée de l'étudiant..."
    }
  ]
}
```

**Payload pour Texte à trous :**
```json
{
  "responses": [
    {
      "question_id": 5,
      "answer": "mot_manquant"
    }
  ]
}
```

**Réponse de succès :**
```json
{
  "message": "Réponses soumises avec succès",
  "total_points": 28,
  "max_points": 40,
  "percentage": 70.0,
  "correct_answers": 14
}
```

### 3.2 Voir ses réponses
**GET** `/api/student/results/{resultId}/responses`

### 3.3 Voir une réponse spécifique
**GET** `/api/student/results/{resultId}/responses/{questionId}`

---

## 👤 4. PROFIL ÉTUDIANT

### 4.1 Voir son profil
**GET** `/api/student/profile`

### 4.2 Modifier son profil
**PUT** `/api/student/profile`

**Payload partiel :**
```json
{
  "name": "Jean Dupont",
  "phone": "+33123456789",
  "address": "123 Rue de l'École"
}
```

### 4.3 Changer mot de passe
**POST** `/api/student/profile/change-password`

**Payload :**
```json
{
  "current_password": "ancien_mot_de_passe",
  "password": "nouveau_mot_de_passe",
  "password_confirmation": "nouveau_mot_de_passe"
}
```

### 4.4 Uploader photo de profil
**POST** `/api/student/profile/picture`

**Content-Type :** `multipart/form-data`

**Payload :**
```
picture: [fichier image]
```

### 4.5 Supprimer photo de profil
**DELETE** `/api/student/profile/picture`

---

## 📊 5. TABLEAU DE BORD

### 5.1 Vue d'ensemble
**GET** `/api/student/dashboard`

**Réponse :**
```json
{
  "stats": {
    "total_quizzes_taken": 15,
    "average_score": 78.5,
    "completed_sessions": 12,
    "upcoming_sessions": 3
  },
  "recent_sessions": [...],
  "upcoming_sessions": [...],
  "performance_trends": [...]
}
```

---

## ⚠️ CODES D'ERREUR COURANTS

| Code | Signification |
|------|---------------|
| 400 | Requête invalide / Session terminée |
| 401 | Non authentifié |
| 403 | Non autorisé / Accès réservé étudiants |
| 404 | Session/Question introuvable |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## 🔄 WORKFLOWS TYPIQUES

### Workflow Participation à un Quiz :
1. **POST** `/auth/login` - Connexion étudiant
2. **POST** `/student/session/join` - Rejoindre avec code (ABC123XY)
3. **GET** `/student/session/{id}/questions` - Récupérer questions
4. **POST** `/student/results/{id}/responses` - Soumettre réponses
5. **GET** `/student/session/{id}/progress` - Suivre progression

### Workflow Consultation Résultats :
1. **GET** `/student/dashboard` - Vue d'ensemble
2. **GET** `/student/results/{id}/responses` - Voir ses réponses
3. **GET** `/student/session/{id}/progress` - Statistiques détaillées

---

## 📝 NOTES IMPORTANTES

- **Codes de session** : Format alphanumérique (ex: ABC123XY)
- **Authentification** : Toutes les routes nécessitent un token Bearer
- **Autorisation** : Un étudiant ne peut accéder qu'à ses propres données
- **Sessions** : Vérification automatique des droits d'accès
- **Réponses** : Validation automatique des formats selon le type de question
- **Progression** : Mise à jour automatique des statistiques
- **Temps** : Respect des limites de temps par question/session

---

## 🔧 TYPES DE QUESTIONS SUPPORTÉS

| Type | Format Réponse | Validation |
|------|----------------|------------|
| `multiple_choice` | `number` (index option) | Option existe |
| `true_false` | `"true"` ou `"false"` | Valeur booléenne |
| `open_ended` | `string` | Longueur max 5000 chars |
| `fill_blank` | `string` | Format libre |

---

## 📊 STATUTS POSSIBLES

### Session :
- `scheduled` : Programmée
- `active` : En cours
- `completed` : Terminée

### Résultat étudiant :
- `in_progress` : En cours
- `submitted` : Soumis
- `graded` : Corrigé
- `published` : Résultats publiés</content>
<parameter name="filePath">c:\Users\_Salim_mevtr_\_sout_\quiz-platform-backend\STUDENT_API_DOCUMENTATION.md