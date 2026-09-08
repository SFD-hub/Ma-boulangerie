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
