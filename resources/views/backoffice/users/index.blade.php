@extends('backoffice.layout')

@section('content')
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
@endsection
