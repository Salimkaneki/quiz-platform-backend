# Quiz Platform Backend

Une plateforme de quiz éducative développée avec Laravel permettant la gestion complète des quiz, des utilisateurs et des sessions d'examen.

## 📋 Description

Cette application fournit une API REST complète pour une plateforme de quiz éducative avec gestion des utilisateurs (administrateurs, enseignants, étudiants), des institutions, des formations, des matières et des sessions de quiz.

## 🏗️ Architecture

### Techn# 5. Rejoindre une s# 7. Soumettre des réponses (plusieurs types)
curl -X POST http://localhost:8000/api/student/results/1/responses \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "responses": [
      {"question_id": 1, "answer": "Paris"},
      {"question_id": 2, "answer": "true"},
      {"question_id": 3, "answer": "0"},
      {"question_id": 4, "answer": "Ma réponse ouverte"}
    ]
  }'

## Gestion du Profil Étudiant

### 1. Voir le profil
curl -X GET http://localhost:8000/api/student/profile \
  -H "Authorization: Bearer {TOKEN}"

### 2. Mettre à jour le profil
curl -X PUT http://localhost:8000/api/student/profile \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "address": "123 Main St",
    "emergency_contact": "+1234567890",
    "preferences": {"theme": "dark", "notifications": true}
  }'

### 3. Changer le mot de passe
curl -X PUT http://localhost:8000/api/student/profile/password \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'

### 4. Télécharger une photo de profil
curl -X POST http://localhost:8000/api/student/profile/picture \
  -H "Authorization: Bearer {TOKEN}" \
  -F "profile_picture=@/path/to/your/photo.jpg"

### 5. Supprimer la photo de profil
curl -X DELETE http://localhost:8000/api/student/profile/picture \
  -H "Authorization: Bearer {TOKEN}"

### 6. Voir le tableau de bord
curl -X GET http://localhost:8000/api/student/dashboard \
  -H "Authorization: Bearer {TOKEN}"

#### Réponse du tableau de bord
Le tableau de bord retourne un objet JSON complet avec :
- **stats** : Statistiques générales (quiz passés, moyenne, etc.)
- **recent_results** : Derniers résultats (10 plus récents)
- **active_sessions** : Sessions de quiz en cours
- **upcoming_sessions** : Sessions disponibles à rejoindre
- **subject_progress** : Progression par matière
- **in_progress_quizzes** : Quiz commencés mais non terminés-X POST http://localhost:8000/api/student/session/join \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"session_code":"ABC123"}'

# 6. Voir les questions
curl -X GET http://localhost:8000/api/student/session/1/questions \
  -H "Authorization: Bearer {TOKEN}"

# 7. Soumettre des réponses (plusieurs types)lisées
- **Framework**: Laravel 11.x
- **Base de données**: SQLite (développement) / PostgreSQL (production)
- **Authentification**: Laravel Sanctum
- **ORM**: Eloquent
- **Architecture**: MVC avec séparation par domaine

## 👥 Acteurs et Rôles

### 1. **Administrateur** (`admin`)
- Gestion des institutions, formations, matières et classes
- Gestion des utilisateurs (enseignants, étudiants, administrateurs)
- Supervision et statistiques globales
- Accès aux tableaux de bord administratifs

### 2. **Enseignant** (`teacher`)
- Création et gestion des quiz
- Gestion des questions et réponses
- Organisation des sessions de quiz
- Consultation des résultats des étudiants

### 3. **Étudiant** (`student`)
- Participation aux sessions de quiz
- Soumission des réponses
- Consultation des résultats personnels

## 📁 Structure des Fichiers par Acteur

### 🔐 **Authentification** (`app/Http/Controllers/Auth/`)
```
Auth/
├── AdminAuthController.php      # Authentification administrateur
├── StudentAuthController.php    # Authentification étudiant
└── TeacherAuthController.php    # Authentification enseignant
```

### 🏢 **Gestion** (`app/Http/Controllers/Management/`)
*Utilisé principalement par les Administrateurs*
```
Management/
├── AdministratorController.php  # Gestion des administrateurs
├── ClasseController.php         # Gestion des classes
├── FormationController.php      # Gestion des formations
├── InstitutionController.php    # Gestion des institutions
├── SubjectController.php        # Gestion des matières
├── TeacherSubjectController.php # Attribution matière-enseignant
└── UserController.php           # Gestion des utilisateurs
```

