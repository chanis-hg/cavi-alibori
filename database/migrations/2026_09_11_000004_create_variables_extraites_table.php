<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variables_extraites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_id')->constrained('bulletins')->cascadeOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->nullOnDelete();
            $table->string('niveau_risque'); // ex: secheresse_severe, secheresse_moderee, risque_faible
            $table->unsignedInteger('duree_jours')->nullable();
            $table->float('confiance')->nullable();
            $table->enum('methode', ['embeddings', 'regex_fallback']);
            $table->json('reponse_brute_service')->nullable(); // trace de ce que le microservice a renvoyé
            $table->timestamp('extrait_le')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variables_extraites');
    }
};
