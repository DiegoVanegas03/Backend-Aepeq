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
        Schema::create('facturas_promotores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotor_id')->constrained('promotores')->cascadeOnDelete()->cascadeOnUpdate(); 
            $table->string('cfdi');
            $table->string('factura_realizada')->default('No');
            $table->string('factura')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_promotores');
    }
};
