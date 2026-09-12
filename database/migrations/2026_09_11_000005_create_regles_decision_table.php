<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_decision', function (Blueprint $table) {
            $table->id();
            $table->string('niveau_risque');
            $table->unsignedInteger('duree_min');
            $table->unsignedInteger('duree_max');
            $table->foreignId('action_id')->constrained('catalogue_actions')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_decision');
    }
};
