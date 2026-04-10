<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('congés', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('type');
            $table->date('dateDebut');
            $table->date('dateFin');
            $table->integer('nbrJour');
            $table->string('photo')->nullable();
            $table->string('cause')->nullable();
            $table->string('status')->nullable();
            $table->string('status2')->default('attente');

            $table->boolean('enCongé')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('congés');
    }
};
