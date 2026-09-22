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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('razon_social');
            $table->string('rfc', 13);
            $table->string('contacto');
            $table->string('email');
            $table->string('telefono', 30);
            $table->string('ciudad');
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
