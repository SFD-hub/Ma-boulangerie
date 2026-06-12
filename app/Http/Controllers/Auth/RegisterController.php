<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:users,telephone',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Le nom complet est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $proprietaireRoleId = Role::where('nom', 'proprietaire')->value('id');

        $user = User::create([
            'name'           => $request->name,
            'telephone'      => $request->telephone,
            'password'       => bcrypt($request->password),
            'role_id'        => $proprietaireRoleId,
            'boulangerie_id' => null,
            'actif'          => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('setup.boulangerie');
    }
}
