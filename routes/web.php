<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AchatMatierePremiereController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BoulangerieSetupController;
use App\Http\Controllers\BoulangerieSwitchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BilanController;
use App\Http\Controllers\ClientAbonneController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ConsommationAbonneController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\GerantController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\MatierePremiereController;
use App\Http\Controllers\PaiementFactureController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\VersementController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AProposController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SuperAdminController;

// ── Page d'accueil → redirection vers la connexion ───────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Authentification ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',   [AuthenticatedSessionController::class, 'store'])->name('login.store')->middleware('throttle:6,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->name('register.store')->middleware('throttle:6,1');
    Route::get('/mot-de-passe-oublie', fn () => view('auth.mot-de-passe-oublie'))->name('password.info');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Configuration initiale (propriétaire sans boulangerie) ───────────────────
Route::middleware('auth')->group(function () {
    Route::get('/setup/boulangerie',  [BoulangerieSetupController::class, 'create'])->name('setup.boulangerie');
    Route::post('/setup/boulangerie', [BoulangerieSetupController::class, 'store'])->name('setup.boulangerie.store');
});

// ── Super Admin (développeur uniquement) ────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/',                                                    [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/boulangeries',                                        [SuperAdminController::class, 'boulangeries'])->name('boulangeries');
    Route::get('/boulangeries/{boulangerie}',                          [SuperAdminController::class, 'showBoulangerie'])->name('boulangeries.show');
    Route::patch('/boulangeries/{boulangerie}/suspendre',              [SuperAdminController::class, 'suspendre'])->name('boulangeries.suspendre');
    Route::patch('/boulangeries/{boulangerie}/reactiver',              [SuperAdminController::class, 'reactiver'])->name('boulangeries.reactiver');
    Route::post('/boulangeries/{boulangerie}/acceder',                 [SuperAdminController::class, 'accederBoulangerie'])->name('boulangeries.acceder');
    Route::get('/utilisateurs',                                        [SuperAdminController::class, 'utilisateurs'])->name('utilisateurs');
    Route::get('/utilisateurs/{user}/reinitialiser',                   [SuperAdminController::class, 'resetPasswordForm'])->name('utilisateurs.reset-password.form');
    Route::post('/utilisateurs/{user}/reinitialiser',                  [SuperAdminController::class, 'resetPassword'])->name('utilisateurs.reset-password')->middleware('throttle:10,1');
    Route::get('/sauvegardes',                                         [BackupController::class, 'index'])->name('backups');
    Route::post('/sauvegardes',                                        [BackupController::class, 'create'])->name('backups.create');
    Route::get('/sauvegardes/{filename}/telecharger',                  [BackupController::class, 'download'])->name('backups.download')->where('filename', '[^/]+');
    Route::delete('/sauvegardes/{filename}',                           [BackupController::class, 'destroy'])->name('backups.destroy')->where('filename', '[^/]+');
    Route::post('/sauvegardes/{filename}/verifier',                    [BackupController::class, 'verify'])->name('backups.verify')->where('filename', '[^/]+');
    Route::post('/sauvegardes/{filename}/basculer',                    [BackupController::class, 'promote'])->name('backups.promote')->where('filename', '[^/]+');
    Route::get('/journal',                                             [ActivityLogController::class, 'index'])->name('activity-logs');
});

// ── Quitter le mode support (accessible sans restriction de rôle) ─────────────
Route::middleware(['auth'])->group(function () {
    Route::post('/super-admin/quitter-support', [SuperAdminController::class, 'quitterSupport'])->name('super-admin.quitter');
});

