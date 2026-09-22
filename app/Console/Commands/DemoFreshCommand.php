<?php

namespace App\Console\Commands;

use App\Enums\EstatusOrdenCompra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:fresh {--force : Reinicia sin pedir confirmación}')]
#[Description('Reinicia la demostración: limpia órdenes y conversaciones, y vuelve a sembrar los datos de ejemplo')]
class DemoFreshCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Se borrarán las órdenes de compra, las conversaciones y los mensajes. ¿Continuar?')) {
            $this->components->warn('Operación cancelada.');

            return self::FAILURE;
        }

        OrdenCompra::query()->delete();
        Conversacion::query()->delete();

        $this->call('db:seed', ['--force' => true]);

        $this->components->info('Demostración reiniciada.');
        $this->components->twoColumnDetail('Materiales en catálogo', (string) Material::query()->count());
        $this->components->twoColumnDetail('Proveedores', (string) Proveedor::query()->count());
        $this->components->twoColumnDetail('Obras', (string) Obra::query()->count());
        $this->components->twoColumnDetail('Órdenes de compra', OrdenCompra::query()->generadas()->count().' generadas y '.OrdenCompra::query()->where('estatus', EstatusOrdenCompra::Borrador->value)->count().' borrador');
        $this->components->twoColumnDetail('Conversaciones', (string) Conversacion::query()->count());

        return self::SUCCESS;
    }
}