### 📝 **Quiz** (`app/Http/Controllers/Quiz/`)
*Utilisé principalement par les Enseignants*
```
Quiz/
├── QuizController.php           # Gestion des quiz
├── QuestionController.php       # Gestion des questions
├── QuizSessionController.php    # Gestion des sessions
└── ResultController.php         # Gestion des résultats
```

### 👨‍🎓 **Étudiant** (`app/Http/Controllers/Student/`)
*Utilisé par les Étudiants*
```
Student/
├── StudentResponseController.php # Soumission des réponses
└── StudentSessionController.php  # Participation aux sessions
```

### ⚙️ **Administration** (`app/Http/Controllers/Admin/`)
*Fonctionnalités administratives spécifiques*
```
Admin/
├── DashboardController.php      # Tableaux de bord et statistiques
├── QuizController.php           # Vue admin des quiz
├── StudentController.php        # Gestion admin des étudiants
├── StudentImportController.php  # Import en masse d'étudiants
└── TeacherController.php        # Gestion admin des enseignants
```

## 🚀 Endpoints API

### 🔐 **Authentification**

#### Administrateur
```
POST   /api/admin/login          # Connexion admin
POST   /api/admin/logout         # Déconnexion admin
GET    /api/admin/me             # Infos utilisateur connecté
```

#### Enseignant
```
POST   /api/teacher/login        # Connexion enseignant
POST   /api/teacher/logout       # Déconnexion enseignant
GET    /api/teacher/me           # Infos utilisateur connecté
GET    /api/teacher/my-subjects  # Matières de l'enseignant
```

#### Étudiant
```
POST   /api/student/auth/login   # Connexion étudiant
POST   /api/student/auth/logout  # Déconnexion étudiant
GET    /api/student/auth/me      # Infos utilisateur connecté
```

### 🏢 **Gestion (Administrateur)**

#### Institutions
```
GET    /api/institutions              # Liste des institutions
POST   /api/institutions              # Créer une institution
GET    /api/institutions/{id}         # Détails institution
PUT    /api/institutions/{id}         # Modifier institution
DELETE /api/institutions/{id}         # Supprimer institution
```

#### Utilisateurs
```
GET    /api/users                                 # Liste des utilisateurs
POST   /api/users                                 # Créer un utilisateur
GET    /api/users/{user}                          # Détails utilisateur
PUT    /api/users/{user}                          # Modifier utilisateur
DELETE /api/users/{user}                          # Supprimer utilisateur
GET    /api/users/account-type/{accountType}      # Utilisateurs par type
```

#### Administrateurs
```
GET    /api/administrators                          # Liste des administrateurs
POST   /api/administrators                          # Créer un administrateur
GET    /api/administrators/{administrator}          # Détails administrateur
PUT    /api/administrators/{administrator}          # Modifier administrateur
DELETE /api/administrators/{administrator}          # Supprimer administrateur
GET    /api/administrators/institution/{institutionId} # Par institution
GET    /api/administrators/type/{type}              # Par type
```

#### Formations
```
GET    /api/admin/formations                 # Liste des formations
POST   /api/admin/formations                 # Créer une formation
GET    /api/admin/formations/{formation}     # Détails formation
PUT    /api/admin/formations/{formation}     # Modifier formation
DELETE /api/admin/formations/{formation}     # Supprimer formation
```

#### Matières
```
GET    /api/admin/subjects                   # Liste des matières
POST   /api/admin/subjects                   # Créer une matière
GET    /api/admin/subjects/{subject}         # Détails matière
PUT    /api/admin/subjects/{subject}         # Modifier matière
DELETE /api/admin/subjects/{subject}         # Supprimer matière
```

#### Classes
```
GET    /api/admin/classes                    # Liste des classes
POST   /api/admin/classes                    # Créer une classe
GET    /api/admin/classes/{classe}           # Détails classe
PUT    /api/admin/classes/{classe}           # Modifier classe
DELETE /api/admin/classes/{classe}           # Supprimer classe
```

#### Étudiants (Admin)
```
GET    /api/admin/students                   # Liste des étudiants
POST   /api/admin/students                   # Créer un étudiant
GET    /api/admin/students/{student}         # Détails étudiant
PUT    /api/admin/students/{student}         # Modifier étudiant
DELETE /api/admin/students/{student}         # Supprimer étudiant
POST   /api/admin/students/import            # Import en masse
GET    /api/admin/students/by-class/{classId}      # Étudiants par classe
GET    /api/admin/students/by-formation/{formationId} # Par formation
```

