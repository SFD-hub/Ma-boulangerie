<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(): View
    {
        return view('profil.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'telephone'        => 'required|string|max:20|unique:users,telephone,' . $user->id,
            'password'         => 'nullable|string|min:8|confirmed',
            'current_password' => ['required', 'current_password'],
        ], [
            'name.required'             => 'Le nom est obligatoire.',
            'telephone.required'        => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'          => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'        => 'La confirmation du mot de passe ne correspond pas.',
            'current_password.required' => 'Votre mot de passe actuel est obligatoire pour confirmer ce changement.',
            'current_password.current_password' => 'Mot de passe actuel incorrect.',
        ]);

        $ancienTelephone = $user->telephone;

        $user->update([
            'name'      => $validated['name'],
            'telephone' => $validated['telephone'],
            'password'  => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        // Regénère la session par précaution après un changement d'identifiants.
        $request->session()->regenerate();

        $detail = $validated['telephone'] !== $ancienTelephone
            ? ' (nouveau téléphone de connexion : ' . $validated['telephone'] . ')'
            : '';

        ActivityLog::record(
            'profil_modifie',
            $user->name . ' a mis à jour son profil' . $detail,
            $user->boulangerie_id,
            'compte'
        );

        // back() plutôt qu'une route fixe : ce formulaire est utilisé depuis
        // /mon-compte (gérant) ET depuis /parametres (propriétaire, section
        // "Mon compte" de la page).
        return redirect()->back()
            ->with('success', 'Vos informations ont été mises à jour.');
    }
}
