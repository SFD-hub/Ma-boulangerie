<?php

use App\Support\Telephone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les numéros des comptes existants passent à l'écriture unique (chiffres seulement, sans 221). Si deux
     * comptes se retrouvent avec le même numéro après normalisation, le plus ancien est normalisé et le
     * second est laissé tel quel (il faudra alors changer l'un des deux numéros à la main).
     */
    public function up(): void
    {
        $pris = [];
        foreach (DB::table('users')->whereNotNull('telephone')->orderBy('id')->get(['id', 'telephone']) as $u) {
            $norme = Telephone::normaliser($u->telephone);
            if ($norme === null || $norme === '' || $norme === $u->telephone) {
                $pris[$u->telephone] = true;
                continue;
            }
            if (isset($pris[$norme]) || DB::table('users')->where('telephone', $norme)->where('id', '!=', $u->id)->exists()) {
                continue; // conflit : on ne touche pas
            }
            DB::table('users')->where('id', $u->id)->update(['telephone' => $norme]);
            $pris[$norme] = true;
        }
    }

    public function down(): void
    {
        // La forme d'origine n'est pas conservée : rien à restaurer.
    }
};