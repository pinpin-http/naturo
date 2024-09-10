<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserActionLog;
use Illuminate\Support\Facades\Storage;

class ExportUserSpecificLogs extends Command
{
    // Nom de la commande pour l'appeler dans le terminal
    protected $signature = 'logs:exportuser {email : L\'email de l\'utilisateur}';

    // Description de la commande
    protected $description = 'Exporter les logs d\'actions pour un utilisateur spécifique dans un fichier CSV';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Récupérer l'utilisateur en fonction de l'email passé en argument
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        // Si l'utilisateur n'existe pas, afficher un message d'erreur
        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email : $email");
            return;
        }

        // Récupérer les logs de cet utilisateur
        $logs = UserActionLog::where('user_id', $user->id)->get();

        // Si aucun log n'est trouvé, afficher un message d'information
        if ($logs->isEmpty()) {
            $this->info("Aucun log trouvé pour l'utilisateur : $email");
            return;
        }

        // Créer un tableau pour stocker les données CSV
        $csvData = [];
        $csvData[] = ['ID', 'Utilisateur', 'Action', 'Détails', 'Couleur du Log', 'Date de Création'];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $user->email,
                $log->action,
                $log->details,
                $log->log_color,
                $log->created_at->toDateTimeString(),
            ];
        }

        // Convertir le tableau en une chaîne CSV
        $csvContent = implode("\n", array_map(function($row) {
            return implode(',', $row);
        }, $csvData));

        // Définir le chemin où le fichier CSV sera stocké
        $filePath = 'logs/user_action_logs_' . $user->username . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Stocker le fichier CSV
        Storage::disk('local')->put($filePath, $csvContent);

        // Message de succès
        $this->info('Les logs de l\'utilisateur ' . $user->email . ' ont été exportés avec succès vers ' . $filePath);
    }
}
