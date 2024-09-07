<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    use HasFactory;
   
    // Autoriser l'assignation en masse pour ces champs
    protected $fillable = ['user_id', 'action', 'created_at', 'updated_at'];
      // Définir la relation entre UserActionLog et User
      public function user()
      {
          return $this->belongsTo(User::class);
      }
}
