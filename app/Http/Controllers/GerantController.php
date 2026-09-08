<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GerantController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $gerantRoleId   = Role::where('nom', 'gerant')->value('id');

        $gerants = User::where('boulangerie_id', $boulangerie_id)
            ->where('role_id', $gerantRoleId)
            ->orderBy('name')
            ->get();

        return view('gerants.index', compact('gerants'));
    }

    public function create(): View
    {
        return view('gerants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:users,telephone',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;
        $gerantRoleId   = Role::where('nom', 'gerant')->value('id');

        User::create([
            'name'           => $validated['name'],
            'telephone'      => $validated['telephone'],
            'password'       => bcrypt($validated['password']),
            'role_id'        => $gerantRoleId,
            'boulangerie_id' => $boulangerie_id,
        ]);

        ActivityLog::record(
            'gerant_ajoute',
            auth()->user()->name . ' a ajouté un gérant — ' . $validated['name'],
            $boulangerie_id,
            'gerant'
        );

        return redirect()->route('gerants.index')
            ->with('success', 'Gérant créé avec succès.');
    }

    public function show(User $gerant): View
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);

        return view('gerants.show', compact('gerant'));
    }

    public function edit(User $gerant): View
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);

        return view('gerants.edit', compact('gerant'));
    }

    public function update(Request $request, User $gerant): RedirectResponse
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:users,telephone,' . $gerant->id,
            'password'  => 'nullable|string|min:8|confirmed',
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $gerant->update([
            'name'      => $validated['name'],
            'telephone' => $validated['telephone'],
            'password'  => $validated['password'] ? bcrypt($validated['password']) : $gerant->password,
        ]);

        return redirect()->route('gerants.show', $gerant)
            ->with('success', 'Gérant mis à jour.');
    }

    public function destroy(User $gerant): RedirectResponse
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);

        $gerant->delete();

        return redirect()->route('gerants.index')
            ->with('success', 'Gérant supprimé.');
    }

    public function desactiver(User $gerant): RedirectResponse
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);
        $gerant->update(['actif' => false]);
        return redirect()->route('gerants.index')->with('success', 'Gérant désactivé.');
    }

    public function activer(User $gerant): RedirectResponse
    {
        abort_if($gerant->boulangerie_id !== auth()->user()->boulangerie_id || $gerant->role?->nom !== 'gerant', 403);
        $gerant->update(['actif' => true]);
        return redirect()->route('gerants.index')->with('success', 'Gérant activé.');
    }
}
