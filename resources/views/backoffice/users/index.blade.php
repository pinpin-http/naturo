
<!-- Cha
@extends('layouts.backoffice.app')

@section('content')
    <h1>Gestion des Utilisateurs</h1>
   <!-- Modal for Success or Error Message -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Notification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(session('success'))
                    <p>{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p>{{ session('error') }}</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>


<div style="position: relative; z-index: 100; background-color: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-top: 20px; margin-left: 20px;margin-right: 20px;">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Nom d'utilisateur</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle actuel</th>
                <th>Action</th>
                <th>Supprimer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->lastname }}</td>
                    <td>{{ $user->firstname }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                    <td>
                        <form action="{{ route('users.updateRole', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <select name="role" class="form-select">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary mt-2">Mettre à jour le rôle</button>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirmDelete(event, this)">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i>X
                            </button>
                            <!-- Champ caché pour le mot de passe -->
                            <input type="hidden" name="password" class="passwordField">
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->links() }}
    </div>

   <script>
    function confirmDelete(event, form) {
        event.preventDefault();

        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            let password = prompt("Veuillez entrer votre mot de passe d'administrateur pour confirmer la suppression:");

            if (password) {
                // Remplit le champ caché avec le mot de passe dans le formulaire spécifique
                form.querySelector('.passwordField').value = password;

                // Envoie le formulaire
                form.submit();
            }
        }
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        @if(session('success') || session('error'))
            $('#statusModal').modal('show');
        @endif
    });
</script>
@endsection
