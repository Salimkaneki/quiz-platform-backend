# 📚 Collection Postman - Quiz Platform API v2.0

Collection Postman complète pour tester l'API de la plateforme de quiz avec toutes les nouvelles fonctionnalités.

## 📦 Fichiers

- `Quiz_Platform_Postman_Collection_v2.0.json` - Collection Postman complète
- `README.md` - Ce guide d'utilisation

## 🚀 Import dans Postman

1. **Ouvrir Postman**
2. **Importer la collection** :
   - Cliquez sur "Import" en haut à gauche
   - Sélectionnez "File"
   - Choisissez `Quiz_Platform_Postman_Collection_v2.0.json`
3. **Configurer les variables** :
   - Ouvrez l'onglet "Variables" de la collection
   - Modifiez `base_url` si nécessaire (par défaut: `http://localhost:8000/api`)

## 🔧 Configuration Requise

### Variables d'environnement
La collection utilise ces variables automatiquement remplies :

| Variable | Description | Valeur par défaut |
|----------|-------------|-------------------|
| `base_url` | URL de base de l'API | `http://localhost:8000/api` |
| `auth_token` | Token d'authentification actuel | Auto-rempli |
| `admin_token` | Token administrateur | Auto-rempli |
| `teacher_token` | Token enseignant | Auto-rempli |
| `student_token` | Token étudiant | Auto-rempli |
| `quiz_id` | ID du quiz actuel | Auto-rempli |
| `session_id` | ID de la session actuelle | Auto-rempli |
| `result_id` | ID du résultat actuel | Auto-rempli |
| `notification_id` | ID de la notification actuelle | Auto-rempli |

## 📋 Structure de la Collection

### 🔐 AUTHENTIFICATION
- **Connexion Administrateur** - Obtenir token admin
- **Connexion Enseignant** - Obtenir token enseignant
- **Connexion Étudiant** - Obtenir token étudiant

### 🏢 ADMIN - GESTION DES SESSIONS D'EXAMEN
- Lister les sessions de l'institution
- Voir les quiz disponibles pour créer des sessions
- Voir les enseignants disponibles
- **Créer une session d'examen (NOUVEAU)**
- Voir les détails d'une session
- Modifier une session
- Activer/Terminer/Annuler une session
- Supprimer une session
- Voir les statistiques des sessions

### 🔔 ADMIN - NOTIFICATIONS ENSEIGNANTS
- Lister les enseignants disponibles
- **Envoyer à tous les enseignants (NOUVEAU)**
- **Envoyer à un enseignant spécifique (NOUVEAU)**
- **Envoyer à plusieurs enseignants (NOUVEAU)**

### 👨‍🏫 ENSEIGNANT - NOTIFICATIONS
- Lister mes notifications
- Compteur de notifications non lues
- Marquer une notification comme lue
- Marquer plusieurs notifications comme lues
- Marquer toutes les notifications comme lues
- Supprimer une notification

### 👨‍🎓 ÉTUDIANT - NOTIFICATIONS
- Lister mes notifications
- Compteur de notifications non lues
- Marquer une notification comme lue
- Marquer plusieurs notifications comme lues
- Marquer toutes les notifications comme lues
- Supprimer une notification

### 📊 DASHBOARD ADMIN
- Voir le tableau de bord principal
- Voir les données des graphiques

### 📝 EXEMPLES COMPLETS - WORKFLOWS
- **🏫 WORKFLOW COMPLET: Création d'examen institutionnel**
- **📢 WORKFLOW COMPLET: Campagne de notifications enseignants**

## 🎯 Utilisation Recommandée

### 1. **Première Utilisation**
```bash
# Démarrer le serveur Laravel
php artisan serve

# Importer la collection dans Postman
# Exécuter les authentifications dans l'ordre
```

### 2. **Ordre d'Exécution**
1. **Authentification** - Se connecter avec chaque rôle
2. **Tests individuels** - Tester chaque endpoint séparément
3. **Workflows complets** - Exécuter les scénarios complets

