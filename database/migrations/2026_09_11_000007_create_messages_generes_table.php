<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages_generes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_id')->constrained('bulletins')->cascadeOnDelete();
            $table->foreignId('regle_id')->constrained('regles_decision')->cascadeOnDelete();
            $table->enum('langue', ['bariba', 'peulh', 'dendi']);
            $table->string('chemin_fichier_final');
            $table->timestamp('genere_le')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_generes');
    }
};
