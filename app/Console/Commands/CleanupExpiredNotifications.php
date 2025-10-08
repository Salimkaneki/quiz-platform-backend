<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlatformNotificationService;

class CleanupExpiredNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup 
                            {--dry-run : Afficher seulement ce qui serait supprimé}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les notifications de plateforme expirées';

    protected $notificationService;

    public function __construct(PlatformNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode simulation - Aucune suppression ne sera effectuée');
        }

        // Compter les notifications expirées
        $expiredCount = \App\Models\PlatformNotification::expired()->count();

        if ($expiredCount === 0) {
            $this->info('✅ Aucune notification expirée trouvée');
            return 0;
        }

        $this->info("📊 {$expiredCount} notifications expirées trouvées");

        if ($dryRun) {
            // Afficher un aperçu des notifications qui seraient supprimées
            $expiredNotifications = \App\Models\PlatformNotification::expired()
                ->with('user')
                ->limit(10)
                ->get();

            $this->table(
                ['ID', 'Utilisateur', 'Type', 'Titre', 'Expiré le'],
                $expiredNotifications->map(function ($notification) {
                    return [
                        $notification->id,
                        $notification->user->name ?? 'N/A',
                        $notification->type,
                        $notification->title,
                        $notification->expires_at?->format('d/m/Y H:i') ?? 'N/A'
                    ];
                })
            );

            if ($expiredCount > 10) {
                $this->info("... et " . ($expiredCount - 10) . " autres");
            }

            return 0;
        }

        // Supprimer les notifications expirées
        $deletedCount = $this->notificationService->cleanupExpired();

        $this->info("🗑️  {$deletedCount} notifications expirées supprimées avec succès");

        // Statistiques supplémentaires
        $totalNotifications = \App\Models\PlatformNotification::count();
        $unreadCount = \App\Models\PlatformNotification::unread()->count();

        $this->info("📈 Statistiques après nettoyage :");
        $this->info("   • Total notifications : {$totalNotifications}");
        $this->info("   • Non lues : {$unreadCount}");

        return 0;
    }
}
