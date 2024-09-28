<?php

namespace App\Http\Controllers\Backoffice;

use App\Models\Appointment;
use App\Models\User; // Import du modèle User
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        // Récupérer tous les clients existants
        $clients = User::role('client')->get(['id', 'firstname', 'lastname', 'email']); // Supposons que tu utilises Spatie pour les rôles

        // Passe les clients à la vue
        return view('backoffice.pages.calendar', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'slot_duration' => 'required|integer',
            'client_name' => 'nullable|string',
            'client_email' => 'nullable|email',
        ]);

        // Conversion de la durée en entier
        $slotDuration = (int) $request->input('slot_duration');

        try {
            $startTime = Carbon::parse($request->input('start_time'));
            $endTime = $startTime->copy()->addMinutes($slotDuration); // Utilisation de $slotDuration en tant qu'entier

            if (!Appointment::isSlotAvailable($request->input('date'), $startTime, $endTime)) {
                return response()->json(['error' => 'Un rendez-vous existe déjà à cette heure.'], 409);
            }

            $appointment = Appointment::create([
                'date' => $request->input('date'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'client_name' => $request->input('client_name'),
                'client_email' => $request->input('client_email'),
                'is_booked' => $request->input('client_name') && $request->input('client_email'),
            ]);

            return response()->json(['success' => 'Rendez-vous ajouté avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getDailySlots(Request $request)
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json(['error' => 'Date non spécifiée'], 400);
        }

        // Récupérer les rendez-vous existants pour la date donnée
        $appointments = Appointment::where('date', $date)->get(['id', 'start_time', 'end_time', 'is_booked']);

        return response()->json($appointments);
    }
    
     // Méthode pour récupérer les rendez-vous à afficher dans le calendrier
     public function fetchEvents(Request $request)
     {
         // Récupère les rendez-vous entre les dates de début et de fin envoyées par FullCalendar
         $start = $request->query('start');
         $end = $request->query('end');
 
         // Récupérer les rendez-vous entre les dates
         $appointments = Appointment::whereBetween('date', [$start, $end])->get();
 
         // Mapper les rendez-vous au format attendu par FullCalendar
         $events = $appointments->map(function($appointment) {
             return [
                 'id' => $appointment->id,
                 'title' => $appointment->client_name ?? 'Créneau disponible',
                 'start' => $appointment->date . 'T' . $appointment->start_time,
                 'end' => $appointment->date . 'T' . $appointment->end_time,
                 'color' => $appointment->is_booked ? '#ff4d4d' : '#4CAF50', // Rouge pour réservé, vert pour disponible
                 'extendedProps' => [
                     'client_email' => $appointment->client_email,
                     'duration' => $appointment->duration ?? '',
                 ],
             ];
         });
 
         return response()->json($events);
     }


     public function getAvailableSlots(Request $request)
     {
         $request->validate([
             'date' => 'required|date',
             'slot_duration' => 'required|integer',
         ]);
     
         $date = $request->date;
         $duration = (int) $request->slot_duration;
     
         $openingTime = Carbon::createFromTime(15, 0); // Heures d'ouverture
         $closingTime = Carbon::createFromTime(18, 0); // Heures de fermeture
     
         $appointments = Appointment::where('date', $date)->get(); // Récupère les rendez-vous de la journée
     
         $availableSlots = [];
         while ($openingTime->lt($closingTime)) {
             $slotStart = $openingTime->copy();
             $slotEnd = $slotStart->copy()->addMinutes($duration);
     
             if ($slotEnd->gt($closingTime)) {
                 break;
             }
     
             // Vérifie si le créneau est disponible
             $isAvailable = $appointments->every(function ($appointment) use ($slotStart, $slotEnd) {
                 return !(
                     $slotStart->between($appointment->start_time, $appointment->end_time) ||
                     $slotEnd->between($appointment->start_time, $appointment->end_time)
                 );
             });
     
             if ($isAvailable) {
                 $availableSlots[] = [
                     'start_time' => $slotStart->format('H:i'),
                     'end_time' => $slotEnd->format('H:i'),
                 ];
             }
     
             $openingTime->addMinutes($duration); // Passer au créneau suivant
         }
     
         return response()->json($availableSlots);
     }
     

}
