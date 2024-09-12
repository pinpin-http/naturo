<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event; // Assure-toi d'avoir un modèle Event pour stocker les créneaux
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        return view('backoffice.pages.calendar '); // Renvoie vers la vue du calendrier
    }
   
   
   
    public function addRdv(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'duration' => 'required|integer',
        ]);

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes((int) $request->duration);
        
       // Vérification qu'il n'y a pas de chevauchement avec un autre rendez-vous
        $overlappingRdv = RendezVous::whereDate('date', $request->date)
        ->where(function($query) use ($startTime, $endTime) {
            $query->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween(DB::raw("start_time + INTERVAL '10 minutes'"), [$startTime, $endTime]);
        })
        ->exists();

        if ($overlappingRdv) {
            return response()->json(['error' => 'Un rendez-vous existe déjà à cette heure.'], 409);
        }

        // Ajouter le rendez-vous si pas de chevauchement
        RendezVous::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'duration' => $request->duration,
            'practicien_id' => auth()->id(),
        ]);

        return response()->json(['success' => 'Rendez-vous ajouté avec succès.']);
    }


    public function fetchEvents(Request $request)
{
    $events = RendezVous::whereDate('date', '>=', now())
        ->get()
        ->map(function ($rdv) {
            return [
                'id' => $rdv->id,
                'title' => 'Rendez-vous',
                'start' => $rdv->date . ' ' . $rdv->start_time,
                'end' => Carbon::parse($rdv->start_time)->addMinutes($rdv->duration + 10), // Inclure la marge de 10 minutes
                'extendedProps' => [
                    'duration' => $rdv->duration,
                    'numRdv' => RendezVous::whereDate('date', $rdv->date)->count(),
                ],
            ];
        });

    return response()->json($events);
}


    // Ajouter un créneau
    public function storeEvent(Request $request)
    {
        $event = Event::create([
            'title' => 'Créneau disponible',
            'start_time' => $request->input('start'),
            'end_time' => $request->input('end'),
        ]);

        return response()->json(['status' => 'Créneau ajouté']);
    }
    
    
    public function getCreneauxDisponibles($date)
    {
        $rdvs = RendezVous::where('date', $date)->get();
        
        $bookedTimes = $rdvs->map(function($rdv) {
            $startTime = $rdv->start_time;
            $endTime = date('H:i', strtotime($startTime) + ($rdv->duration + 10) * 60); // Marge de 10 min
            return [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];
        });

        return response()->json([
            'bookedTimes' => $bookedTimes
        ]);
    }

}
