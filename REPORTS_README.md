# Système de Rapports Administrateur

Ce système permet d'envoyer automatiquement des rapports détaillés sur les résultats des quiz aux administrateurs de l'institution.

## Fonctionnalités

### 1. Rapport par Session
Envoie un rapport détaillé pour une session spécifique avec :
- Liste complète des participants et leurs résultats
- Statistiques générales (moyenne, taux de réussite, etc.)
- Détails par étudiant (nom, classe, score, statut)

### 2. Rapports Périodiques
Génère des rapports automatiques pour :
- **Quotidien** : Sessions terminées dans la journée
- **Hebdomadaire** : Sessions terminées dans la semaine
- **Mensuel** : Sessions terminées dans le mois

### 3. Automatisation
- Commande Artisan pour l'envoi automatique
- Jobs en file d'attente pour les performances
- Notifications par email avec tableaux HTML
- Notifications via la plateforme (dashboard administrateur)

## API Endpoints

### Rapports
```
GET  /admin/reports/sessions                    # Lister sessions disponibles
POST /admin/reports/sessions/{id}/send          # Envoyer rapport session
POST /admin/reports/periodic                    # Rapport périodique
```

### Notifications de plateforme
```
GET  /admin/notifications                       # Lister notifications
GET  /admin/notifications/unread-count          # Compter notifications non lues
PATCH /admin/notifications/{id}/read            # Marquer comme lue
PATCH /admin/notifications/bulk-read            # Marquer plusieurs comme lues
PATCH /admin/notifications/all-read             # Tout marquer comme lu
DELETE /admin/notifications/{id}                # Supprimer notification
POST /admin/notifications/cleanup               # Nettoyer expirées
```

## Commandes Artisan

### Envoi manuel des rapports périodiques
```bash
php artisan reports:send-periodic {period} [--date=YYYY-MM-DD]
```

Exemples :
```bash
# Rapport quotidien pour aujourd'hui
php artisan reports:send-periodic daily

# Rapport hebdomadaire pour une date spécifique
php artisan reports:send-periodic weekly --date=2025-10-01

# Rapport mensuel
php artisan reports:send-periodic monthly
```

### Nettoyage automatique
```bash
# Nettoyage quotidien des notifications expirées
php artisan notifications:cleanup

# Mode simulation (voir ce qui serait supprimé)
php artisan notifications:cleanup --dry-run
```

### Planification recommandée (dans `app/Console/Kernel.php`)
```php
protected function schedule(Schedule $schedule)
{
    // Rapports quotidiens
    $schedule->command('reports:send-periodic daily')->dailyAt('06:00');
    
    // Nettoyage des notifications expirées
    $schedule->command('notifications:cleanup')->dailyAt('02:00');
    
    // Rapports hebdomadaires
    $schedule->command('reports:send-periodic weekly')->weeklyOn(1, '07:00');
    
    // Rapports mensuels
    $schedule->command('reports:send-periodic monthly')->monthlyOn(1, '08:00');
}
```

## Configuration

### File d'attente (Queue)
Assurez-vous que les jobs sont configurés :
```bash
php artisan queue:work
```

### Email
Configurez le système d'email dans `.env` :
```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="Plateforme Quiz"
```

## Structure des Rapports

### Rapport de Session
- **En-tête** : Titre de la session, quiz, enseignant, dates
- **Statistiques** : Nombre de participants, scores moyens, etc.
- **Tableau détaillé** : Liste des étudiants avec résultats

### Rapport Périodique
- **Statistiques globales** : Total sessions, participants, moyennes
- **Détail par session** : Chaque session avec ses statistiques
- **Top 10** : Meilleurs résultats de la période

## Sécurité

- Seuls les administrateurs pédagogiques peuvent envoyer des rapports
- Vérification de l'appartenance des sessions à l'institution
- Logs détaillés pour le suivi

## Personnalisation

Les notifications peuvent être personnalisées en modifiant :
- `SessionResultsReportNotification.php` pour les rapports de session
- `PeriodicResultsReportNotification.php` pour les rapports périodiques

## Exemple d'utilisation

1. **Rapport immédiat pour une session** :
   ```bash
   curl -X POST /admin/reports/sessions/123/send \
        -H "Authorization: Bearer {token}"
   ```

2. **Rapport périodique automatique** :
   Ajouter dans le scheduler Laravel pour une exécution automatique.

## Notifications de Plateforme

### Types de notifications
- **Rapport disponible** : Nouveau rapport généré automatiquement
- **Session terminée** : Session d'examen finalisée avec résultats
- **Alerte système** : Notifications techniques importantes

### Gestion des notifications
- **Expiration** : Les notifications expirent automatiquement (7-30 jours)
- **Marquage** : Possibilité de marquer comme lue individuellement ou en masse
- **Nettoyage** : Suppression automatique des notifications expirées

### Intégration Frontend
```javascript
// Récupérer les notifications
fetch('/admin/notifications')
  .then(response => response.json())
  .then(data => {
    console.log('Notifications:', data.notifications);
    console.log('Non lues:', data.unread_count);
  });

// Marquer comme lue
fetch(`/admin/notifications/${id}/read`, { method: 'PATCH' });

// Marquer toutes comme lues
fetch('/admin/notifications/all-read', { method: 'PATCH' });
```

## Exemple complet d'utilisation

1. **Session terminée automatiquement** :
   - Quand une session se termine, un job est déclenché
   - Rapport envoyé par email aux administrateurs
   - Notification créée dans la plateforme

2. **Vérification côté administrateur** :
   ```bash
   # Voir les sessions disponibles
   GET /admin/reports/sessions

   # Envoyer un rapport manuellement
   POST /admin/reports/sessions/123/send

   # Consulter les notifications
   GET /admin/notifications
   ```

3. **Rapports périodiques** :
   ```bash
   # Rapport quotidien
   php artisan reports:send-periodic daily

   # Avec planification Laravel
   # Ajouter dans app/Console/Kernel.php
   $schedule->command('reports:send-periodic daily')->dailyAt('06:00');
   ```