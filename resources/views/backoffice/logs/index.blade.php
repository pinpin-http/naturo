@extends('layouts.backoffice.app')

@section('content')
    <h1>Journal des Actions Utilisateurs</h1>

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


    <div style="color:white; position: relative; z-index: 100; background-color: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-top: 20px; margin-left: 20px;margin-right: 20px;">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Action</th>
                    <th>Détails</th>
                    <th>Date de l'action</th>
                    <th>Heure</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <!-- Appliquer la couleur en fonction du type de log -->
                    <tr style="background-color: {{ $log->log_color ?? '#fff' }};color: black;">
                        <td>{{ $log->user->username ?? 'Utilisateur supprimé' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>
                            @php
                                $details = json_decode($log->details, true);
                            @endphp
                            @if($details)
                                <ul>
                                    @foreach($details as $key => $value)
                                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                Aucun détail
                            @endif
                        </td>
                        <td>{{ $log->created_at->timezone('Europe/Paris')->format('d/m/Y') }}</td> <!-- Assure-toi que la timezone est correcte -->
                        <td>{{ $log->created_at->timezone('Europe/Paris')->format('H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $logs->links() }}
    </div>

    <!-- Formulaire de recherche par email -->
    <div class="container" style="z-index:1000;">
        <form action="{{ route('logs.search') }}" method="GET" class="form-inline mb-4" style="z-index:1000;">
            <div class="form-group mr-3">
                <label for="email" class="mr-2">Rechercher par e-mail :</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Entrez l'e-mail de l'utilisateur" value="{{ request('email') }}">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            @if(session('success') || session('error'))
                $('#statusModal').modal('show');
            @endif
        });
    </script>
     <script>
        // Rafraîchir la page toutes les 10 secondes (10 000 ms)
        setInterval(function(){
            location.reload();
        }, 10000); // 10000 ms = 10 seconds
    </script>

@endsection
