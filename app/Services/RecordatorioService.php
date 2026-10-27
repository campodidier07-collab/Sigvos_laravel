<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Notificacion;
use App\Models\TipoActividad;
use App\Models\Usuario;
use Carbon\Carbon;

class RecordatorioService
{
    /**
     * Evalúa y genera recordatorios automáticos y alertas agronómicas (HU DP-10).
     */
    public function evaluarAlertasYRecordatorios(): array
    {
        $recordatoriosGenerados = $this->evaluarActividadesProximas();
        $alertasCosecha = $this->evaluarCosechasProximas();
        $alertasFertilizacion = $this->evaluarFertilizacionesVencidas();

        return [
            'recordatorios_actividades' => $recordatoriosGenerados,
            'alertas_cosecha'           => $alertasCosecha,
            'alertas_fertilizacion'     => $alertasFertilizacion,
        ];
    }

    /**
     * Criterio 2: Recordatorio automático de actividad próxima.
     * Cuando una actividad está programada para el día siguiente (o hoy), genera la notificación recordatoria.
     */
    public function evaluarActividadesProximas(): int
    {
        $mananaInicio = Carbon::tomorrow()->startOfDay()->toDateTimeString();
        $mananaFin = Carbon::tomorrow()->endOfDay()->toDateTimeString();
        $hoyInicio = Carbon::today()->startOfDay()->toDateTimeString();

        $actividadesProximas = Actividad::with(['cultivo.lote', 'tipoActividad', 'asignadoA'])
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->where(function($q) use ($hoyInicio, $mananaFin) {
                $q->whereBetween('fecha_programada', [Carbon::today()->toDateString(), Carbon::tomorrow()->toDateString()])
                  ->orWhereBetween('fecha_programada', [$hoyInicio, $mananaFin]);
            })
            ->get();

        $creadas = 0;
        $admins = Usuario::where('id_rol', 1)->where('activo', true)->get();

        foreach ($actividadesProximas as $act) {
            $esParaManana = Carbon::parse($act->fecha_programada)->isTomorrow();
            $tiempoTxt = $esParaManana ? 'mañana' : 'hoy';
            $titulo = "Recordatorio de actividad para {$tiempoTxt}";

            // 1. Notificar al trabajador asignado si existe
            if ($act->asignado_a) {
                $existeTrabajador = Notificacion::where('id_usuario', $act->asignado_a)
                    ->where('id_actividad', $act->id)
                    ->where('tipo', 'actividad')
                    ->where('titulo', 'like', '%Recordatorio%')
                    ->whereDate('creado_en', Carbon::today())
                    ->exists();

                if (! $existeTrabajador) {
                    Notificacion::create([
                        'id_usuario'   => $act->asignado_a,
                        'id_actividad' => $act->id,
                        'tipo'         => 'actividad',
                        'prioridad'    => 'alta',
                        'titulo'       => $titulo,
                        'mensaje'      => "Recordatorio: Tienes programada la actividad {$act->tipoActividad?->nombre} ({$act->cultivo?->codigo}) para {$tiempoTxt}.",
                        'url'          => route('actividades.show', $act),
                        'creado_en'    => now(),
                    ]);
                    $creadas++;
                }
            }

            // 2. Notificar a administradores
            foreach ($admins as $admin) {
                $existeAdmin = Notificacion::where('id_usuario', $admin->id)
                    ->where('id_actividad', $act->id)
                    ->where('tipo', 'actividad')
                    ->where('titulo', 'like', '%Recordatorio%')
                    ->whereDate('creado_en', Carbon::today())
                    ->exists();

                if (! $existeAdmin) {
                    Notificacion::create([
                        'id_usuario'   => $admin->id,
                        'id_actividad' => $act->id,
                        'tipo'         => 'actividad',
                        'prioridad'    => 'normal',
                        'titulo'       => $titulo,
                        'mensaje'      => "Actividad programada para {$tiempoTxt}: {$act->tipoActividad?->nombre} en el cultivo {$act->cultivo?->codigo} (Asignado a: {$act->asignadoA?->nombre}).",
                        'url'          => route('actividades.show', $act),
                        'creado_en'    => now(),
                    ]);
                    $creadas++;
                }
            }
        }

        return $creadas;
    }

