<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    public function index(): View
    {
        $files = [];

        if (is_dir($this->backupDir)) {
            $raw = glob($this->backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql') ?: [];
            rsort($raw);

            foreach ($raw as $file) {
                $files[] = [
                    'name'    => basename($file),
                    'size'    => filesize($file),
                    'created' => filemtime($file),
                ];
            }
        }

        return view('super-admin.backups.index', compact('files'));
    }

    public function create(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('backup:database');

            if ($exitCode === 0) {
                ActivityLog::record('backup_manuelle', 'Super Admin a créé une sauvegarde manuelle');
                return back()->with('success', 'Sauvegarde créée avec succès.');
            }

            return back()->with('error', 'La sauvegarde a échoué. Vérifiez que mysqldump est disponible sur le serveur.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }

    public function download(string $filename): BinaryFileResponse
    {
        $this->validateFilename($filename);

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        abort_if(! file_exists($path), 404);

        return response()->download($path);
    }

    // ── Étape 1 : restaurer dans une base de vérification séparée ───────────
    // Ne touche jamais à la production — sert uniquement à inspecter le
    // contenu d'une sauvegarde avant de décider de basculer dessus.
    public function verify(string $filename): RedirectResponse
    {
        $this->validateFilename($filename);

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        abort_if(! file_exists($path), 404);

        try {
            Artisan::call('backup:restore-verify', ['filename' => $filename]);
            $output = trim(Artisan::output());

            $database = config('database.connections.mysql.database') . '_verification';

            ActivityLog::record('backup_verifiee', "Super Admin a importé la sauvegarde {$filename} dans la base de vérification");

            return back()->with('success', "Sauvegarde importée dans la base de vérification « {$database} ». Contenu :\n{$output}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors de la vérification : ' . $e->getMessage());
        }
    }

    // ── Étape 2 : basculer réellement la production sur cette sauvegarde ────
    // Irréversible sans une nouvelle restauration : une sauvegarde de
    // sécurité de l'état actuel est donc toujours prise juste avant.
    public function promote(string $filename): RedirectResponse
    {
        $this->validateFilename($filename);

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        abort_if(! file_exists($path), 404);

        $safetyExitCode = Artisan::call('backup:database');
        if ($safetyExitCode !== 0) {
            return back()->with('error', 'Restauration annulée : impossible de créer la sauvegarde de sécurité préalable.');
        }

        try {
            $exitCode = Artisan::call('backup:restore-production', ['filename' => $filename]);

            if ($exitCode === 0) {
                ActivityLog::record('backup_restauree', "Super Admin a restauré la base de PRODUCTION depuis la sauvegarde {$filename}");
                return back()->with('success', "Base de production restaurée à partir de « {$filename} ». Une sauvegarde de l'état précédent a été créée automatiquement.");
            }

            return back()->with('error', 'La restauration a échoué. Consultez les logs pour le détail.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors de la restauration : ' . $e->getMessage());
        }
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->validateFilename($filename);

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        abort_if(! file_exists($path), 404);

        @unlink($path);

        ActivityLog::record('backup_supprimee', "Super Admin a supprimé la sauvegarde {$filename}");

        return back()->with('success', "Sauvegarde « {$filename} » supprimée.");
    }

    // Prévenir l'injection de chemin — seul le pattern exact est accepté
    private function validateFilename(string $filename): void
    {
        abort_if(! preg_match('/^backup_\d{4}_\d{2}_\d{2}_\d{6}\.sql$/', $filename), 403);
    }
}
