<?php

namespace App\Http\Controllers\Backoffice;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::all();

        return view('backoffice.users.index', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        $user->syncRoles($request->role);
        return redirect()->back()->with('success', 'Rôle mis à jour avec succès.');
    }

    public function destroy(User $user, Request $request)
    {
        // Récupérer l'utilisateur admin avec le username "admin"
        $admin = User::where('username', 'admin')->first();
    
        // Vérification du mot de passe administrateur
        $adminPassword = $request->input('password');
        if (!Hash::check($adminPassword, $admin->password)) {
            return redirect()->back()->with('error', 'Mot de passe incorrect');
        }
    
        // Supprimer l'utilisateur
        $user->delete();
    
        return redirect()->route('backoffice.dashboard');
    }
 
  
    
    
}
