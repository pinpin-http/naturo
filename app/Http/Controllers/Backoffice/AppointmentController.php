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
        $validatedData = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'slot_duration' => 'required|integer',
            'client_id' => 'nullable|exists:users,id',
            'appointment_type' => 'nullable|string',
        ]);
    
        try {
            $startTime = Carbon::parse($request->input('start_time'));
            $slotDuration = (int) $request->input('slot_duration');
            $endTime = $startTime->copy()->addMinutes($slotDuration);
    
            // Récupérer tous les rendez-vous existants pour cette date, triés par heure de début
            $appointments = Appointment::where('date', $request->input('date'))
                ->orderBy('start_time', 'asc')
                ->get();
    
            // Vérifier les conflits
            foreach ($appointments as $existingAppointment) {
                $existingStartTime = Carbon::parse($existingAppointment->start_time);
                $existingEndTime = Carbon::parse($existingAppointment->end_time);
    
                // Si le nouveau rendez-vous commence avant la fin du rendez-vous existant
                if ($startTime->between($existingStartTime, $existingEndTime) || $startTime->lt($existingEndTime)) {
                    // Ajuster l'heure de début pour qu'elle soit 1 minute après la fin de ce rendez-vous existant
                    $startTime = $existingEndTime->copy()->addMinute();
                    $endTime = $startTime->copy()->addMinutes($slotDuration);
                }
            }
    
            // Récupération des informations du client s'il est fourni
            $client = $request->input('client_id') ? User::find($request->input('client_id')) : null;
    
            // Créer le rendez-vous
            $appointment = Appointment::create([
                'date' => $request->input('date'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'client_name' => $client ? $client->firstname . ' ' . $client->lastname : null,
                'client_email' => $client ? $client->email : null,
                'is_booked' => $client != null,
                'appointment_type' => $request->input('appointment_type'),
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
             $startTime = Carbon::parse($appointment->start_time)->format('H:i');
             $endTime = Carbon::parse($appointment->end_time)->format('H:i');
             
             return [
                 'id' => $appointment->id,
                 'title' => "{$startTime} - {$endTime} " . ($appointment->client_name ?? 'Créneau disponible'),
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

     public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json(['success' => 'Rendez-vous annulé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    public function update(Request $request, $id)
{
    // Valider les données de la requête
    $validatedData = $request->validate([
        'date' => 'required|date',
        'start_time' => 'required',
        'slot_duration' => 'required|integer',
        'client_id' => 'nullable|exists:users,id',
        'appointment_type' => 'nullable|string',
    ]);

    try {
        // Trouver le rendez-vous à mettre à jour
        $appointment = Appointment::findOrFail($id);

        // Déterminer les nouveaux horaires de début et de fin du rendez-vous
        $startTime = Carbon::parse($request->input('start_time'));
        $slotDuration = (int) $request->input('slot_duration');
        $endTime = $startTime->copy()->addMinutes($slotDuration);

        // Récupérer les rendez-vous existants pour la même date (à l'exclusion du rendez-vous actuel)
        $appointments = Appointment::where('date', $request->input('date'))
            ->where('id', '!=', $id)
            ->orderBy('start_time', 'asc')
            ->get();

        // Vérifier les conflits avec les rendez-vous existants
        foreach ($appointments as $conflict) {
            $conflictStartTime = Carbon::parse($conflict->start_time);
            $conflictEndTime = Carbon::parse($conflict->end_time);

            // Si le nouveau rendez-vous commence avant la fin d'un rendez-vous existant
            if ($startTime->between($conflictStartTime, $conflictEndTime) || $startTime->lt($conflictEndTime)) {
                // Ajuster l'heure de début pour qu'elle soit 1 minute après la fin de ce rendez-vous existant
                $startTime = $conflictEndTime->copy()->addMinute();
                $endTime = $startTime->copy()->addMinutes($slotDuration);
            }
        }

        // Il faut vérifier à nouveau pour s'assurer que la nouvelle heure de fin ne chevauche aucun rendez-vous
        $additionalConflicts = Appointment::where('date', $request->input('date'))
            ->where('id', '!=', $id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                      ->orWhereBetween('end_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                      ->orWhere(function($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime->format('H:i:s'))
                            ->where('end_time', '>=', $endTime->format('H:i:s'));
                      });
            })->orderBy('start_time')
            ->get();

        // Réajuster les conflits supplémentaires s'il y en a
        foreach ($additionalConflicts as $conflict) {
            $conflictStartTime = Carbon::parse($conflict->start_time);
            $conflictEndTime = Carbon::parse($conflict->end_time);
            
            // Calculer la durée de ce rendez-vous conflictuel
            $conflictDuration = $conflictEndTime->diffInMinutes($conflictStartTime);

            // Le nouveau rendez-vous commence 1 minute après la fin du rendez-vous conflictuel
            $startTime = $conflictEndTime->copy()->addMinute();
            $endTime = $startTime->copy()->addMinutes($conflictDuration);

            // Mettre à jour le rendez-vous conflictuel pour éviter l'empiètement
            $conflict->update([
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
            ]);

            // Ajuster les variables pour les prochains conflits éventuels
            $startTime = $startTime;
            $endTime = $endTime;
        }

        // Récupérer les informations du client s'il est fourni
        $client = $request->input('client_id') ? User::find($request->input('client_id')) : null;

        // Mettre à jour le rendez-vous d'origine avec les nouvelles informations
        $appointment->update([
            'date' => $request->input('date'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'client_name' => $client ? $client->firstname . ' ' . $client->lastname : null,
            'client_email' => $client ? $client->email : null,
            'is_booked' => $client != null,
            'appointment_type' => $request->input('appointment_type'),
        ]);

        return response()->json(['success' => 'Rendez-vous modifié avec succès.']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


        


     

}
