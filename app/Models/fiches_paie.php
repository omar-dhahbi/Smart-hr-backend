<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fiches_paie extends Model
{
    use HasFactory;

    protected $table = 'fiches_paies';

    protected $fillable = [
        'user_id',
        'file',
    ];
}
