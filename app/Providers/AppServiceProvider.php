<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Messages par défaut en français pour les dates (les formulaires n'ont pas tous un message propre).
        app('translator')->addLines([
            'validation.after_or_equal'  => 'La date est trop ancienne.',
            'validation.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'validation.date'            => 'La date n\'est pas valide.',
            'validation.regex'           => 'Le format de cette valeur n\'est pas valide (le numéro de téléphone ne contient que des chiffres).',
        ], 'en');
    }
}