#### Enseignants (Admin)
```
GET    /api/admin/teachers                   # Liste des enseignants
POST   /api/admin/teachers                   # Créer un enseignant
GET    /api/admin/teachers/{teacher}         # Détails enseignant
PUT    /api/admin/teachers/{teacher}         # Modifier enseignant
DELETE /api/admin/teachers/{teacher}         # Supprimer enseignant
GET    /api/admin/teachers/users             # Utilisateurs disponibles
GET    /api/admin/teachers/with-subjects     # Avec attributions
```

#### Attributions Matière-Enseignant
```
GET    /api/admin/teacher-subjects           # Liste des attributions
POST   /api/admin/teacher-subjects           # Créer une attribution
GET    /api/admin/teacher-subjects/{teacherSubject} # Détails attribution
PUT    /api/admin/teacher-subjects/{teacherSubject} # Modifier attribution
DELETE /api/admin/teacher-subjects/{teacherSubject} # Supprimer attribution
```

#### Quiz (Vue Admin)
```
GET    /api/admin/quizzes                    # Liste des quiz
GET    /api/admin/quizzes/{id}               # Détails quiz
GET    /api/admin/quizzes/by-teacher/{teacherId}    # Quiz par enseignant
GET    /api/admin/quizzes/by-subject/{subjectId}    # Quiz par matière
GET    /api/admin/quizzes/statistics         # Statistiques
```

#### Tableaux de bord
```
GET    /api/admin/dashboard                  # Dashboard principal
GET    /api/admin/dashboard/charts/{chartType} # Données graphiques
```

### 📝 **Quiz (Enseignant)**

#### Quiz
```
GET    /api/teacher/quizzes                  # Mes quiz
POST   /api/teacher/quizzes                  # Créer un quiz
GET    /api/teacher/quizzes/{quiz}           # Détails quiz
PUT    /api/teacher/quizzes/{quiz}           # Modifier quiz
DELETE /api/teacher/quizzes/{quiz}           # Supprimer quiz
```

#### Questions
```
GET    /api/teacher/quizzes/{quizId}/questions       # Questions d'un quiz
POST   /api/teacher/quizzes/{quizId}/questions       # Ajouter une question
POST   /api/teacher/quizzes/{quizId}/questions/batch # Ajouter plusieurs questions
GET    /api/teacher/quizzes/{quizId}/questions/{questionId}   # Détails question
PUT    /api/teacher/quizzes/{quizId}/questions/{questionId}   # Modifier question
DELETE /api/teacher/quizzes/{quizId}/questions/{questionId}   # Supprimer question
```

#### Sessions de Quiz
```
GET    /api/teacher/sessions                 # Mes sessions
POST   /api/teacher/sessions                 # Créer une session
GET    /api/teacher/sessions/{id}            # Détails session
PUT    /api/teacher/sessions/{id}            # Modifier session
DELETE /api/teacher/sessions/{id}            # Supprimer session
PATCH  /api/teacher/sessions/{id}/activate   # Activer session
PATCH  /api/teacher/sessions/{id}/pause      # Mettre en pause
PATCH  /api/teacher/sessions/{id}/resume     # Reprendre session
PATCH  /api/teacher/sessions/{id}/complete   # Terminer session
PATCH  /api/teacher/sessions/{id}/cancel     # Annuler session
GET    /api/teacher/sessions/duplicates      # Détecter doublons
POST   /api/teacher/sessions/clean-duplicates # Nettoyer doublons
```

#### Résultats
```
GET    /api/teacher/quiz-sessions/{quizSessionId}/results  # Liste des résultats d'une session
GET    /api/teacher/results/{id}               # Détails d'un résultat étudiant
PUT    /api/teacher/results/{id}               # Modifier un résultat global
PUT    /api/teacher/results/{resultId}/responses/{responseId} # Corriger une réponse spécifique
POST   /api/teacher/results/{id}/mark-graded   # Marquer comme corrigé
POST   /api/teacher/results/{id}/publish       # Publier le résultat
GET    /api/teacher/quiz/{quizId}/results      # Tous les résultats d'un quiz
```

### 📊 **Gestion des Résultats (Enseignant)**

#### 1. Lister les résultats d'une session
```bash
curl -X GET http://localhost:8000/api/teacher/quiz-sessions/8/results \
  -H "Authorization: Bearer {TOKEN}"
```

