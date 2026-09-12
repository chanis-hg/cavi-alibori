<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogue_actions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ex: paillage_sol
            $table->string('description_fr');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogue_actions');
    }
};
