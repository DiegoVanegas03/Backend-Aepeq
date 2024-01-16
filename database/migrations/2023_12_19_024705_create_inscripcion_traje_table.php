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
        Schema::create('inscripcion_traje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primer_participante')->unique()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('segundo_participante')->unique()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('estado_representante')->constrained('estados_mexico')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nombre_doc')->nullable();
            $table->string('nombre_pista')->nullable();
            $table->timestamp('confirmacion_segundo_participante')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_traje');
    }
};
