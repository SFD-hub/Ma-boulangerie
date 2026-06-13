<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AchatMatierePremiereController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BoulangerieSetupController;
use App\Http\Controllers\BilanController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\ClientAbonneController;
use App\Http\Controllers\ConsommationAbonneController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\GerantController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\MatierePremiereController;
use App\Http\Controllers\PaiementFactureController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\VersementController;
use App\Http\Controllers\VenteController;

// ── Page d'accueil → redirection vers la connexion ───────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Authentification ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',   [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Configuration initiale (propriétaire sans boulangerie) ───────────────────
Route::middleware('auth')->group(function () {
    Route::get('/setup/boulangerie',  [BoulangerieSetupController::class, 'create'])->name('setup.boulangerie');
    Route::post('/setup/boulangerie', [BoulangerieSetupController::class, 'store'])->name('setup.boulangerie.store');
});

// ── Propriétaire ET gérant ───────────────────────────────────────────────────
Route::middleware(['auth', 'setup', 'role:proprietaire,gerant'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stock & matières premières
    Route::resource('matieres-premieres', MatierePremiereController::class)
        ->parameters(['matieres-premieres' => 'matiere_premiere']);
    Route::resource('achats-matieres-premieres', AchatMatierePremiereController::class)
        ->parameters(['achats-matieres-premieres' => 'achat_matiere_premiere']);

    // Produits & catégories
    Route::resource('categories-produits', CategorieProduitController::class)
        ->parameters(['categories-produits' => 'categorie_produit']);
    Route::resource('produits', ProduitController::class);

    // Production
    Route::get('/productions/historique', [ProductionController::class, 'historique'])->name('productions.historique');
    Route::resource('productions', ProductionController::class);

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

    // Paiements factures
    Route::resource('paiements-factures', PaiementFactureController::class)
        ->parameters(['paiements-factures' => 'paiement_facture']);

    // Dépenses
    Route::get('/depenses/historique', [DepenseController::class, 'historique'])->name('depenses.historique');
    Route::resource('depenses', DepenseController::class);

    // Ventes directes
    Route::resource('ventes', VenteController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);
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
});
