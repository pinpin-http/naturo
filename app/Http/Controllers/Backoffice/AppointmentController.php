<?php

namespace App\Http\Controllers\Backoffice;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        // Retourne une vue pour afficher le calendrier ou la gestion des rendez-vous
        return view('backoffice.pages.calendar'); // Assure-toi que ce fichier de vue existe
    }

    // Méthode pour récupérer les créneaux disponibles en fonction de la durée
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'slot_duration' => 'required|integer', // La durée du créneau en minutes
        ]);

        $date = $request->date;
        $duration = (int) $request->slot_duration; // Conversion en entier

        // Les horaires d'ouverture, par exemple de 9h à 18h
        $openingTime = Carbon::createFromTime(9, 0, 0); // 9h00
        $closingTime = Carbon::createFromTime(18, 0, 0); // 18h00

        // Récupérer tous les rendez-vous de cette journée
        $appointments = Appointment::where('date', $date)->get();

        // Tableau pour stocker les créneaux horaires disponibles
        $availableSlots = [];

        // Parcourir tous les créneaux horaires de la journée
        while ($openingTime->lt($closingTime)) {
            $slotStart = $openingTime->copy();
            $slotEnd = $openingTime->copy()->addMinutes($duration); // Créneau de 1h30

            // Vérifier si le créneau chevauche un rendez-vous existant
            $isAvailable = true;
            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);

                // Si le créneau chevauche un rendez-vous, il n'est pas disponible
                if ($slotStart->between($appointmentStart, $appointmentEnd) ||
                    $slotEnd->between($appointmentStart, $appointmentEnd)) {
                    $isAvailable = false;
                    break;
                }
            }

            // Si le créneau est disponible, on l'ajoute à la liste
            if ($isAvailable) {
                $availableSlots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                ];
            }

            // Incrémenter l'heure d'ouverture pour passer au créneau suivant
            $openingTime->addMinutes($duration); // Ajoute directement la durée sans pause
        }

        return response()->json($availableSlots);
    }

    public function getDailySlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Les horaires d'ouverture
        $openingTime = Carbon::createFromTime(9, 0); // 9h00
        $closingTime = Carbon::createFromTime(18, 0); // 18h00
        $slotDuration = 90; // Durée de chaque créneau, ici 1 heure 30 minutes

        // Récupérer les rendez-vous existants pour cette journée
        $appointments = Appointment::where('date', $date)->get();

        // Tableau pour stocker les créneaux horaires
        $dailySlots = [];
        $maxDay = 2;

        

        // Parcourir les créneaux horaires de la journée
        while ($openingTime->lt($closingTime)) {
            $slotStart = $openingTime->copy();
            $slotEnd = $openingTime->copy()->addMinutes((int) $slotDuration); // Créneau de 1h30

            // Vérifier si le créneau est déjà réservé
            $isBooked = $appointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);
                return $slotStart->between($appointmentStart, $appointmentEnd) || 
                       $slotEnd->between($appointmentStart, $appointmentEnd);
            });

            // Ajouter le créneau au tableau avec sa disponibilité
            $dailySlots[] = [
                'start_time' => $slotStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'is_booked' => $isBooked
            ];

            // Passer au créneau suivant
            $openingTime->addMinutes((int) $slotDuration); // Pas de pause
        }

        return response()->json($dailySlots);
    }

    // Récupérer les événements à afficher dans le calendrier
    public function fetchEvents(Request $request)
    {
        // Récupérer les rendez-vous entre les dates de début et de fin envoyées par FullCalendar
        $start = $request->query('start');
        $end = $request->query('end');

        $appointments = Appointment::whereBetween('date', [$start, $end])->get();

        // Mapper les rendez-vous au format attendu par FullCalendar
        $events = $appointments->map(function($appointment) {
            return [
                'id' => $appointment->id,
                'title' => 'Rendez-vous',
                'start' => $appointment->date . 'T' . $appointment->start_time,
                'end' => $appointment->date . 'T' . $appointment->end_time,
                'extendedProps' => [
                    'duration' => $appointment->duration, // Si tu as un champ 'duration'
                ],
            ];
        });

        return response()->json($events);
    }

    // Méthode pour stocker un nouveau rendez-vous (store)
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'slot_duration' => 'required|integer',
            'client_name' => 'required',
            'client_email' => 'required|email',
        ]);

        // Calcul de l'heure de fin du rendez-vous
        $startTime = Carbon::parse($request->input('start_time'));
        $endTime = $startTime->copy()->addMinutes((int)$request->input('slot_duration'));

        // Vérifier s'il y a un conflit avec un rendez-vous existant
        $conflictingAppointment = Appointment::where('date', $request->input('date'))
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->exists();

        if ($conflictingAppointment) {
            return response()->json(['error' => 'Un rendez-vous existe déjà à cette heure.'], 409);
        }

        // Stocker le rendez-vous
        Appointment::create([
            'date' => $request->input('date'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'client_name' => $request->input('client_name'),
            'client_email' => $request->input('client_email'),
            'is_booked' => true,
        ]);

        return response()->json(['success' => 'Rendez-vous ajouté avec succès.']);
    }
}
