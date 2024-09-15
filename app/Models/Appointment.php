<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['date', 'start_time', 'end_time', 'client_name', 'client_email', 'is_booked'];

    // Vérifie si un créneau est disponible
    public static function isSlotAvailable($date, $startTime, $endTime)
    {
        return !self::where('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->exists();
    }
}
