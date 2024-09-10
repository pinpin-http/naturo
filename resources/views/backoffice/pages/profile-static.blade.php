@extends('layouts.backoffice.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.backoffice.navbars.auth.topnav', ['title' => 'Profile'])
    
    

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-auto">
                    <div class="avatar avatar-xl position-relative">
                        <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : '../images/backoffice/placeholder-profile.jpg' }}" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                    </div>
                </div>
                <div class="col-auto my-auto">
                    <div class="h-100">
                        <h5 class="mb-1">
                            {{ Auth::user()->username }}
                        </h5>
                        <p class="mb-0 font-weight-bold text-sm">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Affichage des messages de succès, erreur, et info -->
    @if (session('success'))
        <div class="alert alert-success text-center col-md-8 offset-md-2" id="success-alert"style="color: white; font-weight: bold; width: 60%; margin-top:2%;"">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
            <div id="errorMessage" class="alert alert-danger col-md-8 offset-md-2"  style="color: white; font-weight: bold; width: 60%; margin-top:2%;">
                        {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info text-center col-md-8 offset-md-2" id="info-alert" style="color: white; font-weight: bold; width: 60%; margin-top:2%;">
            {{ session('info') }}
        </div>
    @endif

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Editer mon profil</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <p class="text-uppercase text-sm">Mes Informations</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username" class="form-control-label">Nom d'utilisateur</label>
                                        <input class="form-control" type="text" name="username" placeholder="{{ Auth::user()->username }}">
                                         @error('username')
                                            <span class="text-danger">{{ $message }}</span> <!-- Affiche le message d'erreur pour le username -->
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Adresse e-mail</label>
                                        @if (is_null(Auth::user()->google_id)) 
                                            <!-- Si l'utilisateur est classique (pas de google_id) -->
                                            <input class="form-control" type="email" name="email" placeholder="{{ Auth::user()->email }}">
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        @else
                                            <!-- Si l'utilisateur a un compte Google, l'e-mail est en lecture seule -->
                                            <input class="form-control" type="email" name="email" value="{{ Auth::user()->email }}" readonly>
                                            email non modifiable car connexion avec service tiers
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="firstname" class="form-control-label">Prénom</label>
                                        <input class="form-control" type="text" name="firstname" placeholder="{{ Auth::user()->firstname }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lastname" class="form-control-label">Nom</label>
                                        <input class="form-control" type="text" name="lastname" placeholder="{{ Auth::user()->lastname }}">
                                    </div>
                                </div>
                            </div>
                               <!-- Bouton pour réinitialiser le mot de passe -->
                        <div class="mt-4">
                            <a href="{{ route('reset-password') }}" class="btn btn-primary">
                                Réinitialiser le mot de passe
                            </a>
                        </div>
                            <div class="form-group">
                                <label for="date_of_birth">Date de naissance</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth', Auth::user()->date_of_birth) }}">
                            </div>

                            <hr class="horizontal dark">
                            <p class="text-uppercase text-sm">Informations de contact</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address" class="form-control-label">Adresse</label>
                                        <input class="form-control" type="text" name="address" placeholder="{{ Auth::user()->address }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city" class="form-control-label">Ville</label>
                                        <input class="form-control" type="text" name="city" placeholder="{{ Auth::user()->city }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="postal_code" class="form-control-label">Code postal</label>
                                        <input class="form-control" type="text" name="postal_code" placeholder="{{ Auth::user()->postal_code }}">
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark">
                            <p class="text-uppercase text-sm">Image de profil</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="profile_picture" class="form-control-label">Changer l'image de profil</label>
                                        <input class="form-control" type="file" name="profile_picture">
                                          @error('profile_picture')
            <p class="text-danger">{{ $message }}</p> <!-- Affiche le message d'erreur -->
        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-profile">
                    <img src="/images/backoffice/bg-profile.jpg" alt="Image placeholder" class="card-img-top">
                    <div class="row justify-content-center">
                        <div class="col-4 col-lg-4 order-lg-2">
                            <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                                <a href="javascript:;">
                                    <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : '../images/backoffice/placeholder-profile.jpg' }}" class="rounded-circle img-fluid border border-2 border-white">
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="text-center mt-4">
                            <h5>Bonjour {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}<span class="font-weight-light"> {{ Auth::user()->age }}</span></h5>
<p>
    Vous êtes <strong>{{ Auth::user()->firstname ?? '(à compléter)' }} {{ Auth::user()->lastname ?? '(à compléter)' }}</strong>, né(e) le <strong>{{ Auth::user()->date_of_birth ? date('d/m/Y', strtotime(Auth::user()->date_of_birth)) : '(à compléter)' }}</strong> (âge: <strong>{{ Auth::user()->date_of_birth ? \Carbon\Carbon::parse(Auth::user()->date_of_birth)->age . ' ans' : '(à compléter)' }}</strong>). 
    Vous résidez au <strong>{{ Auth::user()->address ?? '(à compléter)' }}, {{ Auth::user()->city ?? '(à compléter)' }}, {{ Auth::user()->postal_code ?? '(à compléter)' }}</strong> et votre adresse e-mail est <strong>{{ Auth::user()->email ?? '(à compléter)' }}</strong>.
</p>

                            <div class="h6 font-weight-300" style="color:#94a16c;">
                               Si une des informations ci-dessus est erronée, merci de la modifier dans le formulaire.
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.backoffice.footers.auth.footer')
    </div>

    <!-- Script pour les messages de confirmation -->
    <script>
        setTimeout(function() {
            let successAlert = document.getElementById('success-alert');
            if (successAlert) {
                successAlert.style.display = 'none';
            }

            let errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                errorAlert.style.display = 'none';
            }

            let infoAlert = document.getElementById('info-alert');
            if (infoAlert) {
                infoAlert.style.display = 'none';
            }
        }, 5000); // Le message disparaîtra après 5 secondes
    </script>
@endsection
