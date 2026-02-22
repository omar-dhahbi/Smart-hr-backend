<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class departements extends Model
{
    use HasFactory;
     protected $table = "departements";
    protected $fillable = ['NomDepartement', 'Description'];
    protected $hidden = ['created_at', 'updated_at'];
       public function User()
    {
        return $this->hasMany(User::class);
    }
}
