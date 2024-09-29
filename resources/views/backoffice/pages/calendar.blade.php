@extends('layouts.backoffice.app')

@section('content')
@include('layouts.backoffice.navbars.auth.topnav', ['title' => 'Gestion des rendez-vous'])

<meta name="csrf-token" content="{{ csrf_token() }}">
<div style="">
<div class="container mt-5">
    <h1 class="text-center">Gestion des Rendez-vous</h1>
<style>
    .min-height-300.bg-primary.position-absolute.w-100 {
    z-index: -1;
}
</style>

<!-- Modal pour afficher et modifier les détails d'un rendez-vous -->
<div class="modal fade" id="rdvDetailModal" tabindex="-1" aria-labelledby="rdvDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rdvDetailModalLabel">Détails / Modification du Rendez-vous</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
             <!-- Formulaire de modification du rendez-vous -->
            <form id="editAppointmentForm">
                @csrf
                <input type="hidden" id="editAppointmentId">
                <div class="mb-3">
                    <label for="editDate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="editDate" name="date" required>
                </div>
                <div class="mb-3">
                    <label for="editStartTime" class="form-label">Heure de début</label>
                    <input type="time" class="form-control" id="editStartTime" name="start_time" required>
                </div>
                <div class="mb-3">
                    <label for="editSlotDuration" class="form-label">Durée (minutes)</label>
                    <input type="number" class="form-control" id="editSlotDuration" name="slot_duration" value="30" required>
                </div>
                <div class="mb-3">
                    <label for="editEndTime" class="form-label">Heure de fin</label>
                    <input type="text" class="form-control" id="editEndTime" readonly>
                </div>
                <div class="mb-3">
                    <label for="editClientId" class="form-label">Client (optionnel)</label>
                    <select class="form-select" id="editClientId" name="client_id">
                        <option value="">Créneau disponible</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->firstname }} {{ $client->lastname }} ({{ $client->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editAppointmentType" class="form-label">Type de rendez-vous (facultatif)</label>
                    <select class="form-select" id="editAppointmentType" name="appointment_type">
                        <option value="">Déterminé automatiquement</option>
                        <option value="bilan">Bilan</option>
                        <option value="suivi">Suivi</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
            </form>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteRdvBtn">Annuler le rendez-vous</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire pour ajouter un rendez-vous -->
<form id="appointmentForm" class="mb-5">
    @csrf
    <div class="row">
        <div class="col-md-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="col-md-3">
            <label for="start_time" class="form-label">Heure de début</label>
            <input type="time" class="form-control" id="start_time" name="start_time" min="15:00" max="18:00" required>
        </div>
        <div class="col-md-3">
            <label for="slot_duration" class="form-label">Durée (minutes)</label>
            <input type="number" class="form-control" id="slot_duration" name="slot_duration" value="30" required>
        </div>
        <div class="col-md-3">
            <label for="client_id" class="form-label">Client (optionnel)</label>
            <select class="form-select" id="client_id" name="client_id">
                <option value="">Créneau disponible</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->firstname }} {{ $client->lastname }} ({{ $client->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mt-3">
            <label for="appointment_type" class="form-label">Type de rendez-vous (facultatif)</label>
            <select class="form-select" id="appointment_type" name="appointment_type">
                <option value="">Déterminé automatiquement</option>
                <option value="bilan">Bilan</option>
                <option value="suivi">Suivi</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Ajouter le rendez-vous</button>
</form>
</div>
<!-- Section Calendrier -->
<h2 class="text-center">Calendrier des rendez-vous</h2>
<div id="calendar"></div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Fonction pour mettre à jour l'heure de fin
        function updateEndTime() {
            var startTimeInput = document.getElementById('editStartTime').value;
            var duration = parseInt(document.getElementById('editSlotDuration').value);

            if (startTimeInput && !isNaN(duration)) {
                var startTime = new Date(`1970-01-01T${startTimeInput}:00`);
                startTime.setMinutes(startTime.getMinutes() + duration);

                var endHours = ('0' + startTime.getHours()).slice(-2);
                var endMinutes = ('0' + startTime.getMinutes()).slice(-2);
                document.getElementById('editEndTime').value = `${endHours}:${endMinutes}`;
            }
        }

        // Ajouter des écouteurs d'événements pour mettre à jour l'heure de fin
        document.getElementById('editStartTime').addEventListener('change', updateEndTime);
        document.getElementById('editSlotDuration').addEventListener('input', updateEndTime);

        // Initialiser le calendrier
        var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        selectable: true,
        events: '/backoffice/appointments/fetch',
        eventTimeFormat: { // Format de l'heure à afficher
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        dateClick: function(info) {
            document.getElementById('date').value = info.dateStr;
            $('#appointmentFormModal').modal('show');
        },

        eventClick: function(info) {
            var eventObj = info.event;

            // Convertir l'heure de début et de fin en heure locale
            var startTime = new Date(eventObj.start);
            var endTime = new Date(eventObj.end);

            // Remplir le formulaire de modification avec les valeurs converties
            document.getElementById('editAppointmentId').value = eventObj.id;
            document.getElementById('editDate').value = startTime.toISOString().slice(0, 10); // Afficher la date au format AAAA-MM-JJ
            
            var startHours = ('0' + startTime.getHours()).slice(-2); 
            var startMinutes = ('0' + startTime.getMinutes()).slice(-2);
            document.getElementById('editStartTime').value = `${startHours}:${startMinutes}`;

            document.getElementById('editSlotDuration').value = (endTime - startTime) / 60000; // Calcule la durée en minutes
            document.getElementById('editClientId').value = eventObj.extendedProps.client_id || '';
            document.getElementById('editAppointmentType').value = eventObj.extendedProps.appointment_type || '';

            updateEndTime();
            $('#rdvDetailModal').modal('show');
        }
    });

    calendar.render();




        // Gestion de l'ajout de rendez-vous
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            fetch('/backoffice/appointments/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    alert('Rendez-vous ajouté avec succès');
                } else {
                    alert('Erreur lors de l\'enregistrement du rendez-vous');
                }
            })
            .catch(error => console.error('Erreur lors de l\'enregistrement du rendez-vous', error));
        });

        document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var appointmentId = document.getElementById('editAppointmentId').value;
            var formData = new FormData(this); // Récupérer les données directement à partir du formulaire

            fetch(`/backoffice/appointments/${appointmentId}`, {
                method: 'POST', // Change method to 'POST' if necessary, see the next point
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT' // Laravel peut intercepter et traiter cela comme un PUT
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    alert('Rendez-vous modifié avec succès');
                    $('#rdvDetailModal').modal('hide');
                    document.getElementById('editAppointmentForm').reset(); // Réinitialiser le formulaire
                    document.getElementById('editAppointmentForm').removeAttribute('data-id'); // Supprimer l'attribut data-id
                } else {
                    alert('Erreur lors de la modification du rendez-vous');
                }
            })
            .catch(error => console.error('Erreur lors de la modification du rendez-vous', error));
        });


    });
</script>

@endsection
