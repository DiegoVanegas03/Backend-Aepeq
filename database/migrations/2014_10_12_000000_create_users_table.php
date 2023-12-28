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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->binary('qr_code')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('numero_celular');
            $table->string('estado_provincia');
            $table->string('pais');
            $table->string('ocupacion');
            $table->string('lugar_trabajo');
            $table->string('tipo_inscripcion');
            $table->string('escuela')->nullable();
            $table->string('asociacion')->nullable();
            $table->string('documento_certificado')->nullable();
            $table->string('beca_pago');
            $table->string('metodo_pago')->nullable();
            $table->string('comprobante_pago')->nullable();
            $table->string('promotor')->nullable();
            $table->string('token_de_registro')->nullable();
            $table->integer('estado_del_registro')->default('1');
            $table->integer('rol')->default('1');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
