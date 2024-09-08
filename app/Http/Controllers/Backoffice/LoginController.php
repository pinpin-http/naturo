<?php

namespace App\Http\Controllers\Backoffice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActionLog;

class LoginController extends Controller
{
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Valider les informations de connexion
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tenter de connecter l'utilisateur avec les informations fournies
        if (Auth::attempt($credentials)) {
            // Régénérer la session pour éviter les attaques de fixation de session
            $request->session()->regenerate();

            // Récupérer l'utilisateur connecté
            $user = Auth::user();

           // Enregistrer un log de connexion avec couleur jaune
            UserActionLog::create([
                'user_id' => $user->id,
                'action' => 'Connexion',
                'details' => json_encode([
                    'Utilisateur' => $user->username,
                    'Action' => 'Connexion',
                ]), // Stocker les détails sous forme de tableau associatif
                'log_color' => 'yellow', // Couleur jaune pour la connexion
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Vérifier si l'utilisateur a complété son profil
            if (!$user->profile_complete) {
                return redirect()->route('page.profile')->with('info', 'Veuillez compléter votre profil.');
            }

            // Si le profil est déjà complété, rediriger vers le tableau de bord
            return redirect()->intended('backoffice/dashboard');
        }

        // Si les informations d'authentification sont incorrectes, renvoyer une erreur
        return back()->withErrors([
            'email' => 'Les informations d\'identification ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

       // Enregistrer un log de déconnexion avec couleur jaune
        UserActionLog::create([
            'user_id' => $user->id,
            'action' => 'Déconnexion',
            'details' => json_encode([
                'Utilisateur' => $user->username,
                'Action' => 'Déconnexion',
            ]), // Stocker les détails sous forme de tableau associatif
            'log_color' => 'yellow', // Couleur jaune pour la déconnexion
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Déconnexion de l'utilisateur
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
