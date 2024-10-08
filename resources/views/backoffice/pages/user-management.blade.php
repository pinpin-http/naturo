@extends('layouts.backoffice.app')

@section('content')
    @include('layouts.backoffice.navbars.auth.topnav', ['title' => 'User Management'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="alert alert-light" role="alert">
                This feature is available in <strong>Argon Dashboard 2 Pro Laravel</strong>. Check it
                <strong>
                    <a href="https://www.creative-tim.com/product/argon-dashboard-pro-laravel" target="_blank">
                        here
                    </a>
                </strong>
            </div>
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Users</h6>
                </div>
               <h1>Gestion des Utilisateurs</h1>

    @foreach($users as $user)
        <div class="user-card">
            <p>{{ $user->username }} ({{ $user->email }})</p>
            <p>Rôle actuel : {{ $user->roles->pluck('name')->implode(', ') }}</p>

            <form action="{{ route('users.updateRole', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <select name="role">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">Mettre à jour le rôle</button>
            </form>
        </div>
    @endforeach
            </div>
        </div>
    </div>
@endsection
