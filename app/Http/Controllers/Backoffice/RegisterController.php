<?php
namespace App\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\UserActionLog;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Validation des champs, y compris l'unicité de l'email et du username
        $attributes = $request->validate([
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'username' => 'required|max:255|min:2|unique:users,username', // Validation du username unique
            'email' => 'required|email|max:255|unique:users,email', // Validation de l'email unique
            'password' => 'required|min:5|max:255|confirmed', // Validation du mot de passe avec confirmation
            'terms' => 'required'
        ]);

        // Créer un utilisateur
        $user = User::create([
            'firstname' => $attributes['firstname'],
            'lastname' => $attributes['lastname'],
            'username' => $attributes['username'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']) // Utiliser Hash::make() pour hacher le mot de passe
        ]);

        // Attribuer un rôle à l'utilisateur (ex: 'client')
        $user->assignRole('client');

        // Log l'action de création de l'utilisateur avec les détails
        UserActionLog::create([
            'user_id' => $user->id,
            'action' => 'Inscription',
            'details' => json_encode([
                'Nom' => $user->lastname,
                'Prénom' => $user->firstname,
                'Nom d\'utilisateur' => $user->username,
                'Email' => $user->email,
            ]),
            'color' => 'green', // Utilise la couleur bleue pour une nouvelle inscription
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Authentifier l'utilisateur
        auth()->login($user);

        // Redirection vers la page de profil avec un message flash
        return redirect()->route('page.profile')->with('info', 'Veuillez compléter votre profil.');
    }
}
