# 📚 **COLLECTION API - PLATEFORME DE QUIZ**
## **Université de Lomé - Quiz Platform Backend**

*Date: Octobre 2025*
*Version: 1.0*

---

## 📋 **TABLE DES MATIÈRES**

### 🔓 **ROUTES PUBLIQUES**
- [Institutions](#institutions)
- [Users](#users)
- [Administrators](#administrators)
- [Teachers (Public)](#teachers-public)

### 🔐 **ADMINISTRATION**
- [Authentification Admin](#admin-auth)
- [Gestion des Enseignants](#admin-teachers)
- [Gestion des Formations](#admin-formations)
- [Gestion des Matières](#admin-subjects)
- [Gestion des Classes](#admin-classes)
- [Gestion des Étudiants](#admin-students)
- [Attributions Enseignant-Matière](#admin-teacher-subjects)
- [Gestion des Quiz (Vue Admin)](#admin-quizzes)
- [Dashboard Admin](#admin-dashboard)

### 👨‍🏫 **ENSEIGNANTS**
- [Authentification Enseignant](#teacher-auth)
- [Gestion des Quiz](#teacher-quizzes)
- [Gestion des Sessions de Quiz](#teacher-sessions)
- [Gestion des Questions](#teacher-questions)
- [Gestion des Résultats](#teacher-results)
- [Historique Enseignant](#teacher-history)

### 👨‍🎓 **ÉTUDIANTS**
- [Authentification Étudiant](#student-auth)
- [Sessions de Quiz](#student-sessions)
- [Profil Étudiant](#student-profile)
- [Tableau de Bord](#student-dashboard)
- [Réponses aux Quiz](#student-responses)

---

## 🔓 **ROUTES PUBLIQUES**

### 📚 **INSTITUTIONS**
**Base URL:** `/api/institutions`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste toutes les institutions | ❌ Public |
| `POST` | `/` | Créer une institution | ❌ Public |
| `GET` | `/{id}` | Détails d'une institution | ❌ Public |
| `PUT` | `/{id}` | Modifier une institution | ❌ Public |
| `DELETE` | `/{id}` | Supprimer une institution | ❌ Public |

### 👥 **USERS**
**Base URL:** `/api/users`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste tous les utilisateurs | ❌ Public |
| `POST` | `/` | Créer un utilisateur | ❌ Public |
| `GET` | `/{user}` | Détails d'un utilisateur | ❌ Public |
| `PUT` | `/{user}` | Modifier un utilisateur | ❌ Public |
| `PATCH` | `/{user}` | Modifier partiellement un utilisateur | ❌ Public |
| `DELETE` | `/{user}` | Supprimer un utilisateur | ❌ Public |
| `GET` | `/account-type/{accountType}` | Utilisateurs par type de compte | ❌ Public |

### 👔 **ADMINISTRATORS**
**Base URL:** `/api/administrators`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste tous les administrateurs | ❌ Public |
| `POST` | `/` | Créer un administrateur | ❌ Public |
| `GET` | `/{administrator}` | Détails d'un administrateur | ❌ Public |
| `PUT` | `/{administrator}` | Modifier un administrateur | ❌ Public |
| `PATCH` | `/{administrator}` | Modifier partiellement un administrateur | ❌ Public |
| `DELETE` | `/{administrator}` | Supprimer un administrateur | ❌ Public |
| `GET` | `/institution/{institutionId}` | Administrateurs par institution | ❌ Public |
| `GET` | `/type/{type}` | Administrateurs par type | ❌ Public |

### 👨‍🏫 **TEACHERS (PUBLIC)**
**Base URL:** `/api/teachers`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste tous les enseignants | ❌ Public |
| `POST` | `/` | Créer un enseignant | ❌ Public |
| `GET` | `/{teacher}` | Détails d'un enseignant | ❌ Public |
| `PUT` | `/{teacher}` | Modifier un enseignant | ❌ Public |
| `PATCH` | `/{teacher}` | Modifier partiellement un enseignant | ❌ Public |
| `DELETE` | `/{teacher}` | Supprimer un enseignant | ❌ Public |
| `GET` | `/permanent` | Enseignants permanents | ❌ Public |
| `GET` | `/grade/{grade}` | Enseignants par grade | ❌ Public |
| `GET` | `/my-institution` | Enseignants de mon institution | ❌ Public |

---

## 🔐 **ADMINISTRATION**

### 🔑 **ADMIN AUTH**
**Base URL:** `/api/admin`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `POST` | `/login` | Connexion administrateur | ❌ Public |
| `POST` | `/logout` | Déconnexion administrateur | ✅ Sanctum |
| `GET` | `/me` | Informations de l'admin connecté | ✅ Sanctum |

### 👨‍🏫 **ADMIN TEACHERS**
**Base URL:** `/api/admin/teachers`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des enseignants | ✅ Admin |
| `POST` | `/` | Créer un enseignant | ✅ Admin |
| `GET` | `/{teacher}` | Détails d'un enseignant | ✅ Admin |
| `PUT` | `/{teacher}` | Modifier un enseignant | ✅ Admin |
| `DELETE` | `/{teacher}` | Supprimer un enseignant | ✅ Admin |
| `GET` | `/users` | Utilisateurs disponibles pour enseignants | ✅ Admin |
| `GET` | `/with-subjects` | Enseignants avec leurs matières | ✅ Admin |

### 🎓 **ADMIN FORMATIONS**
**Base URL:** `/api/admin/formations`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des formations | ✅ Admin |
| `POST` | `/` | Créer une formation | ✅ Admin |
| `GET` | `/{formation}` | Détails d'une formation | ✅ Admin |
| `PUT` | `/{formation}` | Modifier une formation | ✅ Admin |
| `DELETE` | `/{formation}` | Supprimer une formation | ✅ Admin |

### 📖 **ADMIN SUBJECTS**
**Base URL:** `/api/admin/subjects`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des matières | ✅ Admin |
| `POST` | `/` | Créer une matière | ✅ Admin |
| `GET` | `/{subject}` | Détails d'une matière | ✅ Admin |
| `PUT` | `/{subject}` | Modifier une matière | ✅ Admin |
| `DELETE` | `/{subject}` | Supprimer une matière | ✅ Admin |

### 🏫 **ADMIN CLASSES**
**Base URL:** `/api/admin/classes`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des classes | ✅ Admin |
| `POST` | `/` | Créer une classe | ✅ Admin |
| `GET` | `/{classe}` | Détails d'une classe | ✅ Admin |
| `PUT` | `/{classe}` | Modifier une classe | ✅ Admin |
| `PATCH` | `/{classe}` | Modifier partiellement une classe | ✅ Admin |
| `DELETE` | `/{classe}` | Supprimer une classe | ✅ Admin |

### 👨‍🎓 **ADMIN STUDENTS**
**Base URL:** `/api/admin/students`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des étudiants | ✅ Admin |
| `POST` | `/` | Créer un étudiant | ✅ Admin |
| `GET` | `/{student}` | Détails d'un étudiant | ✅ Admin |
| `PUT` | `/{student}` | Modifier un étudiant | ✅ Admin |
| `DELETE` | `/{student}` | Supprimer un étudiant | ✅ Admin |
| `POST` | `/import` | Importer des étudiants via CSV | ✅ Admin |
| `GET` | `/by-class/{classId}` | Étudiants par classe | ✅ Admin |
| `GET` | `/by-formation/{formationId}` | Étudiants par formation | ✅ Admin |

### 🔗 **ADMIN TEACHER-SUBJECTS**
**Base URL:** `/api/admin/teacher-subjects`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des attributions | ✅ Admin |
| `POST` | `/` | Créer une attribution | ✅ Admin |
| `GET` | `/{teacherSubject}` | Détails d'une attribution | ✅ Admin |
| `PUT` | `/{teacherSubject}` | Modifier une attribution | ✅ Admin |
| `PATCH` | `/{teacherSubject}` | Modifier partiellement une attribution | ✅ Admin |
| `DELETE` | `/{teacherSubject}` | Supprimer une attribution | ✅ Admin |

### 📝 **ADMIN QUIZZES**
**Base URL:** `/api/admin/quizzes`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des quiz (vue admin) | ✅ Admin |
| `GET` | `/{id}` | Détails d'un quiz | ✅ Admin |
| `GET` | `/by-teacher/{teacherId}` | Quiz par enseignant | ✅ Admin |
| `GET` | `/by-subject/{subjectId}` | Quiz par matière | ✅ Admin |
| `GET` | `/statistics` | Statistiques des quiz | ✅ Admin |

### 📊 **ADMIN DASHBOARD**
**Base URL:** `/api/admin/dashboard`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Données du tableau de bord | ✅ Admin |
| `GET` | `/charts/{chartType}` | Données des graphiques | ✅ Admin |

---

## 👨‍🏫 **ENSEIGNANTS**

### 🔑 **TEACHER AUTH**
**Base URL:** `/api/teacher`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `POST` | `/login` | Connexion enseignant | ❌ Public |
| `GET` | `/me` | Informations de l'enseignant connecté | ✅ Sanctum |
| `GET` | `/my-subjects` | Matières de l'enseignant | ✅ Sanctum |
| `POST` | `/logout` | Déconnexion enseignant | ✅ Sanctum |

### 📝 **TEACHER QUIZZES**
**Base URL:** `/api/teacher/quizzes`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des quiz de l'enseignant | ✅ Teacher |
| `POST` | `/` | Créer un quiz | ✅ Teacher |
| `GET` | `/{id}` | Détails d'un quiz | ✅ Teacher |
| `PUT` | `/{id}` | Modifier un quiz | ✅ Teacher |
| `DELETE` | `/{id}` | Supprimer un quiz | ✅ Teacher |

### 🎯 **TEACHER SESSIONS**
**Base URL:** `/api/teacher/sessions`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des sessions de quiz | ✅ Teacher |
| `POST` | `/` | Créer une session de quiz | ✅ Teacher |
| `GET` | `/{id}` | Détails d'une session | ✅ Teacher |
| `PUT` | `/{id}` | Modifier une session | ✅ Teacher |
| `DELETE` | `/{id}` | Supprimer une session | ✅ Teacher |
| `PATCH` | `/{id}/activate` | Activer une session | ✅ Teacher |
| `PATCH` | `/{id}/complete` | Terminer une session | ✅ Teacher |
| `GET` | `/duplicates` | Détecter les doublons | ✅ Teacher |
| `POST` | `/clean-duplicates` | Nettoyer les doublons | ✅ Teacher |

### ❓ **TEACHER QUESTIONS**
**Base URL:** `/api/teacher/quizzes/{quizId}/questions`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Liste des questions d'un quiz | ✅ Teacher |
| `POST` | `/` | Ajouter une question | ✅ Teacher |
| `POST` | `/batch` | Ajouter plusieurs questions | ✅ Teacher |
| `GET` | `/{questionId}` | Détails d'une question | ✅ Teacher |
| `PUT` | `/{questionId}` | Modifier une question | ✅ Teacher |
| `DELETE` | `/{questionId}` | Supprimer une question | ✅ Teacher |

### 📊 **TEACHER RESULTS**
**Base URL:** `/api/teacher`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/quiz-sessions/{quizSessionId}/results` | Résultats d'une session | ✅ Teacher |
| `GET` | `/results/{id}` | Détails d'un résultat | ✅ Teacher |
| `PUT` | `/results/{id}` | Modifier un résultat | ✅ Teacher |
| `PUT` | `/results/{resultId}/responses/{responseId}` | Modifier une réponse | ✅ Teacher |
| `POST` | `/results/{id}/mark-graded` | Marquer comme corrigé | ✅ Teacher |
| `POST` | `/results/{id}/publish` | Publier les résultats | ✅ Teacher |
| `GET` | `/quiz/{quizId}/results` | Tous les résultats d'un quiz | ✅ Teacher |

### 📚 **TEACHER HISTORY**
**Base URL:** `/api/teacher/history`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Historique complet | ✅ Teacher |
| `GET` | `/quizzes` | Historique des quiz | ✅ Teacher |
| `GET` | `/sessions` | Historique des sessions | ✅ Teacher |
| `GET` | `/results` | Historique des résultats | ✅ Teacher |

---

## 👨‍🎓 **ÉTUDIANTS**

### 🔑 **STUDENT AUTH**
**Base URL:** `/api/student/auth`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `POST` | `/login` | Connexion étudiant | ❌ Public |
| `POST` | `/logout` | Déconnexion étudiant | ✅ Sanctum |
| `GET` | `/me` | Informations de l'étudiant connecté | ✅ Sanctum |

### 🎯 **STUDENT SESSIONS**
**Base URL:** `/api/student`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/sessions` | Liste des sessions disponibles | ✅ Sanctum |
| `POST` | `/session/join` | Rejoindre une session | ✅ Sanctum |
| `GET` | `/session/{sessionId}/questions` | Questions d'une session | ✅ Sanctum |
| `GET` | `/session/{sessionId}/questions/{questionId}` | Question spécifique | ✅ Sanctum |
| `GET` | `/session/{sessionId}/progress` | Progrès dans la session | ✅ Sanctum |

### 👤 **STUDENT PROFILE**
**Base URL:** `/api/student/profile`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Profil de l'étudiant | ✅ Student |
| `PUT` | `/` | Modifier le profil | ✅ Student |
| `POST` | `/change-password` | Changer le mot de passe | ✅ Student |
| `POST` | `/picture` | Uploader une photo de profil | ✅ Student |
| `DELETE` | `/picture` | Supprimer la photo de profil | ✅ Student |

### 📊 **STUDENT DASHBOARD**
**Base URL:** `/api/student/dashboard`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `GET` | `/` | Tableau de bord étudiant | ✅ Student |

### ✏️ **STUDENT RESPONSES**
**Base URL:** `/api/student/results`

| Méthode | Endpoint | Description | Authentification |
|---------|----------|-------------|------------------|
| `POST` | `/{resultId}/responses` | Soumettre des réponses | ✅ Student |
| `GET` | `/{resultId}/responses` | Voir ses réponses | ✅ Student |
| `GET` | `/{resultId}/responses/{questionId}` | Voir une réponse spécifique | ✅ Student |

---

## 🔧 **INFORMATIONS TECHNIQUES**

### **Authentification**
- **Sanctum Token**: `Authorization: Bearer {token}`
- **Middlewares**:
  - `auth:sanctum`: Utilisateur connecté
  - `admin`: Administrateur pédagogique
  - `teacher`: Enseignant
  - `student`: Étudiant

### **Format des Données**
- **Content-Type**: `application/json`
- **Accept**: `application/json`

### **Codes de Réponse**
- `200`: Succès
- `201`: Créé
- `400`: Erreur de validation
- `401`: Non authentifié
- `403`: Non autorisé
- `404`: Non trouvé
- `500`: Erreur serveur

### **Pagination**
Certains endpoints supportent la pagination:
- Paramètre: `?page=1&per_page=15`

### **Filtres et Recherche**
- `?search=term`: Recherche textuelle
- `?is_active=1`: Filtre par statut
- `?class_id=123`: Filtre par classe

---

## 📞 **SUPPORT**

Pour toute question concernant cette API:
- **Documentation détaillée**: Chaque endpoint contient des exemples
- **Tests**: Utilisez Postman ou Insomnia pour tester
- **Logs**: Vérifiez `storage/logs/laravel.log` pour le debugging

---
*Document généré automatiquement - Université de Lomé*