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
        Schema::create('orden_compra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materiales');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('importe', 14, 2);
            $table->string('notas')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->unique(['orden_compra_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_items');
    }
};