### 3. **Workflow Typique - Admin**
```
1. 🔐 Connexion Administrateur
2. 🏢 Lister les quiz disponibles
3. 🏢 Lister les enseignants disponibles
4. 🏢 Créer une session d'examen
5. 🏢 Activer la session
6. 📊 Vérifier les statistiques
```

### 4. **Workflow Typique - Notifications**
```
1. 🔐 Connexion Administrateur
2. 🔔 Lister les enseignants
3. 🔔 Envoyer une notification
4. 🔐 Changer pour token enseignant
5. 👨‍🏫 Vérifier réception notification
6. 👨‍🏫 Marquer comme lue
```

## 🆕 Nouvelles Fonctionnalités v2.0

### ✨ **Admin - Gestion des Sessions d'Examen**
- ✅ Création de sessions d'examen par les administrateurs
- ✅ Assignation flexible des enseignants aux sessions
- ✅ Gestion complète du cycle de vie des sessions
- ✅ Statistiques institutionnelles des sessions

### 🔔 **Système de Notifications Complet**
- ✅ Notifications admin → enseignants (8 types)
- ✅ Gestion complète côté enseignants
- ✅ Gestion complète côté étudiants
- ✅ Notifications automatiques lors de création de sessions

### 📊 **Améliorations Techniques**
- ✅ Variables automatiques remplies
- ✅ Tests automatiques pour sauvegarder les IDs
- ✅ Workflows complets documentés
- ✅ Gestion d'erreurs améliorée

## 🔍 Types de Notifications Disponibles

| Type | Label | Description |
|------|-------|-------------|
| `admin_announcement` | Annonce administrative | Annonces générales |
| `teacher_assignment` | Attribution de matière | Nouvelles attributions |
| `schedule_change` | Changement d'horaire | Modifications d'emploi du temps |
| `system_maintenance` | Maintenance système | Alertes de maintenance |
| `policy_update` | Mise à jour des politiques | Changements de politiques |
| `training_required` | Formation requise | Formations obligatoires |
| `performance_review` | Évaluation de performance | Évaluations périodiques |
| `contract_update` | Mise à jour contractuelle | Changements contractuels |

## ⚠️ Notes Importantes

### **Authentification**
- Chaque rôle a son propre token
- Les tokens sont automatiquement sauvegardés lors de la connexion
- Utilisez le bon token selon le contexte

### **Variables Automatiques**
- Les IDs sont automatiquement sauvegardés lors de la création
- Vérifiez les variables après chaque création importante
- Les tokens sont mis à jour automatiquement

### **Ordre des Tests**
- Commencez toujours par l'authentification
- Créez des ressources avant de les modifier/supprimer
- Testez les workflows complets en dernier

### **Données de Test**
- Utilisez des dates futures pour les sessions
- Les emails doivent être uniques dans la base
- Vérifiez que les relations existent (quiz, enseignants, etc.)

## 🐛 Dépannage

### **Erreur 401 - Non autorisé**
- Vérifiez que vous êtes connecté avec le bon rôle
- Vérifiez que le token n'est pas expiré

### **Erreur 404 - Non trouvé**
- Vérifiez que la ressource existe
- Vérifiez les IDs dans les variables

### **Erreur 422 - Validation**
- Vérifiez les données envoyées
- Respectez les formats requis (dates, emails, etc.)

### **Erreur 500 - Serveur**
- Vérifiez que le serveur Laravel fonctionne
- Vérifiez les logs Laravel

## 📞 Support

Pour toute question concernant cette collection :
- Vérifiez d'abord ce README
- Testez les endpoints un par un
- Consultez les logs du serveur
- Vérifiez la documentation API complète

---

**Collection créée le :** 10 octobre 2025
**Version API :** 2.0
**Environnement :** Laravel 11.x + PostgreSQL