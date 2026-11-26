<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock étendu : Produit (paquets), Sel (sacs), Huile (bidons), Gasoil (bidons).
 *
 * - matieres_premieres : unité d'affichage, catégorie de dépense créée à
 *   l'achat, et mode de suivi (déduit à la production OU retiré à la main
 *   quand le bidon/sac est terminé).
 * - retraits_stock : historique des "bidon/sac terminé" (qui, quand, durée).
 * - productions.quantite_produit : paquets de "Produit" consommés.
 * - bilans_financiers : achats stock supplémentaires.
 *
 * Les données existantes (Farine, Levure, achats, dépenses) ne sont pas touchées,
 * hormis leur seuil d'alerte (15) et leur unité.
 */
return new class extends Migration
{
    private const CATALOGUE = [
        // nom        unité     catégorie de dépense  suivi par unité  seuil
        ['Produit', 'paquet', 'achat_produit', false, 15],
        ['Sel',     'sac',    'achat_sel',     true,  2],
        ['Huile',   'bidon',  'achat_huile',   true,  2],
        ['Gasoil',  'bidon',  'achat_gasoil',  true,  2],
    ];

    public function up(): void
    {
        Schema::table('matieres_premieres', function (Blueprint $table) {
            $table->string('unite', 20)->default('unité')->after('nom');
            $table->string('categorie_depense', 40)->nullable()->after('unite');
            $table->boolean('retrait_manuel')->default(false)->after('categorie_depense');
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('quantite_produit', 8, 2)->default(0)->after('quantite_levure');
        });

        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->decimal('achat_produit', 12, 2)->default(0)->after('achat_levure');
            $table->decimal('achat_sel',     12, 2)->default(0)->after('achat_produit');
            $table->decimal('achat_huile',   12, 2)->default(0)->after('achat_sel');
            $table->decimal('achat_gasoil',  12, 2)->default(0)->after('achat_huile');
        });

        Schema::create('retraits_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_retrait');
            $table->date('debut_le')->nullable();      // début d'utilisation de l'unité terminée
            $table->decimal('stock_apres', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['matiere_premiere_id', 'date_retrait']);
        });

        // Farine et Levure : unité, catégorie de dépense (celles déjà utilisées
        // par les achats existants) et seuil d'alerte à 15.
        DB::table('matieres_premieres')->where('nom', 'Farine')->update([
            'unite' => 'sac', 'categorie_depense' => 'achat_farine', 'seuil_alerte' => 15,
        ]);
        DB::table('matieres_premieres')->where('nom', 'Levure')->update([
            'unite' => 'paquet', 'categorie_depense' => 'achat_levure', 'seuil_alerte' => 15,
        ]);

        // Nouveaux produits pour chaque boulangerie existante (sans doublon).
        $now = now();
        foreach (DB::table('boulangeries')->pluck('id') as $boulangerieId) {
            foreach (self::CATALOGUE as [$nom, $unite, $categorie, $retraitManuel, $seuil]) {
                $existe = DB::table('matieres_premieres')
                    ->where('boulangerie_id', $boulangerieId)
                    ->where('nom', $nom)
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('matieres_premieres')->insert([
                    'boulangerie_id'    => $boulangerieId,
                    'nom'               => $nom,
                    'unite'             => $unite,
                    'categorie_depense' => $categorie,
                    'retrait_manuel'    => $retraitManuel,
                    'stock_actuel'      => 0,
                    'seuil_alerte'      => $seuil,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('retraits_stock');

        // Les achats déjà saisis sur les nouveaux produits sont supprimés avec
        // eux (cascade), de même que les dépenses qu'ils avaient créées.
        DB::table('depenses')->whereIn('categorie', ['achat_produit', 'achat_sel', 'achat_huile', 'achat_gasoil'])->delete();
        DB::table('matieres_premieres')->whereIn('nom', ['Produit', 'Sel', 'Huile', 'Gasoil'])->delete();

        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->dropColumn(['achat_produit', 'achat_sel', 'achat_huile', 'achat_gasoil']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn('quantite_produit');
        });

        Schema::table('matieres_premieres', function (Blueprint $table) {
            $table->dropColumn(['unite', 'categorie_depense', 'retrait_manuel']);
        });
    }
};
