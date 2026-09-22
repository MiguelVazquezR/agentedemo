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
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('nombre');
            $table->string('categoria', 40);
            $table->string('familia');
            $table->string('marca')->nullable();
            $table->string('unidad', 20);
            $table->string('presentacion', 60)->nullable();
            $table->string('medida', 60)->nullable();
            $table->string('color', 60)->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->string('moneda', 3)->default('MXN');
            $table->json('especificaciones')->nullable();
            $table->foreignId('proveedor_preferido_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['categoria', 'familia']);
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
