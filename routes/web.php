<?php

use App\Http\Controllers\Agente\BorradorController;
use App\Http\Controllers\Agente\CatalogoController;
use App\Http\Controllers\Agente\ConversacionController;
use App\Http\Controllers\Agente\MensajeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Ordenes\EnvioDeOrdenController;
use App\Http\Controllers\Ordenes\OrdenCompraController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');

    Route::get('ordenes', [OrdenCompraController::class, 'index'])->name('ordenes.index');
    Route::get('ordenes/{orden}', [OrdenCompraController::class, 'show'])->name('ordenes.show');
    Route::get('ordenes/{orden}/pdf', [OrdenCompraController::class, 'pdf'])->name('ordenes.pdf');
    Route::post('ordenes/{orden}/enviar', [EnvioDeOrdenController::class, 'store'])->name('ordenes.enviar');

    Route::prefix('agente')->name('agente.')->group(function () {
        Route::get('/', [ConversacionController::class, 'index'])->name('index');
        Route::post('/', [ConversacionController::class, 'store'])->name('store');
        Route::delete('{conversacion}', [ConversacionController::class, 'destroy'])->name('destroy');

        Route::post('{conversacion}/mensajes', [MensajeController::class, 'store'])->name('mensajes.store');
        Route::post('{conversacion}/orden', [OrdenCompraController::class, 'store'])->name('orden.store');

        Route::patch('{conversacion}/borrador', [BorradorController::class, 'update'])->name('borrador.update');
        Route::post('{conversacion}/borrador/partidas', [BorradorController::class, 'store'])->name('borrador.partidas.store');
        Route::patch('{conversacion}/borrador/partidas', [BorradorController::class, 'updatePartida'])->name('borrador.partidas.update');
        Route::delete('{conversacion}/borrador/partidas', [BorradorController::class, 'destroyPartida'])->name('borrador.partidas.destroy');
    });
});

require __DIR__.'/settings.php';