**Réponse :**
```json
[
  {
    "id": 5,
    "student_id": 7,
    "student": {
      "id": 7,
      "name": "Samir PEREIRA",
      "email": "samirpereira07@gmail.com"
    },
    "total_points": 25.0,
    "max_points": 40.0,
    "percentage": 62.5,
    "status": "submitted",
    "started_at": "2025-09-30T01:12:00.000000Z",
    "submitted_at": "2025-09-30T01:30:00.000000Z"
  }
]
```

#### 2. Voir les détails d'un résultat
```bash
curl -X GET http://localhost:8000/api/teacher/results/5 \
  -H "Authorization: Bearer {TOKEN}"
```

**Réponse :** Détails complets du résultat + réponses de l'étudiant.

#### 3. Corriger une réponse spécifique
```bash
curl -X PUT http://localhost:8000/api/teacher/results/5/responses/15 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "is_correct": true,
    "points_earned": 2.0,
    "teacher_comment": "Bonne réponse"
  }'
```

#### 4. Modifier le résultat global
```bash
curl -X PUT http://localhost:8000/api/teacher/results/5 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "total_points": 30.0,
    "teacher_feedback": "Bon travail général"
  }'
```

#### 5. Marquer comme corrigé
```bash
curl -X POST http://localhost:8000/api/teacher/results/5/mark-graded \
  -H "Authorization: Bearer {TOKEN}"
```

#### 6. Publier le résultat
```bash
curl -X POST http://localhost:8000/api/teacher/results/5/publish \
  -H "Authorization: Bearer {TOKEN}"
```

#### Historique
```
GET    /api/teacher/history                    # Historique complet
GET    /api/teacher/history/quizzes            # Historique des quiz
GET    /api/teacher/history/sessions           # Historique des sessions
GET    /api/teacher/history/results            # Historique des résultats
```

### 👨‍🎓 **Étudiant**

#### Participation aux Sessions
```
POST   /api/student/session/join                      # Rejoindre une session
GET    /api/student/session/{sessionId}/questions     # Questions de la session
GET    /api/student/session/{sessionId}/questions/{questionId} # Question spécifique
GET    /api/student/session/{sessionId}/progress      # Progrès dans la session
```

#### Gestion du Profil
```
GET    /api/student/profile                           # Voir son profil
PUT    /api/student/profile                           # Modifier son profil
POST   /api/student/profile/change-password           # Changer mot de passe
POST   /api/student/profile/picture                   # Télécharger photo profil
DELETE /api/student/profile/picture                   # Supprimer photo profil
```

#### Tableau de Bord
```
GET    /api/student/dashboard                         # Tableau de bord personnel
```

#### Soumission des Réponses
```
POST   /api/student/results/{resultId}/responses      # Soumettre réponses
GET    /api/student/results/{resultId}/responses      # Voir ses réponses
GET    /api/student/results/{resultId}/responses/{questionId} # Réponse spécifique
```

### 👨‍🏫 **Enseignants (Routes publiques)**
```
GET    /api/teachers                           # Liste des enseignants
POST   /api/teachers                           # Créer un enseignant
GET    /api/teachers/{teacher}                 # Détails enseignant
PUT    /api/teachers/{teacher}                 # Modifier enseignant
DELETE /api/teachers/{teacher}                 # Supprimer enseignant
GET    /api/teachers/permanent                 # Enseignants permanents
GET    /api/teachers/grade/{grade}             # Par grade
GET    /api/teachers/my-institution            # De mon institution
```

## 🛠️ Installation et Configuration

### Prérequis
- PHP 8.1+
- Composer
- Node.js & npm (pour les assets frontend)
- SQLite ou PostgreSQL

### Installation
```bash
# Cloner le repository
git clone <repository-url>
cd quiz-platform-backend

# Installer les dépendances PHP
composer install

# Installer les dépendances Node.js
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
# Pour SQLite (recommandé pour développement):
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# Créer la base de données
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate

# (Optionnel) Exécuter les seeders
php artisan db:seed

# Compiler les assets
npm run build

# Démarrer le serveur
php artisan serve
```

### Configuration
Modifier le fichier `.env` selon vos besoins :
```env
APP_NAME="Quiz Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# Ou pour PostgreSQL :
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=quiz_platform
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

## 📖 Utilisation

### Authentification
Tous les endpoints (sauf ceux publics) nécessitent une authentification via Laravel Sanctum.

#### Exemple de connexion administrateur :
```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password",
    "institution_id": 1
  }'
