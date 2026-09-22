<?php

use App\Enums\EstatusObra;
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
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->string('cliente');
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('estado');
            $table->string('responsable');
            $table->string('telefono_contacto', 30)->nullable();
            $table->decimal('presupuesto_autorizado', 14, 2)->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin_estimada')->nullable();
            $table->string('estatus', 20)->default(EstatusObra::EnEjecucion->value);
            $table->timestamps();

            $table->index('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
