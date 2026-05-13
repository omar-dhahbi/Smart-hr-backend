<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('cin')->unique()->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('verif_email')->default(false);
            $table->string('password');
            $table->string('photo')->nullable();
            $table->string('role')->default('employee');
            $table->string('code')->nullable();
            $table->timestamp('session_ouverte')->nullable();
            $table->timestamp('session_fermee')->nullable();
            $table->double('prix_heure')->nullable();
            $table->double('salaire')->default(0);
            $table->double('nb_heure_par_jour')->nullable();
            $table->integer('nb_jour_conge')->default(21);
            $table->string('Contrat')->nullable();
            $table->boolean('status');
            $table->string('Genre')->nullable();
            $table->date('date_naissance');
            $table->boolean('enConge')->default(false);
            // $table->boolean('first_login')->default(true);
            $table->integer('jours_absence')->default(0);
            $table->integer('jours_presence')->default(0);
            $table->date('derniere_presence')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
