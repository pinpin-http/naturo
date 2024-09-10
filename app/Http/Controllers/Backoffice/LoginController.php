<?php

namespace App\Http\Controllers\Backoffice;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActionLog;
use Laravel\Socialite\Facades\Socialite;

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


     // Rediriger vers Google
     public function redirectToGoogle()
     {
         return Socialite::driver('google')->redirect();
     }
 
     // Gérer le callback de Google
     public function handleGoogleCallback()
     {
         try {
             $googleUser = Socialite::driver('google')->stateless()->user();
             $user = User::where('email', $googleUser->getEmail())->first();
     
             if ($user) {
                 // Si l'utilisateur existe mais n'a pas encore de Google ID, on l'ajoute
                 if (is_null($user->google_id)) {
                     $user->google_id = $googleUser->getId();
                     $user->save();
                      // Journaliser la connexion via Google
                UserActionLog::create([
                    'user_id' => $user->google_id,
                    'action' => 'ajout de Google sur un compte classique',
                    'details' => json_encode(['email' => $googleUser->getEmail()],['id'=>$user->google_id]),
                    'log_color' => 'green', // Couleur verte pour une connexion
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                 }
     
                 Auth::login($user);
                  // Journaliser la connexion via Google
            UserActionLog::create([
                'user_id' => $user->id,
                'action' => 'Connexion via Google',
                'details' => json_encode(['email' => $googleUser->getEmail()]),
                'log_color' => 'green', // Couleur verte pour une connexion
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             } else {
                 // Si l'utilisateur n'existe pas, on le crée
                 $user = User::create([
                     'name' => $googleUser->getName(),
                     'email' => $googleUser->getEmail(),
                     'password' => Hash::make(uniqid()),  // Mot de passe aléatoire
                     'google_id' => $googleUser->getId(),
                 ]);
     
                 Auth::login($user);
                 UserActionLog::create([
                    'user_id' => $user->id,
                    'action' => 'Création de compte via Google',
                    'details' => json_encode(['email' => $googleUser->getEmail()]),
                    'log_color' => 'blue', // Couleur bleue pour une création de compte
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                }
     
             return redirect()->route('backoffice.dashboard');
         } catch (Exception $e) {
             return redirect()->route('login')->with('error', 'Échec de la connexion avec Google');
         }
     }
     
}
