<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projet_tache_users extends Model
{
    use HasFactory;

    protected $table = 'projet_tache_users';

    protected $fillable = ['tache_id', 'projet_id', 'user_id'];

    protected $hidden = ['updated_at'];

    protected $dates = ['deleted_at'];

    // public function Projets()
    // {
    //     return $this->belongsTo(Projets::class);
    // }

    // public function taches()
    // {
    //     return $this->belongsTo(taches::class);
    // }
}
