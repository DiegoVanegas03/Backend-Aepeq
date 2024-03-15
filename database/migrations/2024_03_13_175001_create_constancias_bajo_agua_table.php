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
        Schema::create('constancias_bajo_agua', function (Blueprint $table) {
            $table->id();
            $table->integer('folio')->unique();
            $table->string('nombre_completo');
            $table->string('nombre_doc');
            $table->foreignId('taller_id')->nullable()->constrained('talleres')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancias_bajo_agua');
    }
};
