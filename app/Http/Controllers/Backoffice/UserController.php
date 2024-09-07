<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\UserActionLog;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::all();

        return view('backoffice.users.index', compact('users', 'roles'));
    }

    // Changer de rôle
    public function updateRole(Request $request, User $user)
    {
        $user->syncRoles($request->role);

        // Log de l'action
        UserActionLog::create([
            'user_id' => Auth::id(),
            'action' => 'Changement de rôle pour ' . $user->username . ' en ' . $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Rôle mis à jour avec succès.');
    }

    // Supprimer un utilisateur
    public function destroy(User $user, Request $request)
    {
        // Récupérer l'utilisateur admin avec le username "admin"
        $admin = User::where('username', 'admin')->first();

        // Vérification du mot de passe administrateur
        $adminPassword = $request->input('password');
        if (!Hash::check($adminPassword, $admin->password)) {
            return redirect()->back()->with('error', 'Mot de passe incorrect');
        }

        // Log de l'action de suppression
        UserActionLog::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression de l\'utilisateur ' . $user->username,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
    
        // Préparation pour capturer les modifications
        $modifications = [];
        
        if ($request->filled('username') && $user->username !== $request->input('username')) {
            $modifications['username'] = [$user->username, $request->input('username')];
            $user->username = $request->input('username');
        }
    
        if ($request->filled('email') && $user->email !== $request->input('email')) {
            $modifications['email'] = [$user->email, $request->input('email')];
            $user->email = $request->input('email');
        }
    
        if ($request->filled('firstname') && $user->firstname !== $request->input('firstname')) {
            $modifications['firstname'] = [$user->firstname, $request->input('firstname')];
            $user->firstname = $request->input('firstname');
        }
    
        if ($request->filled('lastname') && $user->lastname !== $request->input('lastname')) {
            $modifications['lastname'] = [$user->lastname, $request->input('lastname')];
            $user->lastname = $request->input('lastname');
        }
    
        if ($request->filled('address') && $user->address !== $request->input('address')) {
            $modifications['address'] = [$user->address, $request->input('address')];
            $user->address = $request->input('address');
        }
    
        if ($request->filled('city') && $user->city !== $request->input('city')) {
            $modifications['city'] = [$user->city, $request->input('city')];
            $user->city = $request->input('city');
        }
    
        if ($request->filled('postal_code') && $user->postal_code !== $request->input('postal_code')) {
            $modifications['postal_code'] = [$user->postal_code, $request->input('postal_code')];
            $user->postal_code = $request->input('postal_code');
        }
    
        if ($request->filled('date_of_birth') && $user->date_of_birth !== $request->input('date_of_birth')) {
            $modifications['date_of_birth'] = [$user->date_of_birth, $request->input('date_of_birth')];
            $user->date_of_birth = $request->input('date_of_birth');
        }
    
        // Si des modifications ont été faites, les enregistrer dans le log
        foreach ($modifications as $key => $values) {
            UserActionLog::create([
                'user_id' => $user->id,
                'action' => ucfirst($key) . ' modifié : ' . $values[0] . ' → ' . $values[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        // Vérification si le profil est complet
        if (
            $user->username &&
            $user->email &&
            $user->firstname &&
            $user->lastname &&
            $user->address &&
            $user->city &&
            $user->postal_code
        ) {
            $user->profile_complete = true;
        } else {
            $user->profile_complete = false;
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
