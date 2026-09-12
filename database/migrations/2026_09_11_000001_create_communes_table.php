<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('departement')->default('Alibori');
            // À documenter avant de généraliser à d'autres communes (voir doc de synthèse, action n°6)
            $table->string('couverture_reseau_estimee')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
