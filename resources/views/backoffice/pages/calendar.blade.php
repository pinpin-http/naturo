@extends('layouts.backoffice.app')

@section('content')
    @include('layouts.backoffice.navbars.auth.topnav', ['title' => 'Gestion des rendez-vous'])
<meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    <div class="container">
        <h1>Calendrier des rendez-vous</h1>
        <div id="calendar"></div>
        <!-- Modal pour l'ajout de rendez-vous -->
        <div class="modal fade" id="addRdvModal" tabindex="-1" aria-labelledby="addRdvModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRdvModalLabel">Ajouter un Rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Section avec les créneaux horaires de la journée -->
                        <div class="planning-day" id="planning-day">
                            <!-- Créneaux horaires seront injectés ici -->
                        </div>

                        <!-- Formulaire pour confirmer le rendez-vous -->
                        <form id="addRdvForm" action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="date" id="modalDate">
                            <input type="hidden" name="start_time" id="modalStartTime">

                            <div class="mb-3">
                                <label for="slot_duration" class="form-label">Durée du créneau :</label>
                                <select name="slot_duration" id="slot_duration" class="form-select" required>
                                    <option value="60">1 heure</option>
                                    <option value="90">1 heure 30 minutes</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Confirmer le rendez-vous</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                selectable: true,
                events: '/backoffice/appointments/fetch', // Route pour récupérer les événements
                dateClick: function(info) {
                    document.getElementById('modalDate').value = info.dateStr; // Autofill la date cliquée
                    fetchAvailableSlots(info.dateStr);
                    $('#addRdvModal').modal('show'); // Ouvre le modal
                },
                eventClick: function(info) {
                    // Remplir les champs du modal avec les données du rendez-vous
                    var eventObj = info.event;
                    document.getElementById('modalDate').value = eventObj.startStr.split('T')[0];
                    document.getElementById('modalStartTime').value = eventObj.startStr.split('T')[1].slice(0, 5);
                    document.getElementById('slot_duration').value = eventObj.extendedProps.duration;
                    $('#addRdvModal').modal('show');
                }
            });
            calendar.render();

            const slotDurationSelect = document.getElementById('slot_duration');
            const planningDay = document.getElementById('planning-day');
            const startTimeInput = document.getElementById('modalStartTime');
            const dateInput = document.getElementById('modalDate');

            // Quand l'utilisateur change la durée du créneau, on recharge les créneaux disponibles
            slotDurationSelect.addEventListener('change', function() {
                const duration = parseInt(this.value); // Durée du créneau sélectionnée
                const date = dateInput.value; // Date sélectionnée

                if (date) {
                    fetchAvailableSlots(date, duration);
                }
            });

            // Fonction pour charger les créneaux disponibles
            function fetchAvailableSlots(date, duration = 60) {
                fetch(`/backoffice/appointments/get-available-slots?date=${date}&slot_duration=${duration}`)
                    .then(response => response.json())
                    .then(slots => {
                        planningDay.innerHTML = ''; // Réinitialiser l'affichage

                        slots.forEach(slot => {
                            const slotDiv = document.createElement('div');
                            slotDiv.classList.add('slot');
                            slotDiv.textContent = `${slot.start_time} - ${slot.end_time}`;

                            if (slot.is_booked) {
                                slotDiv.classList.add('booked');
                                slotDiv.textContent += ' (Réservé)';
                            } else {
                                slotDiv.classList.add('available');
                                slotDiv.addEventListener('click', function() {
                                    startTimeInput.value = slot.start_time; // Stocker l'heure de début dans le formulaire
                                    document.querySelectorAll('.slot').forEach(s => s.classList.remove('selected'));
                                    slotDiv.classList.add('selected');
                                });
                            }

                            planningDay.appendChild(slotDiv);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des créneaux disponibles', error);
                    });
            }

            // Gérer la soumission du formulaire
            document.getElementById('addRdvForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                fetch('/backoffice/appointments/store', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.refetchEvents();
                        $('#addRdvModal').modal('hide');
                    } else {
                        alert('Erreur lors de l\'enregistrement');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
            });
        });
    </script>

    <style>
        .planning-day {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .slot {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        .slot.available:hover {
            background-color: #d1ffd1;
        }

        .slot.booked {
            background-color: #ffd1d1;
            cursor: not-allowed;
        }

        .slot.selected {
            background-color: #1e90ff;
            color: white;
        }
    </style>
@endsection
