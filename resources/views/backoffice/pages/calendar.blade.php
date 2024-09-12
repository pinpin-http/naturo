@extends('layouts.backoffice.app')

@section('content')
    @include('layouts.backoffice.navbars.auth.topnav', ['title' => 'Gestion des rendez-vous'])

<div class="min-height-300 bg-primary position-absolute w-100"></div>

<!-- Titre dans la zone verte -->
<h1 style=" margin-left: 70px; color: white;">Calendrier des disponibilités</h1>

<div card-body px-0 pt-0 pb-2>
    <!-- Conteneur pour le calendrier -->
    <div class="container">
        <div id="calendar"></div>
    </div>
</div>

<!-- Bouton d'ouverture du modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRdvModal" style="display:none;" id="openModalButton">
  Ouvrir le formulaire
</button>

<!-- Modal -->
<div class="modal fade" id="addRdvModal" tabindex="-1" aria-labelledby="addRdvModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addRdvModalLabel">Ajouter un Rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Formulaire d'ajout de rendez-vous -->
        <form id="addRdvForm" action="{{ route('calendar.add') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="date" class="form-label">Date :</label>
            <input type="text" name="date" id="modalDate" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label for="start_time" class="form-label">Heure de début :</label>
            <select name="start_time" id="start_time" class="form-select" required>
              <!-- Les options seront générées dynamiquement -->
            </select>
          </div>

          <div class="mb-3">
            <label for="duration" class="form-label">Durée (en minutes) :</label>
            <input type="number" name="duration" id="duration" class="form-control" required min="10">
          </div>

          <button type="submit" class="btn btn-primary">Ajouter le rendez-vous</button>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

<style>
    #calendar {
        position:relative;
        background-color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
        width: 100%;
        height: 600px;
    }

    /* Ajoute des bordures noires aux jours du calendrier */
    .fc-daygrid-day {
        border: 1px solid black;
    }

    .min-height-300.bg-primary {
        z-index: -1 !important; /* Envoie la bande verte à l'arrière-plan */
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            selectable: true,
            events: '/fetch-events', // Récupérer les événements via une API
            validRange: {
                start: new Date().toISOString().split('T')[0], // Empêche la sélection de dates passées
            },
            eventDidMount: function(info) {
                // Ajouter du texte à la case du calendrier indiquant les créneaux disponibles
                if (info.event.extendedProps.numberOfAppointments > 0) {
                    info.el.innerHTML += `<div>${info.event.extendedProps.numberOfAppointments} créneau(x) dispo</div>`;
                }

                // Ajouter un tooltip au survol
                new Tooltip(info.el, {
                    title: info.event.extendedProps.details, // Détails du rendez-vous
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            dateClick: function(info) {
                // Ouvre le modal et autofill la date cliquée
                document.getElementById('modalDate').value = info.dateStr;
                loadAvailableTimes(info.dateStr);
                document.getElementById('openModalButton').click(); // Simule un clic pour ouvrir le modal
            }
        });
        calendar.render();
    }
    
    // Charger les créneaux disponibles pour une date donnée
    function loadAvailableTimes(date) {
        fetch(`/get-creneaux-disponibles/${date}`)
            .then(response => response.json())
            .then(data => {
                const startTimeSelect = document.getElementById('start_time');
                startTimeSelect.innerHTML = ''; // Réinitialiser le sélecteur

                const bookedTimes = data.bookedTimes;

                for (let i = 9; i < 19; i++) {
                    for (let j = 0; j < 60; j += 10) {
                        const timeStr = `${i.toString().padStart(2, '0')}:${j.toString().padStart(2, '0')}`;
                        const option = document.createElement('option');
                        option.value = timeStr;
                        option.textContent = timeStr;

                        // Désactiver les créneaux déjà pris
                        if (isTimeBooked(bookedTimes, timeStr)) {
                            option.disabled = true;
                            option.textContent += ' (Indisponible)';
                        }

                        startTimeSelect.appendChild(option);
                    }
                }
            });
    }

    // Vérifier si un créneau horaire est déjà pris
    function isTimeBooked(bookedTimes, time) {
        for (let booked of bookedTimes) {
            if (booked.startTime <= time && time < booked.endTime) {
                return true; // L'heure est déjà réservée ou chevauche un autre rendez-vous
            }
        }
        return false;
    }
});
</script>
@endsection
