<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [FACULTATIF pour le MVP SVI simulé] — nécessaire seulement si le canal
// de démo retenu est le relais WhatsApp plutôt que le SVI.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relais_cooperatives', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('commune_id')->constrained('communes')->cascadeOnDelete();
            $table->string('telephone')->nullable();
            $table->string('whatsapp_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relais_cooperatives');
    }
};
