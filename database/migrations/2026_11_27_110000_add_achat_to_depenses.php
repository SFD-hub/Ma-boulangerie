<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lie la dépense automatique à son achat (au lieu de la retrouver par montant et date).
     */
    public function up(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->unsignedBigInteger('achat_matiere_premiere_id')->nullable()->index()->after('categorie');
        });

        // Reprise de l'existant : chaque achat réel reçoit sa dépense (même catégorie, montant, date), une à une.
        $achats = DB::table('achats_matieres_premieres as a')
            ->join('matieres_premieres as m', 'm.id', '=', 'a.matiere_premiere_id')
            ->where('a.depart', false)
            ->orderBy('a.id')
            ->select('a.id', 'a.montant', 'a.date_achat', 'm.boulangerie_id', 'm.nom', 'm.categorie_depense')
            ->get();

                foreach ($achats as $a) {
            $categorie = $a->categorie_depense ?: 'achat_stock';
            $id = DB::table('depenses')
                ->where('boulangerie_id', $a->boulangerie_id)
                ->where('categorie', $categorie)
                ->where('montant', $a->montant)
                ->whereDate('date_depense', substr((string) $a->date_achat, 0, 10))
                ->whereNull('achat_matiere_premiere_id')
                ->orderBy('id')
                ->value('id');
            if ($id) {
                DB::table('depenses')->where('id', $id)->update(['achat_matiere_premiere_id' => $a->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->dropColumn('achat_matiere_premiere_id');
        });
    }
};
