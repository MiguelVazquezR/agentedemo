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
        Schema::create('orden_compra_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destinatario');
            $table->string('cc')->nullable();
            $table->string('asunto');
            $table->text('mensaje')->nullable();
            $table->string('pdf_archivo')->nullable();
            $table->boolean('exito')->default(false);
            $table->text('error')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_envios');
    }
};
