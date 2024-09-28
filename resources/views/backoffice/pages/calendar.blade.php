@extends('layouts.backoffice.app')

@section('content')
    @include('layouts.backoffice.navbars.auth.topnav', ['title' => 'Gestion des rendez-vous'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container mt-5">
        <h1 class="text-center">Calendrier des rendez-vous</h1>
        <div id="calendar"></div>

        <!-- Modal pour l'ajout de rendez-vous -->
        <div class="modal fade" id="addRdvModal" tabindex="-1" aria-labelledby="addRdvModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un Rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Liste des rendez-vous existants -->
                        <div class="mb-3">
                            <h6>Rendez-vous existants pour cette journée :</h6>
                            <ul id="existingAppointmentsList" class="list-group"></ul>
                        </div>

                        <form id="addRdvForm">
                            @csrf
                            <input type="hidden" name="date" id="modalDate">
                            
                            <!-- Sélection de l'heure -->
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Sélectionner l'heure de début :</label>
                                <input type="time" name="start_time" id="modalStartTimeInput" class="form-control" min="15:00" max="18:00" required>
                            </div>

                            <!-- Sélection du client -->
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Sélectionner un client (facultatif) :</label>
                                <select name="client_id" id="client_id" class="form-select">
                                    <option value="">Créneau disponible pour n'importe quel client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->firstname }} {{ $client->lastname }} ({{ $client->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" id="submitBtn">Confirmer le rendez-vous</button>
                            <span id="fullBookedMsg" class="text-danger" style="display:none;">Tous les créneaux sont réservés pour cette journée.</span>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour afficher les détails d'un rendez-vous existant -->
        <div class="modal fade" id="editRdvModal" tabindex="-1" aria-labelledby="editRdvModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails du Rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Date:</strong> <span id="editRdvDate"></span></p>
                        <p><strong>Heure:</strong> <span id="editRdvTime"></span></p>
                        <p><strong>Client:</strong> <span id="editRdvClient"></span></p>
                        <button id="deleteRdvBtn" class="btn btn-danger">Annuler le rendez-vous</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des rendez-vous -->
        <h2 class="mt-5">Liste des Rendez-vous Prévus</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Client</th>
                    <th>Durée</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="appointmentTableBody"></tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var submitBtn = document.getElementById('submitBtn');
            var fullBookedMsg = document.getElementById('fullBookedMsg');

            // Initialiser le calendrier
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                selectable: true,
                editable: true,
                events: '/backoffice/appointments/fetch',

                dateClick: function(info) {
                    document.getElementById('modalDate').value = info.dateStr;
                    fetchAvailableSlots(info.dateStr);
                    $('#addRdvModal').modal('show');
                },

                eventClick: function(info) {
                    var eventObj = info.event;
                    document.getElementById('editRdvDate').textContent = eventObj.start.toISOString().slice(0, 10);
                    document.getElementById('editRdvTime').textContent = `${eventObj.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${eventObj.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                    document.getElementById('editRdvClient').textContent = eventObj.title;
                    $('#editRdvModal').modal('show');

                    document.getElementById('deleteRdvBtn').onclick = function() {
                        deleteAppointment(eventObj.id);
                    };
                }
            });
            calendar.render();

            // Fonction pour récupérer les créneaux disponibles
            function fetchAvailableSlots(date) {
                fetch(`/backoffice/appointments/get-daily-slots?date=${date}`)
                    .then(response => {
                        // Log the response text pour voir le contenu
                        return response.text().then(text => {
                            console.log("API Response:", text);
                            try {
                                return JSON.parse(text); // Essayer de parser le JSON
                            } catch (error) {
                                throw new Error(`Erreur de parsing JSON: ${error.message}`);
                            }
                        });
                    })
                    .then(data => {
                        const existingList = document.getElementById('existingAppointmentsList');
                        existingList.innerHTML = '';
                        if (data.length === 0) {
                            existingList.innerHTML = '<li class="list-group-item">Aucun rendez-vous prévu pour cette journée</li>';
                        } else {
                            data.forEach(appt => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item';
                                li.textContent = `${appt.start_time} - ${appt.end_time} ${appt.is_booked ? '(Réservé)' : '(Disponible)'}`;
                                existingList.appendChild(li);
                            });
                        }
                    })
                    .catch(error => console.error('Erreur lors de la récupération des rendez-vous existants:', error));
            }


            // Gestion de l'ajout de rendez-vous
            document.getElementById('addRdvForm').addEventListener('submit', function(e) {
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
                        $('#addRdvModal').modal('hide');
                    } else {
                        alert('Erreur lors de l\'enregistrement du rendez-vous');
                    }
                })
                .catch(error => console.error('Erreur lors de l\'enregistrement du rendez-vous', error));
            });

            // Fonction pour supprimer un rendez-vous
            function deleteAppointment(appointmentId) {
                if (confirm("Voulez-vous vraiment annuler ce rendez-vous ?")) {
                    fetch(`/backoffice/appointments/${appointmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            calendar.refetchEvents();
                            $('#editRdvModal').modal('hide');
                        } else {
                            alert('Erreur lors de l\'annulation du rendez-vous');
                        }
                    })
                    .catch(error => console.error('Erreur lors de l\'annulation du rendez-vous', error));
                }
            }
        });
    </script>
@endsection
