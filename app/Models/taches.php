<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class taches extends Model
{
    use HasFactory;

    protected $table = 'taches';

    protected $fillable = ['Nom', 'Description', 'DateDebut', 'DateFin', 'status'];

    protected $hidden = ['created_at', 'updated_at'];

    // public function projets()
    // {
    //     return $this->belongsToMany(Projets::class, 'projet_tache_users');
    // }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'projet_tache_users');
    // }
}
