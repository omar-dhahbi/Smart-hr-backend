<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tache extends Model
{
    use HasFactory;
    protected $table = "taches";
    protected $fillable = ['Nom', 'Description', 'DateDebut', 'DateFin', 'user_id', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
     public function user()
    {
        return $this->belongsTo(User::class);
    } 
}






