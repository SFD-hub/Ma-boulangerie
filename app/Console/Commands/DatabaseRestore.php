<?php

namespace App\Console\Commands;

use App\Support\MysqlImporter;
use Illuminate\Console\Command;

// Restauration DIRECTE sur la base de production. Toujours précédée d'une
// sauvegarde de sécurité de l'état actuel côté BackupController — et,
// idéalement, d'un passage par `backup:restore-verify` pour inspecter le
// contenu avant de basculer dessus.
class DatabaseRestore extends Command
{
    protected $signature   = 'backup:restore-production {filename : Nom du fichier dans storage/app/backups}';
    protected $description = 'Restaure la base de données de PRODUCTION à partir d\'une sauvegarde';

    public function handle(): int
    {
        $filename = $this->argument('filename');

        if (! preg_match('/^backup_\d{4}_\d{2}_\d{2}_\d{6}\.sql$/', $filename)) {
            $this->error('Nom de fichier de sauvegarde invalide.');
            return self::FAILURE;
        }

        $filepath = storage_path('app/backups') . DIRECTORY_SEPARATOR . $filename;

        if (! file_exists($filepath)) {
            $this->error("Fichier introuvable : {$filename}");
            return self::FAILURE;
        }

        $database = config('database.connections.mysql.database');

        if (! $database) {
            $this->error('Configuration MySQL manquante.');
            return self::FAILURE;
        }

        $result = MysqlImporter::import($database, $filepath);

        if (! $result['ok']) {
            $this->error("La restauration a échoué : {$result['stderr']}");
            return self::FAILURE;
        }

        $this->info("Base de production restaurée à partir de : {$filename}");

        return self::SUCCESS;
    }
}
