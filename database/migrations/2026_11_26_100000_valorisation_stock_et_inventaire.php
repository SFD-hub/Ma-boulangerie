<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bénéfice calculé sur ce qui est CONSOMMÉ (et non sur ce qui est acheté) :
//  - valeur du stock suivie en prix moyen pondéré (matieres_premieres.valeur_stock) ;
//  - coût des matières consommées enregistré à chaque production / « terminé » ;
//  - inventaire : écarts de stock comptés comme pertes (ou gains) du mois ;
//  - stock de départ : entrée de stock sans dépense (achats.depart) ;
//  - bilan : lignes de consommation, écarts de stock et achats (pour information).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matieres_premieres', function (Blueprint $table) {
            $table->decimal('valeur_stock', 14, 2)->default(0)->after('stock_actuel');
        });

        Schema::table('achats_matieres_premieres', function (Blueprint $table) {
            $table->boolean('depart')->default(false)->after('date_achat');
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('cout_farine', 12, 2)->default(0);
            $table->decimal('cout_levure', 12, 2)->default(0);
            $table->decimal('cout_produit', 12, 2)->default(0);
        });

        Schema::table('retraits_stock', function (Blueprint $table) {
            $table->decimal('cout', 12, 2)->default(0);
        });

        Schema::create('inventaires_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_inventaire');
            $table->decimal('quantite_avant', 10, 2);
            $table->decimal('quantite_comptee', 10, 2);
            $table->decimal('ecart', 10, 2);
            $table->decimal('cout_unitaire', 12, 4)->default(0);
            // Valeur de l'écart (négatif = perte, positif = gain).
            $table->decimal('valeur_ecart', 14, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['boulangerie_id', 'date_inventaire']);
        });

        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->string('methode', 20)->default('achats');
            $table->decimal('conso_farine', 12, 2)->default(0);
            $table->decimal('conso_levure', 12, 2)->default(0);
            $table->decimal('conso_produit', 12, 2)->default(0);
            $table->decimal('conso_sel', 12, 2)->default(0);
            $table->decimal('conso_huile', 12, 2)->default(0);
            $table->decimal('conso_gasoil', 12, 2)->default(0);
            $table->decimal('ecarts_stock', 12, 2)->default(0);
            $table->decimal('achats_stock', 12, 2)->default(0);
        });

        // ── Reprise des données existantes ──────────────────────────────────
        // Prix moyen pondéré de chaque produit = total payé / total acheté.
        $prixMoyen = [];
        foreach (DB::table('matieres_premieres')->get() as $mp) {
            $a = DB::table('achats_matieres_premieres')->where('matiere_premiere_id', $mp->id)
                ->selectRaw('COALESCE(SUM(montant),0) as m, COALESCE(SUM(quantite),0) as q')->first();
            $prix = (float) $a->q > 0 ? (float) $a->m / (float) $a->q : 0.0;
            $prixMoyen[$mp->boulangerie_id][$mp->nom] = $prix;

            DB::table('matieres_premieres')->where('id', $mp->id)
                ->update(['valeur_stock' => round((float) $mp->stock_actuel * $prix, 2)]);
        }

        // Coût des productions et des « terminé » déjà saisis : estimé au prix moyen.
        foreach (DB::table('productions')->get() as $p) {
            $pm = $prixMoyen[$p->boulangerie_id] ?? [];
            DB::table('productions')->where('id', $p->id)->update([
                'cout_farine'  => round((float) $p->quantite_farine * ($pm['Farine'] ?? 0), 2),
                'cout_levure'  => round((float) $p->quantite_levure * ($pm['Levure'] ?? 0), 2),
                'cout_produit' => round((float) $p->quantite_produit * ($pm['Produit'] ?? 0), 2),
            ]);
        }

        foreach (DB::table('retraits_stock as r')->join('matieres_premieres as m', 'm.id', '=', 'r.matiere_premiere_id')
            ->select('r.id', 'm.boulangerie_id', 'm.nom')->get() as $r) {
            DB::table('retraits_stock')->where('id', $r->id)
                ->update(['cout' => round($prixMoyen[$r->boulangerie_id][$r->nom] ?? 0, 2)]);
        }
    }

    public function down(): void
    {
        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->dropColumn(['methode', 'conso_farine', 'conso_levure', 'conso_produit', 'conso_sel', 'conso_huile', 'conso_gasoil', 'ecarts_stock', 'achats_stock']);
        });
        Schema::dropIfExists('inventaires_stock');
        Schema::table('retraits_stock', function (Blueprint $table) {
            $table->dropColumn('cout');
        });
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['cout_farine', 'cout_levure', 'cout_produit']);
        });
        Schema::table('achats_matieres_premieres', function (Blueprint $table) {
            $table->dropColumn('depart');
        });
        Schema::table('matieres_premieres', function (Blueprint $table) {
            $table->dropColumn('valeur_stock');
        });
    }
};
