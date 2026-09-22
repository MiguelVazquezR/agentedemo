<?php

use App\Http\Controllers\Agente\BorradorController;
use App\Http\Controllers\Agente\CatalogoController;
use App\Http\Controllers\Agente\ConversacionController;
use App\Http\Controllers\Agente\MensajeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');

    Route::prefix('agente')->name('agente.')->group(function () {
        Route::get('/', [ConversacionController::class, 'index'])->name('index');
        Route::post('/', [ConversacionController::class, 'store'])->name('store');
        Route::delete('{conversacion}', [ConversacionController::class, 'destroy'])->name('destroy');

        Route::post('{conversacion}/mensajes', [MensajeController::class, 'store'])->name('mensajes.store');

        Route::patch('{conversacion}/borrador', [BorradorController::class, 'update'])->name('borrador.update');
        Route::post('{conversacion}/borrador/partidas', [BorradorController::class, 'store'])->name('borrador.partidas.store');
        Route::patch('{conversacion}/borrador/partidas', [BorradorController::class, 'updatePartida'])->name('borrador.partidas.update');
        Route::delete('{conversacion}/borrador/partidas', [BorradorController::class, 'destroyPartida'])->name('borrador.partidas.destroy');
    });
});

require __DIR__.'/settings.php';
