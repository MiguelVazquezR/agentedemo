<?php

use App\Enums\EstatusOrdenCompra;
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
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->nullable()->unique();
            $table->string('estatus', 20)->default(EstatusOrdenCompra::Borrador->value);
            $table->foreignId('conversacion_id')->nullable()->constrained('conversaciones')->nullOnDelete();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('condiciones_pago')->nullable();
            $table->date('fecha_requerida')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('generada_at')->nullable();
            $table->timestamp('enviada_at')->nullable();
            $table->timestamps();

            $table->index('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra');
    }
};
