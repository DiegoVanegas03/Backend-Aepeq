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
        Schema::create('token_de_registro', function (Blueprint $table) {
            $table->id();
            $table->string('token_de_registro')->unique();
            $table->string('nombre')->nullable();
            $table->string('tipo_inscripcion')->nullable();
            $table->integer('estado_del_registro')->default(0);
            $table->foreignId('promotor_id')->constrained('promotores')->cascadeOnDelete()->cascadeOnUpdate(); 
            $table->timestamps();
            // Definir clave foránea
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_de_registro');
    }
};
