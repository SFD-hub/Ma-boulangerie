<?php

return [

    // Nombre de sauvegardes locales conservées avant suppression automatique
    // des plus anciennes (voir app/Console/Commands/DatabaseBackup.php).
    'keep' => (int) env('BACKUP_KEEP', 30),

];
