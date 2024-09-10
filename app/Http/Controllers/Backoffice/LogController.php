<?php

namespace App\Http\Controllers\Backoffice;
use App\Models\UserActionLog;
use Illuminate\Http\Request;
use App\Models\User; 

class LogController extends Controller
{
    public function index()
    {
        $logs = UserActionLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('backoffice.logs.index', compact('logs'));
    }
    public function search(Request $request)
    {
        $email = $request->input('email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Récupérer les logs de l'utilisateur trouvé
                $logs = UserActionLog::where('user_id', $user->id)->paginate(10);
            } else {
                // Si aucun utilisateur n'est trouvé, on renvoie un tableau vide
                $logs = collect();
                session()->flash('error', "Aucun utilisateur trouvé avec cet email.");
            }
        } else {
            // Si aucun email n'est fourni, retourner tous les logs
            $logs = UserActionLog::with('user')->paginate(10);
        }

        return view('backoffice.logs.index', compact('logs'));
    }
}
