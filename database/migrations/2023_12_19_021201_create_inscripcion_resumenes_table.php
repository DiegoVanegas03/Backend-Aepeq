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
        Schema::create('inscripcion_resumenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nombre_archivo')->nullable();
            $table->string('nombre_investigador');
            $table->string('apellidos_investigador');
            $table->integer('estado_de_revision')->default(0);
            $table->string('dictamen')->nullable();
            $table->string('oral/escrito')->nullable();
            $table->string('documento_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_resumenes');
    }
};
