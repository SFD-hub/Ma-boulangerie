<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature   = 'backup:database';
    protected $description = 'Sauvegarde la base de données MySQL dans storage/app/backups';

    public function handle(): int
    {
        $config = config('database.connections.mysql');

        $host     = $config['host']     ?? '127.0.0.1';
        $port     = $config['port']     ?? 3306;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if (! $database) {
            $this->error('Configuration MySQL manquante.');
            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $cmd = sprintf(
            'mysqldump --host=%s --port=%d --user=%s %s',
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($username),
            escapeshellarg($database)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $filepath, 'w'],
            2 => ['pipe', 'w'],
        ];

        // Passe le mot de passe via variable d'environnement (évite l'exposition dans la liste de processus)
        $env     = array_merge(getenv() ?: [], ['MYSQL_PWD' => $password]);
        $process = proc_open($cmd, $descriptors, $pipes, null, $env);

        if (! is_resource($process)) {
            $this->error('Impossible de lancer mysqldump. Vérifiez que mysqldump est installé et accessible.');
            return self::FAILURE;
        }

        fclose($pipes[0]);
        $stderr   = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            @unlink($filepath);
            $this->error("mysqldump a échoué (code {$exitCode}) : {$stderr}");
            return self::FAILURE;
        }

        if (! file_exists($filepath) || filesize($filepath) === 0) {
            @unlink($filepath);
            $this->error('Le fichier de sauvegarde est vide ou absent.');
            return self::FAILURE;
        }

        $this->info("Sauvegarde créée : {$filename}");

        $this->pruneOldBackups($backupDir);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $dir): void
    {
        $files = glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql') ?: [];
        $keep  = config('backup.keep', 30);

        if (count($files) <= $keep) {
            return;
        }

        // Du plus ancien au plus récent
        usort($files, fn ($a, $b) => filemtime($a) <=> filemtime($b));

        $toDelete = array_slice($files, 0, count($files) - $keep);

        foreach ($toDelete as $file) {
            @unlink($file);
        }

        $this->line(count($toDelete) . ' ancienne(s) sauvegarde(s) supprimée(s).');
    }
}
