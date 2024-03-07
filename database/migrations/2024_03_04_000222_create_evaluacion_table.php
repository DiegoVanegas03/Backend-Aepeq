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
        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('repuestas_select');
            $table->string('mas_gustado',200);
            $table->string('menos_gustado',200);
            $table->string('mejoras',200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion');
    }
};
