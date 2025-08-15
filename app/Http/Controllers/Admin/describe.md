# Gestion des Utilisateurs et Administrateurs

## Vue d'ensemble

Le système utilise **deux tables séparées** pour gérer les utilisateurs et leurs rôles administratifs :
- `users` : Informations de base des utilisateurs
- `administrators` : Rôles administratifs spécifiques par institution

## Structure des données

### Table `users`
```sql
- id (Primary Key)
- name
- email (unique)
- password
- account_type (enum: admin, teacher, student)
- is_active (boolean, default: true)
```

### Table `administrators`
```sql
- id (Primary Key)
- user_id (Foreign Key → users.id)
- institution_id (Foreign Key → institutions.id)
- type (enum: direction, pedagogique, scolarite)
- permissions (JSON, nullable)
```

## Types d'administrateurs

| Type | Permissions | Description |
|------|-------------|-------------|
| `direction` | Toutes | Peut gérer tous les autres administrateurs |
| `pedagogique` | Enseignants | Peut gérer les enseignants de son institution |
| `scolarite` | Étudiants | Peut gérer les étudiants de son institution |

## API Endpoints

### 👤 Gestion des Utilisateurs (`/api/users`)

#### Créer un utilisateur
```http
POST /api/users
Content-Type: application/json

{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "password": "password123",
  "account_type": "admin",
  "phone": "0123456789",
  "address": "123 Rue de l'École"
}
```

#### Lister les utilisateurs
```http
GET /api/users
GET /api/users?account_type=admin
GET /api/users?search=jean
```

#### Voir un utilisateur
```http
GET /api/users/{id}
```

#### Modifier un utilisateur
```http
PUT /api/users/{id}
{
  "name": "Jean Martin",
  "email": "jean.martin@example.com"
}
```

#### Supprimer un utilisateur
```http
DELETE /api/users/{id}
```

### 🔐 Gestion des Administrateurs (`/api/administrators`)

#### Créer un administrateur
```http
POST /api/administrators
Content-Type: application/json

{
  "user_id": 1,
  "institution_id": 1,
  "type": "direction",
  "permissions": ["gestion_cours", "planification"]
}
```

#### Lister les administrateurs
```http
GET /api/administrators
GET /api/administrators?type=pedagogique
GET /api/administrators?institution_id=1
```

#### Voir un administrateur
```http
GET /api/administrators/{id}
```

#### Modifier un administrateur
```http
PUT /api/administrators/{id}
{
  "type": "pedagogique",
  "permissions": ["gestion_cours"]
}
```

#### Supprimer un administrateur
```http
DELETE /api/administrators/{id}
```

## Processus de création d'un administrateur

### Étape 1 : Créer l'utilisateur de base
```bash
POST /api/users
{
  "name": "Admin Principal",
  "email": "admin@institution1.com",
  "password": "password123",
  "account_type": "admin"
}
# Réponse : { "id": 1, ... }
```

### Étape 2 : Lui attribuer un rôle administratif
```bash
POST /api/administrators
{
  "user_id": 1,
  "institution_id": 1,
  "type": "direction"
}
```

## Sécurité et permissions

### Contrôle d'accès par endpoint

#### UserController
- **Lecture** : Seuls les administrateurs
- **Création** : Libre (pour bootstrap initial)
- **Modification/Suppression** : Seuls les administrateurs

#### AdministratorController
- **Toutes les actions** : Seuls les utilisateurs avec `account_type = 'admin'`

### Règles métier

1. **Unicité** : Un utilisateur ne peut pas être administrateur du même type dans la même institution
2. **Validation** : Seuls les utilisateurs avec `account_type = 'admin'` peuvent devenir administrateurs
3. **Contraintes** : Un utilisateur lié à des rôles ne peut pas être supprimé

## Exemples d'utilisation

### Créer le premier administrateur de direction
```bash
# 1. Créer l'utilisateur
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Directeur Général",
    "email": "directeur@institution1.com",
    "password": "password123",
    "account_type": "admin"
  }'

# 2. Lui donner le rôle de direction
curl -X POST http://localhost:8000/api/administrators \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "institution_id": 1,
    "type": "direction"
  }'
```

### Créer un administrateur pédagogique
```bash
# 1. Créer l'utilisateur
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Chef Pédagogique",
    "email": "pedagogique@institution1.com",
    "password": "password123",
    "account_type": "admin"
  }'

# 2. Lui donner le rôle pédagogique
curl -X POST http://localhost:8000/api/administrators \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "institution_id": 1,
    "type": "pedagogique"
  }'
```

## Hiérarchie des permissions

```
Admin Direction
├── Peut créer/modifier tous les administrateurs
├── Peut gérer tous les enseignants
└── Peut gérer tous les étudiants

Admin Pédagogique
├── Peut créer/modifier les enseignants de son institution
└── Peut voir les étudiants de son institution

Admin Scolarité
├── Peut créer/modifier les étudiants de son institution
└── Peut voir les enseignants de son institution
```

## Codes d'erreur courants

| Code | Message | Solution |
|------|---------|----------|
| 400 | L'utilisateur doit avoir le type admin | Vérifier que `account_type = 'admin'` |
| 403 | Seuls les administrateurs peuvent... | Se connecter avec un compte admin |
| 409 | Cet utilisateur est déjà administrateur... | Vérifier l'unicité user_id + institution_id + type |
| 422 | Validation failed | Vérifier les champs requis et formats |

## Bootstrap initial

Pour initialiser le système :

1. **Créer une institution** (via seeder ou manuellement en BDD)
2. **Créer le premier utilisateur admin** via `POST /api/users`
3. **Lui donner le rôle direction** via `POST /api/administrators`
4. **Il peut ensuite créer d'autres administrateurs** via l'interface

## Relation avec les autres entités

```
User (account_type: admin) 
  → Administrator (type: pedagogique)
    → Teacher (dans la même institution)

User (account_type: teacher)
  → Teacher (géré par admin pédagogique)

User (account_type: student)
  → Student (géré par admin scolarité)
```