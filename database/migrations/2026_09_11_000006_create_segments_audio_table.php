<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments_audio', function (Blueprint $table) {
            $table->id();
            $table->enum('langue', ['bariba', 'peulh', 'dendi']);
            $table->enum('type_slot', ['commune', 'risque', 'duree', 'action', 'connecteur']);
            $table->string('valeur_slot'); // ex: "Banikoara", "secheresse_severe", "paillage_sol"
            $table->string('chemin_fichier');
            $table->string('locuteur')->nullable();
            $table->unsignedInteger('duree_ms')->nullable();
            $table->timestamps();

            // Un seul segment par combinaison langue + type + valeur
            $table->unique(['langue', 'type_slot', 'valeur_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segments_audio');
    }
};
