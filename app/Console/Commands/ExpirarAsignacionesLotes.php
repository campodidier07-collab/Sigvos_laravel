<?php

namespace App\Console\Commands;

use App\Models\AsignacionLote;
use Illuminate\Console\Command;

class ExpirarAsignacionesLotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sigvos:expirar-asignaciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita y desactiva asignaciones huérfanas o expiradas de trabajadores en lotes agrícolas (DP-05)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando auditoría de asignaciones de lotes...');

        // 1. Asignaciones ligadas a usuarios inactivos
        $expiradasPorUsuario = AsignacionLote::where('activo', true)
            ->whereHas('usuario', function ($q) {
                $q->where('activo', false);
            })
            ->update([
                'activo'       => false,
                'clave_activa' => null,
            ]);

        // 2. Asignaciones ligadas a lotes inactivos
        $expiradasPorLote = AsignacionLote::where('activo', true)
            ->whereHas('lote', function ($q) {
                $q->where('activo', false);
            })
            ->update([
                'activo'       => false,
                'clave_activa' => null,
            ]);

        $total = $expiradasPorUsuario + $expiradasPorLote;
        $this->info("Proceso completado. Se desactivaron {$total} asignación(es) obsoleta(s) o de usuarios/lotes inactivos.");

        return Command::SUCCESS;
    }
}