// ── Propriétaire ET gérant ───────────────────────────────────────────────────
Route::middleware(['auth', 'setup', 'role:proprietaire,gerant'])->group(function () {

    Route::get('/dashboard',           [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/activites', [DashboardController::class, 'activites'])->name('dashboard.activites');

    // Stock & matières premières
    Route::resource('matieres-premieres', MatierePremiereController::class)
        ->parameters(['matieres-premieres' => 'matiere_premiere']);
    Route::resource('achats-matieres-premieres', AchatMatierePremiereController::class)
        ->parameters(['achats-matieres-premieres' => 'achat_matiere_premiere']);

    // Produits
    Route::patch('/produits/{produit}/desactiver', [ProduitController::class, 'desactiver'])->name('produits.desactiver');
    Route::patch('/produits/{produit}/reactiver', [ProduitController::class, 'reactiver'])->name('produits.reactiver');
    Route::resource('produits', ProduitController::class)->except(['show', 'destroy']);

    // Production
    Route::get('/productions/historique', [ProductionController::class, 'historique'])->name('productions.historique');
    Route::resource('productions', ProductionController::class);

    // Clients (module unifié : Livreurs, Clients revendeurs et Abonnés)
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
    Route::get('/clients/ajouter', [ClientsController::class, 'create'])->name('clients.create');
    Route::get('/clients/distribution-du-jour', [ClientsController::class, 'distribution'])->name('clients.distribution');

    // Caisse du jour (encaissé + dépenses regroupés par journée)
    Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.index');

    // Dépôt (vente boutique en direct, distincte de la consignation Livreur/Client)
    Route::get('/depot', [DepotController::class, 'index'])->name('depot.index');
    Route::post('/depot', [DepotController::class, 'store'])->name('depot.store');
    Route::delete('/depot/{depotVente}', [DepotController::class, 'destroy'])->name('depot.destroy');

    // Livreurs — pages dédiées (avant le resource pour éviter les conflits)
    Route::get('/livreurs/{livreur}/attribuer', [LivreurController::class, 'attribuerForm'])->name('livreurs.attribuer.form');
    Route::get('/livreurs/{livreur}/verser', [LivreurController::class, 'verserForm'])->name('livreurs.verser.form');
    Route::get('/livreurs/{livreur}/distributions', [LivreurController::class, 'distributionsHistorique'])->name('livreurs.distributions');
    // Livreurs — avec actions imbriquées attribution + versement
    Route::resource('livreurs', LivreurController::class);
    Route::patch('/livreurs/{livreur}/reactiver', [LivreurController::class, 'reactiver'])
        ->name('livreurs.reactiver');
    Route::post('/livreurs/{livreur}/attribuer', [LivreurController::class, 'attribuer'])
        ->name('livreurs.attribuer');
    Route::post('/livreurs/{livreur}/verser', [LivreurController::class, 'verser'])
        ->name('livreurs.verser');

    // Distributions (accès direct conservé pour edit/delete depuis l'historique)
    Route::resource('distributions', DistributionController::class)
        ->except(['index', 'create', 'store']);

    // Versements (accès direct conservé pour modification)
    Route::resource('versements', VersementController::class);

    // Abonnés — pages dédiées (avant le resource pour éviter les conflits)
    Route::get('/clients-abonnes/{client_abonne}/ajouter-consommation', [ClientAbonneController::class, 'consoForm'])->name('clients-abonnes.consommation.form');
    Route::get('/clients-abonnes/{client_abonne}/generer-facture', [ClientAbonneController::class, 'factureForm'])->name('clients-abonnes.facture.form');
    Route::get('/clients-abonnes/{client_abonne}/historique-consommations', [ClientAbonneController::class, 'consoHistorique'])->name('clients-abonnes.consommations.historique');
    Route::get('/clients-abonnes/{client_abonne}/historique-factures', [ClientAbonneController::class, 'facturesHistorique'])->name('clients-abonnes.factures.historique');
    // Abonnés — consommation et facture imbriquées dans le détail client
    Route::resource('clients-abonnes', ClientAbonneController::class)
        ->parameters(['clients-abonnes' => 'client_abonne']);
    Route::post('/clients-abonnes/{client_abonne}/consommations', [ClientAbonneController::class, 'storeConso'])
        ->name('clients-abonnes.consommations.store');
    Route::post('/clients-abonnes/{client_abonne}/factures', [ClientAbonneController::class, 'storeFacture'])
        ->name('clients-abonnes.factures.store');

    // Consommations (accès direct pour edit/delete)
    Route::resource('consommations-abonnes', ConsommationAbonneController::class)
        ->parameters(['consommations-abonnes' => 'consommation_abonne'])
        ->except(['index', 'create', 'store']);

    // Factures (accès direct pour show/edit/delete + actions métier)
    Route::resource('factures', FactureController::class)
        ->except(['index', 'create', 'store']);
    Route::patch('/factures/{facture}/payer',    [FactureController::class, 'payer'])->name('factures.payer');
    Route::get('/factures/{facture}/imprimer',   [FactureController::class, 'imprimer'])->name('factures.imprimer');

    // Paiements factures (toujours créés depuis la facture concernée)
    Route::post('/factures/{facture}/paiements', [PaiementFactureController::class, 'store'])
        ->name('factures.paiements.store');
    Route::delete('/paiements-factures/{paiementFacture}', [PaiementFactureController::class, 'destroy'])
        ->name('paiements-factures.destroy');

    // Dépenses
    Route::get('/depenses/historique', [DepenseController::class, 'historique'])->name('depenses.historique');
    Route::resource('depenses', DepenseController::class);

    // Mon compte (accessible propriétaire et gérant, contrairement à /parametres)
    Route::get('/mon-compte', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/mon-compte', [ProfilController::class, 'update'])->name('profil.update');

    // Notifications
    Route::get('/notifications/panel',  [NotificationController::class, 'panel'])->name('notifications.panel');
    Route::get('/notifications/count',  [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/{activityLog}/lire',    [NotificationController::class, 'lire'])->name('notifications.lire');
    Route::post('/notifications/{activityLog}/masquer', [NotificationController::class, 'masquer'])->name('notifications.masquer');
    Route::post('/notifications/tout-lire',   [NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
    Route::post('/notifications/vider-lues',  [NotificationController::class, 'viderLues'])->name('notifications.vider-lues');

});

// ── À propos (tous les rôles authentifiés) ────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/a-propos', [AProposController::class, 'index'])->name('a-propos.index');
});

// ── Propriétaire uniquement ──────────────────────────────────────────────────
Route::middleware(['auth', 'setup', 'role:proprietaire'])->group(function () {

    Route::resource('gerants', GerantController::class)
        ->parameters(['gerants' => 'gerant']);
    Route::patch('/gerants/{gerant}/desactiver', [GerantController::class, 'desactiver'])->name('gerants.desactiver');
    Route::patch('/gerants/{gerant}/activer',    [GerantController::class, 'activer'])->name('gerants.activer');

    Route::get('/bilan',                                 [BilanController::class, 'index'])->name('bilan.index');
    Route::get('/bilan/detail',                          [BilanController::class, 'detail'])->name('bilan.detail');
    Route::post('/bilan/enregistrer',                    [BilanController::class, 'enregistrer'])->name('bilan.enregistrer');
    Route::get('/bilan/historique',                      [BilanController::class, 'historique'])->name('bilan.historique');
    Route::get('/bilan/imprimer',                        [BilanController::class, 'imprimerDetail'])->name('bilan.imprimer.detail');
    Route::get('/bilan/saved/{bilan}',                   [BilanController::class, 'show'])->name('bilan.show');
    Route::get('/bilan/saved/{bilan}/imprimer',          [BilanController::class, 'imprimerSaved'])->name('bilan.imprimer.saved');
    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

    Route::get('/parametres',  [ParametreController::class, 'edit'])->name('parametres.edit');
    Route::put('/parametres',  [ParametreController::class, 'update'])->name('parametres.update');

    Route::get('/boulangeries/ajouter',  [BoulangerieSetupController::class, 'createAdditional'])->name('boulangeries.ajouter');
    Route::post('/boulangeries/ajouter', [BoulangerieSetupController::class, 'storeAdditional'])->name('boulangeries.ajouter.store');
    Route::post('/boulangeries/{boulangerie}/activer', [BoulangerieSwitchController::class, 'switch'])->name('boulangeries.switch');
});
