<?php
/**
 * RÉPÉTITION d'une mise à jour sur une COPIE de la base de production (jamais sur la vraie).
 *
 *   1. Copier la base de production dans une base de test :
 *        mysqldump ... base_prod > prod.sql        (sur le serveur)
 *        mysql -e "CREATE DATABASE copie_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
 *        mysql copie_test < prod.sql               (sur votre machine)
 *   2. DB_DATABASE=copie_test php deploy/repetition-migration.php avant
 *   3. DB_DATABASE=copie_test php artisan migrate --force
 *   4. DB_DATABASE=copie_test php deploy/repetition-migration.php apres
 *
 * « apres » compare avec « avant » (lignes, argent, stocks, bilans enregistrés), ouvre toutes les pages
 * avec les anciennes données, et vérifie que les comptes (argent, stock, valeur du stock) sont cohérents.
 * Rien n'est modifié dans la base (les pages sont seulement lues).
 */
$base = getenv('DB_DATABASE');
if (! $base || in_array($base, ['gestion_boulangerie'], true) || str_contains($base, 'prod')) {
    fwrite(STDERR, "REFUS : indiquez une base de COPIE (DB_DATABASE=copie_test), jamais la base de production.\n");
    exit(1);
}
$mode = $argv[1] ?? '';
if (! in_array($mode, ['avant', 'apres'], true)) { fwrite(STDERR, "Usage : php deploy/repetition-migration.php avant|apres\n"); exit(1); }

chdir(dirname(__DIR__));
require 'vendor/autoload.php';
putenv('APP_ENV=testing'); $_ENV['APP_ENV'] = 'testing'; $_SERVER['APP_ENV'] = 'testing';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema, Route}; use Illuminate\Http\Request;

echo "Base : " . DB::selectOne('select database() d')->d . "\n";
$fichier = sys_get_temp_dir() . '/repetition_' . preg_replace('/\W/', '_', $base) . '.json';
$tables = array_map(fn ($o) => array_values((array) $o)[0], DB::select('SHOW TABLES'));
$photo = [];
foreach ($tables as $t) {
    if (in_array($t, ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'migrations', 'password_reset_tokens'], true)) continue;
    $photo['lignes'][$t] = (int) DB::table($t)->count();
}
$somme = fn ($t, $c) => Schema::hasTable($t) && Schema::hasColumn($t, $c) ? round((float) DB::table($t)->sum($c), 2) : null;
foreach ([['versements', 'montant_verse'], ['distributions', 'montant_attendu'], ['distributions', 'reliquat'], ['distributions', 'nombre_pains'], ['depenses', 'montant'], ['depot_ventes', 'montant'], ['paiements_factures', 'montant'], ['factures', 'montant_total'], ['achats_matieres_premieres', 'montant'], ['achats_matieres_premieres', 'quantite'], ['productions', 'nombre_pains_produits'], ['consommations_abonnes', 'quantite'], ['matieres_premieres', 'stock_actuel'], ['bilans_financiers', 'recettes_total'], ['bilans_financiers', 'depenses_total'], ['bilans_financiers', 'benefice']] as [$t, $c]) $photo['sommes']["$t.$c"] = $somme($t, $c);

if ($mode === 'avant') {
    file_put_contents($fichier, json_encode($photo));
    echo "Photo « avant » enregistrée (" . array_sum($photo['lignes']) . " lignes dans " . count($photo['lignes']) . " tables). Lancez maintenant : DB_DATABASE=$base php artisan migrate --force\n";
    exit(0);
}

if (! file_exists($fichier)) { fwrite(STDERR, "Lancez d'abord « avant » (avant la migration).\n"); exit(1); }
$avant = json_decode(file_get_contents($fichier), true);
$ok = 0; $ko = 0;
$t = function ($nom, $cond, $d = '') use (&$ok, &$ko) { echo ($cond ? '  OK  ' : '  KO  ') . $nom . ($cond ? '' : "  $d") . "\n"; $cond ? $ok++ : $ko++; };

