<?php

namespace App\Http\Controllers\Backoffice;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Models\UserActionLog; // Import du modèle UserActionLog
use App\Notifications\ForgotPassword;
use Illuminate\Support\Facades\Auth;

class ResetPassword extends Controller
{
    use Notifiable;

    public function show()
    {
        return view('auth.reset-password');
    }

    public function showResetForm($token)
    {
        return view('auth.change-password', ['token' => $token]);
    }

    public function routeNotificationForMail() 
    {
        return request()->email;
    }

    public function send(Request $request)
{
    // Valider l'email
    $request->validate([
        'email' => ['required', 'email']
    ]);

    // Rechercher l'utilisateur
    $user = User::where('email', $request->email)->first();

    // Si l'utilisateur existe, envoyer l'email et loguer l'action
    if ($user) {
        // Envoyer l'email de réinitialisation de mot de passe
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        // Vérifier si l'email a été envoyé
        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            // Créer un log
            UserActionLog::create([
                'user_id' => $user->id,
                'action' => 'Email de réinitialisation de mot de passe envoyé',
                'details' => json_encode(['email' => $user->email]),
                'log_color' => 'blue', // Log en bleu pour l'envoi de l'email
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Un e-mail a été envoyé à votre adresse pour réinitialiser votre mot de passe.');
        } else {
            return back()->withErrors(['email' => 'Aucun utilisateur trouvé avec cet e-mail.']);
        }
    }

    return back()->withErrors(['email' => 'Aucun utilisateur trouvé avec cet e-mail.']);
}

    
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
            'token' => 'required'
        ]);

        // Reset the password using Laravel's built-in Password broker
        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Hash::make($password)
                ])->save();

                // Enregistrer un log du changement de mot de passe
                UserActionLog::create([
                    'user_id' => $user->id,
                    'action' => 'Changement de mot de passe',
                    'details' => json_encode(['Utilisateur' => $user->username]),
                    'log_color' => 'violet', // Log en violet pour le changement de mot de passe
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        );

        return $status === \Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé avec succès.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
