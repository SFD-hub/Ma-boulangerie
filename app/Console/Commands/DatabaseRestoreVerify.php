<?php

namespace App\Console\Commands;

use App\Support\MysqlImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Restaure une sauvegarde dans une base de VÉRIFICATION séparée (jamais la
// production), pour inspecter son contenu avant de décider de basculer
// dessus via `backup:restore-production`. Reproduit la procédure déjà
// éprouvée par l'utilisateur sur une autre application en production.
class DatabaseRestoreVerify extends Command
{
    protected $signature   = 'backup:restore-verify {filename : Nom du fichier dans storage/app/backups}';
    protected $description = 'Restaure une sauvegarde dans une base de vérification séparée (sans toucher à la production)';

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

        $productionDb  = config('database.connections.mysql.database');
        $verificationDb = $productionDb . '_verification';

        if (! $productionDb) {
            $this->error('Configuration MySQL manquante.');
            return self::FAILURE;
        }

        DB::statement("DROP DATABASE IF EXISTS `{$verificationDb}`");
        DB::statement("CREATE DATABASE `{$verificationDb}`");

        $result = MysqlImporter::import($verificationDb, $filepath);

        if (! $result['ok']) {
            $this->error("L'import de vérification a échoué : {$result['stderr']}");
            return self::FAILURE;
        }

        $this->info("Sauvegarde importée dans la base de vérification « {$verificationDb} ».");

        // Un vrai COUNT(*) par table plutôt que information_schema.TABLE_ROWS :
        // cette estimation InnoDB est notoirement peu fiable juste après un
        // import (elle a affiché 0 sur des tables réellement peuplées lors
        // des tests), ce qui rendrait la vérification trompeuse.
        $tableNames = collect(DB::select('SHOW TABLES FROM `' . $verificationDb . '`'))
            ->map(fn ($row) => array_values((array) $row)[0]);

        foreach ($tableNames as $table) {
            $count = DB::table($verificationDb . '.' . $table)->count();
            $this->line("  {$table} : {$count} ligne(s)");
        }

        return self::SUCCESS;
    }
}
