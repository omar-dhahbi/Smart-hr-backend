<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tache extends Model
{
    use HasFactory;
    protected $table = "taches";
    protected $fillable = ['Nom', 'Description', 'DateDebut', 'DateFin', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
   public function users()
    {
        return $this->belongsToMany(User::class,'departement_tache_users');
    }

}






