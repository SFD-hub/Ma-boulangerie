<?php

namespace App\Support;

// Import d'un fichier .sql dans une base MySQL via le client `mysql` en
// sous-processus (mot de passe transmis par variable d'environnement, jamais
// en argument de commande). Partagé par les commandes de restauration
// (vérification et production) pour éviter deux implémentations divergentes
// d'un mécanisme aussi sensible.
class MysqlImporter
{
    public static function import(string $database, string $filepath): array
    {
        $config = config('database.connections.mysql');

        $host     = $config['host']     ?? '127.0.0.1';
        $port     = $config['port']     ?? 3306;
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        $cmd = sprintf(
            'mysql --host=%s --port=%d --user=%s %s',
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($username),
            escapeshellarg($database)
        );

        $descriptors = [
            0 => ['file', $filepath, 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env     = array_merge(getenv() ?: [], ['MYSQL_PWD' => $password]);
        $process = proc_open($cmd, $descriptors, $pipes, null, $env);

        if (! is_resource($process)) {
            return ['ok' => false, 'stderr' => 'Impossible de lancer mysql. Vérifiez que le client mysql est installé et accessible.'];
        }

        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return ['ok' => $exitCode === 0, 'stderr' => $stderr];
    }
}
