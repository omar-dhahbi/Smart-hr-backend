<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'verif_email',
        'password',
        'photo',
        'role',
        'code',
        'connecte',
        'session_ouverte',
        'session_fermee',
        'prix_heure',
        'salaire',
        'nb_heure_par_jour',
        'nb_jour_conge',
        'Contrat',
        'status',
        'date_naissance',
        'departement_id',
        'jours_absence',
        'jours_presence',
        'derniere_presence',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function fichesPaie()
    {
        return $this->hasMany(Fiches_paie::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
