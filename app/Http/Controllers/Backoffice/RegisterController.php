<?php
namespace App\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Ajout pour l'authentification
use Illuminate\Auth\Events\Registered; // Ajout pour l'événement d'inscription
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

        // Créer un utilisateur sans authentification immédiate
        $user = User::create([
            'firstname' => $attributes['firstname'],
            'lastname' => $attributes['lastname'],
            'username' => $attributes['username'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']) // Utiliser Hash::make() pour hacher le mot de passe
        ]);
        $user->sendEmailVerificationNotification();
        // Attribuer un rôle à l'utilisateur (ex: 'client')
        $user->assignRole('client');

        // Envoyer l'email de vérification
        event(new Registered($user)); // Déclenche l'événement d'inscription
        auth()->login($user);
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
            'color' => 'green', // Utilise la couleur verte pour une nouvelle inscription
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Redirection vers la page de l'inscription avec un message flash
        return redirect()->route('verification.notice')->with('message', 'Veuillez vérifier votre email pour activer votre compte.');
    }
}