<?php

use App\Enums\EstatusConversacion;
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
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->string('titulo');
            $table->string('estatus', 20)->default(EstatusConversacion::Abierta->value);
            $table->timestamp('ultimo_mensaje_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ultimo_mensaje_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};
