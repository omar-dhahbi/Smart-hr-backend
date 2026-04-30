<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvanceSalire extends Model
{
    use HasFactory;

    protected $table = 'avance_salires';

    protected $fillable = [
        'user_id',
        'SalaireAvance',
        'Description',
        'status',
        'status2',
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