```

#### Utilisation des tokens :
```bash
curl -X GET http://localhost:8000/api/admin/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Création d'un quiz complet
1. **Créer un quiz** (Enseignant)
2. **Ajouter des questions** (Enseignant)
3. **Créer une session** (Enseignant)
4. **Étudiants rejoignent la session**
5. **Soumission des réponses** (Étudiants)
6. **Consultation des résultats** (Enseignant/Étudiants)

## 🧪 Exemples d'Utilisation Détaillée

### 📝 Formats de Réponse par Type de Question
- **Vrai/Faux** : `"true"` ou `"false"`
- **QCM** : Index de l'option `"0"`, `"1"`, `"2"`, etc.
- **Ouvert** : Texte libre `"Ma réponse"`

### 👨‍🎓 Test Complet - Côté Étudiant
```bash
# 1. Connexion étudiant
curl -X POST http://localhost:8000/api/student/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@school.com","password":"password"}'

# 2. Voir son profil
curl -X GET http://localhost:8000/api/student/profile \
  -H "Authorization: Bearer {TOKEN}"

# 3. Modifier son profil
curl -X PUT http://localhost:8000/api/student/profile \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "0123456789",
    "address": "123 Rue des Étudiants",
    "emergency_contact": "Parent Dupont",
    "emergency_phone": "0987654321",
    "preferences": {
      "theme": "dark",
      "language": "fr",
      "notifications": true
    }
  }'

# 4. Changer mot de passe
curl -X POST http://localhost:8000/api/student/profile/change-password \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "password",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'

# 5. Rejoindre une session
curl -X POST http://localhost:8000/api/student/session/join \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"session_code":"ABC123"}'

# 3. Voir les questions
curl -X GET http://localhost:8000/api/student/session/1/questions \
  -H "Authorization: Bearer {TOKEN}"

# 4. Soumettre des réponses (plusieurs types)
curl -X POST http://localhost:8000/api/student/results/1/responses \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "responses": [
      {"question_id": 1, "answer": "Paris"},
      {"question_id": 2, "answer": "true"},
      {"question_id": 3, "answer": "0"},
      {"question_id": 4, "answer": "Ma réponse ouverte"}
    ]
  }'
```

### 👨‍🏫 Test Complet - Côté Enseignant
```bash
# 1. Connexion enseignant
curl -X POST http://localhost:8000/api/teacher/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teacher@school.com","password":"password"}'

# 2. Créer un quiz
curl -X POST http://localhost:8000/api/teacher/quizzes \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Quiz de Géographie",
    "description": "Test sur les capitales",
    "subject_id": 1,
    "duration_minutes": 30,
    "status": "draft"
  }'

# 3. Ajouter des questions
curl -X POST http://localhost:8000/api/teacher/quizzes/1/questions \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "question_text": "Quelle est la capitale de la France ?",
    "type": "multiple_choice",
    "points": 2,
    "options": [
      {"text": "Paris", "is_correct": true},
      {"text": "Lyon", "is_correct": false},
      {"text": "Marseille", "is_correct": false}
    ]
  }'

# 4. Voir l'historique complet
curl -X GET http://localhost:8000/api/teacher/history \
  -H "Authorization: Bearer {TOKEN}"

# 5. Voir l'historique des quiz
curl -X GET http://localhost:8000/api/teacher/history/quizzes \
  -H "Authorization: Bearer {TOKEN}"

# 6. Voir l'historique des sessions
curl -X GET http://localhost:8000/api/teacher/history/sessions \
  -H "Authorization: Bearer {TOKEN}"

# 7. Voir l'historique des résultats
curl -X GET http://localhost:8000/api/teacher/history/results \
  -H "Authorization: Bearer {TOKEN}"
```

### 🏢 Test Complet - Côté Administrateur
```bash
# 1. Connexion admin
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@school.com","password":"password"}'

# 2. Voir le dashboard
curl -X GET http://localhost:8000/api/admin/dashboard \
  -H "Authorization: Bearer {TOKEN}"

# 3. Lister les quiz des enseignants
curl -X GET http://localhost:8000/api/admin/quizzes \
  -H "Authorization: Bearer {TOKEN}"

# 4. Voir les statistiques
curl -X GET http://localhost:8000/api/admin/quizzes/statistics \
  -H "Authorization: Bearer {TOKEN}"
```

## 📊 Tableau de Bord Étudiant

Le tableau de bord fournit une vue d'ensemble complète des activités et performances de l'étudiant :

### 📈 Statistiques Générales
- Nombre total de quiz passés
- Score moyen global
- Meilleur et pire score
- Temps total passé sur les quiz
- Répartition des performances (excellent, bon, moyen, faible)

