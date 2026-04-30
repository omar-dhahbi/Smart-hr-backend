<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sessions extends Model
{
    use HasFactory;

    protected $table = 'sessions';

    protected $fillable = ['user_id', 'heure_entree', 'heure_sortie', 'nb_heures', 'gain', 'date'];

    protected $hidden = ['created_at', 'updated_at'];
}
