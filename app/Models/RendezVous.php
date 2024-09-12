<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;
    protected $table = 'rendez_vous'; // Nom correct de la table
    // Définis les champs autorisés à l'attribution de masse
    protected $fillable = [
        'date',
        'start_time',
        'duration',
        'practicien_id', // Ajoute tous les champs nécessaires ici
    ];
}
