<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserActionLog;
use Illuminate\Support\Facades\Storage;

class ExportUserLogs extends Command
{
    // Nom de la commande pour l'appeler dans le terminal
    protected $signature = 'logs:export';

    // Description de la commande
    protected $description = 'Exporter les logs d\'actions utilisateur dans un fichier CSV';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Récupérer les logs
        $logs = UserActionLog::all();

        // Créer un tableau pour stocker les données CSV
        $csvData = [];
        $csvData[] = ['ID', 'Utilisateur', 'Action', 'Détails', 'Couleur du Log', 'Date de Création'];

        foreach ($logs as $log) {
            $userEmail = $log->user ? $log->user->email : 'Utilisateur supprimé'; // Si l'utilisateur n'existe pas, afficher 'Utilisateur supprimé'

            $csvData[] = [
                $log->id,
                $userEmail,
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
        $filePath = 'logs/user_action_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Stocker le fichier CSV
        Storage::disk('local')->put($filePath, $csvContent);

        // Message de succès
        $this->info('Les logs utilisateur ont été exportés avec succès vers ' . $filePath);
    }
}
