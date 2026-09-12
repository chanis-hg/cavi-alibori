<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journaux_diffusion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages_generes')->cascadeOnDelete();
            $table->enum('canal', ['svi', 'whatsapp', 'radio']);
            $table->string('destinataire_ref')->nullable(); // numéro, id whatsapp, ou nom de radio
            $table->enum('statut', ['envoye', 'echec', 'ecoute_confirmee'])->default('envoye');
            $table->timestamp('envoye_le')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux_diffusion');
    }
};
