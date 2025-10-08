# 📚 Collections Postman - Quiz Platform

Ce dossier contient les collections Postman organisées pour tester l'API de la plateforme de quiz.

## 📁 Structure des Collections

### 🔓 Public_Routes.postman_collection.json
- **Endpoints publics** : Inscription, connexion générale
- **Aucune authentification requise**
- **Utilisation** : Pour les nouvelles inscriptions et connexions initiales

### 👑 Admin_Routes.postman_collection.json
- **Endpoints administrateur** : Gestion complète de la plateforme
- **Authentification** : Token Bearer requis
- **Fonctionnalités** :
  - Gestion des enseignants, formations, matières, classes
  - Gestion des étudiants et quiz
  - Tableau de bord et statistiques

### 👨‍🏫 Teacher_Routes.postman_collection.json
- **Endpoints enseignant** : Gestion des quiz et sessions
- **Authentification** : Token Bearer requis
- **Fonctionnalités** :
  - Création et gestion des quiz
  - Gestion des sessions de quiz
  - Gestion des questions
  - Consultation des résultats

### 👨‍🎓 Student_Routes.postman_collection.json
- **Endpoints étudiant** : Participation aux quiz
- **Authentification** : Token Bearer requis
- **Fonctionnalités** :
  - Consultation des sessions disponibles
  - Participation aux quiz
  - Soumission des réponses
  - Consultation des résultats

## 🚀 Comment utiliser les collections

### 1. Importation dans Postman
1. Ouvrez Postman
2. Cliquez sur "Import" en haut à gauche
3. Sélectionnez "File"
4. Importez chaque fichier `.postman_collection.json`

### 2. Configuration des variables
Chaque collection contient des variables :
- `{{base_url}}` : URL de base de l'API (par défaut : `http://localhost:8000/api`)
- `{{auth_token}}` : Token d'authentification (rempli automatiquement après connexion)

### 3. Ordre d'utilisation recommandé

#### Pour tester un scénario complet :
1. **Commencez par Public** : Inscrivez-vous ou connectez-vous
2. **Admin** : Créez les données de base (enseignants, formations, etc.)
3. **Enseignant** : Créez des quiz et des sessions
4. **Étudiant** : Participez aux quiz

#### Comptes de test disponibles :
- **Admin** : `admin@univ-lome.tg` / `password123`
- **Enseignant** : `teacher@univ-lome.tg` / `password123`
- **Étudiant** : `student@univ-lome.tg` / `password123`

## 📋 Endpoints couverts

### Routes Publiques
- `POST /register` - Inscription
- `POST /login` - Connexion générale

### Routes Admin
- Authentification enseignant
- CRUD Enseignants, Formations, Matières, Classes
- CRUD Étudiants
- Gestion Quiz et Résultats
- Tableau de bord

### Routes Enseignant
- Authentification
- Gestion des matières assignées
- CRUD Quiz
- Gestion des sessions
- Gestion des questions
- Consultation des résultats

### Routes Étudiant
- Authentification
- Consultation du profil
- Sessions disponibles
- Participation aux quiz
- Soumission des réponses
- Consultation des résultats

## 🔧 Configuration

### Variables d'environnement
Vous pouvez créer un environnement dans Postman avec :
- `base_url` : `http://localhost:8000/api`
- `auth_token` : (sera rempli automatiquement)

### Headers automatiques
Les collections sont configurées avec :
- `Authorization: Bearer {{auth_token}}` pour les routes authentifiées
- `Content-Type: application/json` pour les requêtes POST/PUT

## 🐛 Dépannage

### Token expiré
Si vous obtenez une erreur 401 :
1. Relancez la requête de connexion appropriée
2. Le token sera automatiquement mis à jour dans les variables

### Erreur de connexion
Vérifiez que :
- Le serveur Laravel est démarré (`php artisan serve`)
- L'URL de base est correcte
- Les credentials sont valides

### Données manquantes
Si certaines requêtes échouent à cause de données manquantes :
1. Utilisez d'abord les endpoints Admin pour créer les données de base
2. Puis les endpoints Enseignant pour créer les quiz
3. Enfin les endpoints Étudiant pour participer

## 📖 Documentation API
Consultez également :
- `STUDENT_API_DOCUMENTATION.md`
- `TEACHER_API_DOCUMENTATION.md`
- `README.md` principal du projet