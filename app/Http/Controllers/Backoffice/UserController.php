<?php

namespace App\Http\Controllers\Backoffice;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::all();

        return view('backoffice.users.index', compact('users', 'roles'));
    }
    //changer role

    public function updateRole(Request $request, User $user)
    {
        $user->syncRoles($request->role);
        return redirect()->back()->with('success', 'Rôle mis à jour avec succès.');
    }


    //suprimmer user
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
 

    public function update(Request $request)
    {
        $user = Auth::user();
    
        // Validation des champs
        $request->validate([
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        // Gestion de l'upload de l'image de profil
        if ($request->hasFile('profile_picture')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
    
            // Enregistrer la nouvelle image
            $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $imagePath;
        }
     // Vérification si l'email existe déjà dans la base de données, sauf pour l'utilisateur actuel
     if (User::where('email', $request->input('email'))->where('id', '!=', $user->id)->exists()) {
        return redirect()->back()->with('error', 'Cette adresse e-mail est déjà utilisée.');
    }
        // Mise à jour uniquement si un champ est modifié
        $modifications = [];
    
        if ($request->filled('username') && $user->username !== $request->input('username')) {
            $user->username = $request->input('username');
            $modifications[] = 'username';
        }
    
        if ($request->filled('email') && $user->email !== $request->input('email')) {
            $user->email = $request->input('email');
            $modifications[] = 'email';
        }
    
        if ($request->filled('firstname') && $user->firstname !== $request->input('firstname')) {
            $user->firstname = $request->input('firstname');
            $modifications[] = 'firstname';
        }
    
        if ($request->filled('lastname') && $user->lastname !== $request->input('lastname')) {
            $user->lastname = $request->input('lastname');
            $modifications[] = 'lastname';
        }
    
        if ($request->filled('address') && $user->address !== $request->input('address')) {
            $user->address = $request->input('address');
            $modifications[] = 'address';
        }
    
        if ($request->filled('city') && $user->city !== $request->input('city')) {
            $user->city = $request->input('city');
            $modifications[] = 'city';
        }
    
        if ($request->filled('postal_code') && $user->postal_code !== $request->input('postal_code')) {
            $user->postal_code = $request->input('postal_code');
            $modifications[] = 'postal_code';
        }
    
        if ($request->filled('date_of_birth') && $user->date_of_birth !== $request->input('date_of_birth')) {
            $user->date_of_birth = $request->input('date_of_birth');
            $modifications[] = 'date_of_birth';
        }
    
        // Si aucune donnée n'a été modifiée
        if (empty($modifications) && !$request->hasFile('profile_picture')) {
            return redirect()->back()->with('info', 'Aucune donnée n\'a été modifiée.');
        }
    
        // Enregistrer les modifications
        $user->save();
    
        return redirect()->back()->with('success', 'Profil mis à jour avec succès.');
    }
    
}
