@extends('layouts.backoffice.app')

@section('content')
<div class="container" > <!-- 30% du haut de la page -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div style="margin-top: 20vh;" class="card shadow-lg">
                <div class="card-header text-center">
                    <h4 class="font-weight-bold">Réinitialiser le mot de passe</h4>
                </div>

                <div class="card-body">
                    <!-- Message de succès si l'email est envoyé -->
                    @if(session('success'))
                        <div class="alert alert-success" style="color:white;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulaire de réinitialisation de mot de passe -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="email">Adresse e-mail</label>
                            <input id="email" type="email" class="form-control form-control-lg" name="email" required autocomplete="email" autofocus>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                Envoyer le lien de réinitialisation
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bouton de retour selon l'état de connexion -->
                <div class="card-footer text-center">
                    @if(Auth::check())
                        <!-- Si l'utilisateur est connecté -->
                        <a href="{{ route('backoffice.dashboard') }}" class="btn btn-secondary btn-lg mt-3">
                            Retour au Dashboard
                        </a>
                    @else
                        <!-- Si l'utilisateur n'est pas connecté -->
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg mt-3">
                            Retour à la page login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
