<?php

namespace App\Http\Controllers\Backoffice;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        return view('backoffice.users.index', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        $user->syncRoles($request->role);
        return redirect()->back()->with('success', 'Rôle mis à jour avec succès.');
    }
}
