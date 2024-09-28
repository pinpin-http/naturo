<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = ['date', 'start_time', 'end_time', 'client_name', 'client_email', 'is_booked'];

    public function getDurationAttribute()
    {
        return Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
    }

    public static function isSlotAvailable($date, $startTime, $endTime)
    {
        return !self::where('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
            })->exists();
    }
}