echo "\n== 1. Rien n'a été perdu ni modifié ==\n";
$ecarts = [];
foreach ($avant['lignes'] as $tb => $n) {
    $apres = $photo['lignes'][$tb] ?? null;
    // La mise à jour crée Produit, Sel, Huile et Gasoil pour chaque boulangerie : seule augmentation attendue.
    if ($tb === 'matieres_premieres' && $apres !== null && $apres >= $n) { echo "      (matieres_premieres : $n → $apres : produits de stock ajoutés par la mise à jour, normal)\n"; continue; }
    if ($apres !== $n) $ecarts[] = "$tb : $n → " . json_encode($apres);
}
$t(count($avant['lignes']) . ' tables : aucune ligne perdue ni ajoutée par erreur', empty($ecarts), implode(' | ', $ecarts));
$ecarts = [];
foreach ($avant['sommes'] as $k => $v) { if ($v === null) continue; if (abs(($photo['sommes'][$k] ?? 0) - $v) > 0.005) $ecarts[] = "$k : $v → " . json_encode($photo['sommes'][$k] ?? null); }
$t(count(array_filter($avant['sommes'], fn ($v) => $v !== null)) . ' totaux (argent, pains, stocks, bilans enregistrés) identiques', empty($ecarts), implode(' | ', $ecarts));