    /**
     * Criterio 3: Alerta de cultivo próximo a cosechar.
     * Genera una alerta cuando el cultivo está cercano a su fecha estimada de cosecha (próximos 15 días).
     */
    public function evaluarCosechasProximas(): int
    {
        $limiteDias = 15;
        $hoy = Carbon::today();
        $hasta = Carbon::today()->addDays($limiteDias);

        $cultivosCercanos = Cultivo::with(['lote', 'variedad'])
            ->whereNotNull('activo_en_lote')
            ->whereIn('estado', ['sembrado', 'creciendo'])
            ->whereNotNull('fecha_cosecha_estimada')
            ->whereBetween('fecha_cosecha_estimada', [$hoy, $hasta])
            ->get();

        $creadas = 0;
        $admins = Usuario::where('id_rol', 1)->where('activo', true)->get();

        foreach ($cultivosCercanos as $cultivo) {
            $diasRestantes = (int)$hoy->diffInDays($cultivo->fecha_cosecha_estimada, false);
            $tiempoAviso = $diasRestantes <= 0 ? 'hoy' : "en {$diasRestantes} días ({$cultivo->fecha_cosecha_estimada->format('d/m/Y')})";

            foreach ($admins as $admin) {
                // Evitar notificaciones duplicadas en las últimas 48 horas para el mismo cultivo
                $existe = Notificacion::where('id_usuario', $admin->id)
                    ->where('id_cultivo', $cultivo->id)
                    ->where('tipo', 'cosecha')
                    ->where('creado_en', '>=', now()->subHours(48))
                    ->exists();

                if (! $existe) {
                    Notificacion::create([
                        'id_usuario' => $admin->id,
                        'id_cultivo' => $cultivo->id,
                        'tipo'       => 'cosecha',
                        'prioridad'  => 'alta',
                        'titulo'     => 'Alerta de cosecha próxima',
                        'mensaje'    => "El cultivo {$cultivo->codigo} ({$cultivo->variedad?->nombre}) en Lote {$cultivo->lote?->nombre} está próximo a cosechar {$tiempoAviso}.",
                        'url'        => route('cultivos.show', $cultivo),
                        'creado_en'  => now(),
                    ]);
                    $creadas++;
                }
            }
        }

        return $creadas;
    }

    /**
     * Criterio 4: Alerta automática de fertilización vencida.
     * Cuando un cultivo activo supera el ciclo estándar de fertilización (30 días)
     * sin registro de fertilización reciente ni programada, genera una alerta para programarla.
     */
    public function evaluarFertilizacionesVencidas(): int
    {
        $cultivosActivos = Cultivo::with(['lote', 'variedad'])
            ->whereNotNull('activo_en_lote')
            ->whereIn('estado', ['sembrado', 'creciendo'])
            ->get();

        $tipoFertilizacion = TipoActividad::where('codigo', 'FERTILIZACION')->first();
        if (! $tipoFertilizacion) {
            return 0;
        }

        $frecuenciaDias = $tipoFertilizacion->frecuencia_dias_estandar ?: 30;
        $fechaLimite = Carbon::today()->subDays($frecuenciaDias);

        $creadas = 0;
        $admins = Usuario::where('id_rol', 1)->where('activo', true)->get();

        foreach ($cultivosActivos as $cultivo) {
            // Última fertilización completada
            $ultimaFertilizacion = Actividad::where('id_cultivo', $cultivo->id)
                ->where('id_tipo_actividad', $tipoFertilizacion->id)
                ->where('estado', 'completada')
                ->latest('fecha_ejecucion')
                ->first();

            // Si existe fertilización programada pendiente a futuro, no está vencida
            $tieneProgramadaFutura = Actividad::where('id_cultivo', $cultivo->id)
                ->where('id_tipo_actividad', $tipoFertilizacion->id)
                ->where('estado', 'pendiente')
                ->where('fecha_programada', '>=', Carbon::today())
                ->exists();

            if ($tieneProgramadaFutura) {
                continue;
            }

            $estaVencida = false;
            if ($ultimaFertilizacion) {
                $fechaRef = Carbon::parse($ultimaFertilizacion->fecha_ejecucion ?? $ultimaFertilizacion->fecha_programada);
                if ($fechaRef->lt($fechaLimite)) {
                    $estaVencida = true;
                }
            } else {
                // No tiene ninguna fertilización registrada: verificar desde fecha de siembra
                if ($cultivo->fecha_siembra && Carbon::parse($cultivo->fecha_siembra)->lt($fechaLimite)) {
                    $estaVencida = true;
                }
            }

            if ($estaVencida) {
                foreach ($admins as $admin) {
                    $existeAlertaReciente = Notificacion::where('id_usuario', $admin->id)
                        ->where('id_cultivo', $cultivo->id)
                        ->where('tipo', 'alerta')
                        ->where('titulo', 'like', '%Fertilización vencida%')
                        ->where('creado_en', '>=', now()->subDays(5))
                        ->exists();

                    if (! $existeAlertaReciente) {
                        Notificacion::create([
                            'id_usuario' => $admin->id,
                            'id_cultivo' => $cultivo->id,
                            'tipo'       => 'fertilizacion',
                            'prioridad'  => 'alta',
                            'titulo'     => 'Alerta: Fertilización vencida',
                            'mensaje'    => "El cultivo {$cultivo->codigo} (Lote {$cultivo->lote?->nombre}) no registra fertilización en más de {$frecuenciaDias} días. Es necesario programar una fertilización.",
                            'url'        => route('actividades.create', ['cultivo' => $cultivo->id]),
                            'creado_en'  => now(),
                        ]);
                        $creadas++;
                    }
                }
            }
        }

        return $creadas;
    }

    /**
     * Criterio 7: Cancelar recordatorios asociados al eliminar o cancelar una actividad.
     */
    public function cancelarRecordatoriosActividad(int $idActividad): int
    {
        return Notificacion::where('id_actividad', $idActividad)->delete();
    }
}