### 🏆 Résultats Récents
- Liste des 10 derniers résultats publiés
- Détails : titre du quiz, matière, score, note, temps passé
- Date de soumission

### ⚡ Sessions Actives
- Quiz en cours de réalisation
- Progression (questions répondues/total)
- Temps restant avant expiration
- Code de session et titre du quiz

### 📅 Sessions à Venir
- Sessions de quiz disponibles à rejoindre
- Informations : titre, matière, horaires
- Temps avant ouverture

### 📚 Progression par Matière
- Moyenne par matière
- Nombre de quiz passés par matière
- Meilleur et pire score par matière
- Niveau de performance (excellent, bon, moyen, etc.)

### ⏳ Quiz en Cours
- Quiz commencés mais non terminés
- Avancement détaillé
- Temps déjà passé
- Temps restant

## 📚 Historique Enseignant

L'historique enseignant fournit une vue complète de toutes les activités pédagogiques de l'enseignant :

### 📊 Statistiques Globales
- Nombre total de quiz créés (publiés/brouillons)
- Nombre total de sessions organisées (actives/terminées)
- Nombre total de résultats (corrigés/publiés)
- Score moyen des étudiants
- Temps total passé par les étudiants

### 📝 Historique des Quiz
- Liste paginée de tous les quiz créés
- Statut de chaque quiz (publié/brouillon)
- Nombre de sessions et participants par quiz
- Score moyen obtenu
- Date de création et modification

### 🎯 Historique des Sessions
- Liste paginée des sessions de quiz organisées
- Statut des sessions (active/terminée/annulée)
- Nombre de participants et taux de completion
- Score moyen de la session
- Période d'activité

### 📈 Historique des Résultats
- Liste paginée de tous les résultats des étudiants
- Détails des performances individuelles
- Informations sur l'étudiant et le quiz
- Statut de correction et publication
- Possibilité de filtrage et recherche

### 🔔 Activité Récente
- Chronologie des dernières actions
- Créations de quiz et sessions
- Publications de résultats
- Corrections effectuées
- Activités des 15 derniers jours

### Relations principales
- **Institution** → **Formation** → **Classe** → **Student**
- **Institution** → **Administrator**
- **Teacher** ←→ **Subject** (via TeacherSubject)
- **Quiz** → **Question**
- **QuizSession** → **Result** → **StudentResponse**

### Diagramme des Relations
```
Institution
├── Administrators
├── Teachers
│   ├── TeacherSubjects (attributions)
│   ├── Quizzes
│   │   ├── Questions
│   │   └── QuizSessions
│   │       └── Results
│   │           └── StudentResponses
│   └── Classes
├── Students
│   └── Results (via QuizSessions)
├── Formations
│   └── Classes
└── Subjects
    └── TeacherSubjects
```

## 🔒 Sécurité

- Authentification basée sur Laravel Sanctum
- Autorisation par rôle (admin/teacher/student)
- Validation des données d'entrée
- Protection CSRF
- Logs d'audit
- Isolation des données par institution

## 📋 Codes de Réponse HTTP

- **200** : Succès
- **201** : Créé avec succès
- **400** : Requête invalide
- **401** : Non authentifié
- **403** : Accès refusé
- **404** : Ressource non trouvée
- **409** : Conflit (doublon)
- **500** : Erreur serveur

## 🛠️ Outils de Test Recommandés

- **Postman** : Interface graphique pour tester les APIs
- **Insomnia** : Alternative à Postman
- **Thunder Client** (VS Code extension)
- **curl** : Tests en ligne de commande

## 📝 Notes Importantes

1. **Authentification** : Tous les endpoints protégés nécessitent un token Bearer
2. **Rôles** : Les utilisateurs ont des rôles spécifiques (admin, teacher, student)
3. **Institutions** : Isolation des données par institution
4. **Sessions** : Les quiz sont accessibles via des sessions avec codes
5. **Validation** : Toutes les entrées sont validées côté serveur
6. **Soumission multiple** : Les étudiants peuvent soumettre **plusieurs réponses en une seule requête** via `POST /api/student/results/{resultId}/responses`
7. **Soumission partielle** : Les étudiants peuvent soumettre certaines réponses et compléter plus tard (avant la fin de la session)

## 📝 Scripts Utiles

```bash
# Lister toutes les routes
php artisan route:list

# Vérifier la syntaxe des contrôleurs
php artisan route:list --compact

# Exécuter les tests
php artisan test

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

****