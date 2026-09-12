<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->enum('source', ['meteo_benin', 'sap_mr', 'test']);
            $table->text('texte_brut');
            $table->enum('statut_extraction', ['en_attente', 'extrait', 'echec'])->default('en_attente');
            $table->timestamp('recu_le')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