echo "\n== 2. L'application ouvre toutes les anciennes données ==\n";
$proprio = DB::table('users')->join('roles', 'roles.id', '=', 'users.role_id')->where('roles.nom', 'proprietaire')->whereNotNull('users.boulangerie_id')->select('users.id', 'users.boulangerie_id')->first();
if (! $proprio) { $t('un propriétaire existe dans cette base', false); }
else {
    $B = (int) $proprio->boulangerie_id;
    $envoyer = function ($url) use ($kernel, $app, $proprio) { $app['cache']->flush(); $app['session.store']->flush(); $app['auth']->guard('web')->forgetUser(); $app['auth']->guard('web')->setUser(App\Models\User::find($proprio->id)); try { return $kernel->handle(Request::create($url, 'GET')); } catch (Throwable $e) { return new Illuminate\Http\Response('EXC ' . $e->getMessage(), 500); } };
    $map = ['livreur' => 'livreurs', 'client' => 'livreurs', 'client_abonne' => 'clients_abonnes', 'depense' => 'depenses', 'distribution' => 'distributions', 'versement' => 'versements', 'facture' => 'factures', 'production' => 'productions', 'produit' => 'produits', 'matiere_premiere' => 'matieres_premieres', 'achat_matiere_premiere' => 'achats_matieres_premieres', 'consommation_abonne' => 'consommations_abonnes', 'bilan' => 'bilans_financiers'];
    $n = 0; $err = [];
    foreach (Route::getRoutes() as $r) {
        if (! in_array('GET', $r->methods()) || preg_match('/login|register|logout|sauvegarde|super-admin|storage|up$|telecharger|notifications|boulangeries|setup|mot-de-passe/', $r->uri()) || $r->uri() === '/') continue;
        $uri = $r->uri();
        if (str_contains($uri, '{')) { preg_match_all('/\{(\w+)\}/', $uri, $m); if (count($m[1]) > 1 || ! isset($map[$m[1][0]])) continue; $tb = $map[$m[1][0]]; $q = DB::table($tb); if (Schema::hasColumn($tb, 'boulangerie_id')) $q->where('boulangerie_id', $B); foreach ($q->orderBy('id')->limit(5)->pluck('id') as $id) { $resp = $envoyer('/' . str_replace('{' . $m[1][0] . '}', $id, $uri)); $n++; if ($resp->getStatusCode() >= 500) $err[] = "$uri #$id"; } continue; }
        $resp = $envoyer('/' . $uri); $n++; if ($resp->getStatusCode() >= 500) $err[] = $uri;
    }
    $t("$n pages ouvertes avec les données de la boulangerie #$B", empty($err), implode(' | ', array_slice($err, 0, 5)));

    echo "\n== 3. Les comptes sont cohérents ==\n";
    $pb = [];
    foreach (DB::table('livreurs')->where('boulangerie_id', $B)->pluck('id') as $id) { $att = (float) DB::table('distributions')->where('livreur_id', $id)->sum('montant_attendu'); $ver = (float) DB::table('versements')->where('livreur_id', $id)->sum('montant_verse'); $rel = (float) DB::table('distributions')->where('livreur_id', $id)->sum('reliquat'); if ($att - $ver - $rel > 0.6) $pb[] = "argent du livreur #$id : " . round($att - $ver - $rel, 2) . ' FCFA inexpliqués'; }
    $t('argent des livreurs, clients et vendeurs : attendu − versé − reliquats ≥ 0 partout', empty($pb), implode(' | ', array_slice($pb, 0, 4)));
    $pb = [];
    foreach (DB::table('matieres_premieres')->where('boulangerie_id', $B)->get() as $mp) {
        $ach = DB::table('achats_matieres_premieres')->where('matiere_premiere_id', $mp->id); $qA = (float) (clone $ach)->sum('quantite'); $vA = (float) (clone $ach)->sum('montant');
        $col = ['Farine' => ['quantite_farine', 'cout_farine'], 'Levure' => ['quantite_levure', 'cout_levure'], 'Produit' => ['quantite_produit', 'cout_produit']][$mp->nom] ?? null;
        $qP = $col ? (float) DB::table('productions')->where('boulangerie_id', $B)->sum($col[0]) : 0; $vP = $col ? (float) DB::table('productions')->where('boulangerie_id', $B)->sum($col[1]) : 0;
        $vR = (float) DB::table('retraits_stock')->where('matiere_premiere_id', $mp->id)->sum('cout'); $qI = (float) DB::table('inventaires_stock')->where('matiere_premiere_id', $mp->id)->sum('ecart'); $vI = (float) DB::table('inventaires_stock')->where('matiere_premiere_id', $mp->id)->sum('valeur_ecart');
        // Un stock peut avoir été corrigé à la main dans l'ancienne version : on signale sans bloquer (écart de quantité).
        if ($qA > 0 && $col && abs($qA - $qP + $qI - $mp->stock_actuel) > 0.011) $pb[] = "{$mp->nom} : stock enregistré " . $mp->stock_actuel . ' / calculé ' . round($qA - $qP + $qI, 2);
        if ($mp->stock_actuel < -0.001) $pb[] = "{$mp->nom} : stock NÉGATIF";
        if ($col && $qA > 0 && abs($vA - $vP - $vR + $vI - $mp->valeur_stock) > 2.0) $pb[] = "{$mp->nom} : valeur " . round($mp->valeur_stock) . ' / calculée ' . round($vA - $vP - $vR + $vI);
    }
    $t('stock et valeur du stock de chaque produit s\'expliquent par les achats et les productions', empty($pb), implode(' | ', $pb) . '  (un écart de quantité vient souvent d\'une correction de stock manuelle faite dans l\'ancienne version : à vérifier avec la boulangerie)');
    echo "\n== 4. Comptes de connexion ==\n";
    $doublons = DB::select("SELECT telephone, COUNT(*) n FROM users WHERE telephone IS NOT NULL GROUP BY telephone HAVING n > 1");
    $t('aucun numéro de téléphone en double', empty($doublons), json_encode($doublons));
    $mal = DB::table('users')->whereNotNull('telephone')->get(['id', 'name', 'telephone'])->filter(fn ($u) => $u->telephone !== App\Support\Telephone::normaliser($u->telephone));
    $t('tous les numéros sont à l\'écriture unique (sinon : conflit à régler à la main)', $mal->isEmpty(), $mal->map(fn ($u) => "#{$u->id} {$u->name} « {$u->telephone} »")->implode(' | '));
}
echo "\n$ok OK, $ko KO\n";
exit($ko ? 1 : 0);
