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
        Schema::create('promotores', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('nombre');
            $table->integer('numero_de_becas');
            $table->integer('precio_por_beca');
            $table->integer('precio_total');
            $table->string('comprobante_de_pago')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotores');
    }
};
