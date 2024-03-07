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
        Schema::create('constancias_taller', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ins_taller_id')
                ->nullable()
                ->constrained('inscripcion_talleres')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->integer('folio')->unique();
            $table->integer('hoja');
            $table->string('nombre_doc');
            $table->integer('correccion')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancias_taller');
    }
};
