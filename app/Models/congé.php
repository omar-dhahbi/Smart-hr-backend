<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class congé extends Model
{
    use HasFactory;
     protected $table = "congés";
    protected $fillable = ['user_id', 'type', 'dateDebut', 'dateFin', 'nbrJour', 'photo', 'cause', 'status', 'enCongé'];
    protected $hidden = ['created_at', 'updated_at'];
       public function User()
    {
        return $this->hasMany(User::class);
    }
}
