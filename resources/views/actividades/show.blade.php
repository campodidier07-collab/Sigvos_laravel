@extends('layouts.adminlte')

@section('title', 'Detalle de Actividad #' . $actividad->id)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }
    .font-heading {
        font-family: 'Outfit', sans-serif;
    }

    /* Tarjetas Modernas Sigvos */
    .card-sigvos {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-sigvos-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-sigvos-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-sigvos-body {
        padding: 24px;
    }

    /* Icono Hero Actividad */
    .actividad-icon-hero {
        width: 82px;
        height: 82px;
        border-radius: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.3rem;
        color: white;
        margin-bottom: 16px;
        transition: transform 0.3s ease;
    }
    .actividad-icon-hero:hover {
        transform: scale(1.05) rotate(-3deg);
    }

    /* Paletas temáticas por Tipo de Actividad */
    .theme-riego {
        background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.28);
    }
    .theme-fertilizacion {
        background: linear-gradient(135deg, #059669 0%, #34d399 100%);
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.28);
    }
    .theme-poda {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.28);
    }
    .theme-fumigacion {
        background: linear-gradient(135deg, #e11d48 0%, #fb7185 100%);
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.28);
    }
    .theme-cosecha {
        background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.28);
    }
    .theme-default {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.28);
    }

    /* Badges de Estado Estandarizados */
    .badge-sigvos-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: capitalize;
    }
    .status-pendiente {
        background-color: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .status-completada {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .status-cancelada {
        background-color: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* Lista de información de resumen */
    .info-list-sigvos {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }
    .info-list-sigvos li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .info-list-sigvos li:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }

    /* Botones Estandarizados SIGVOS */
    .btn-sigvos-primary {
        background-color: #10b981;
        color: #ffffff !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 12px 20px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-sigvos-primary:hover {
        background-color: #059669;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.38);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .btn-sigvos-outline {
        background-color: #ffffff;
        color: #475569 !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 9px 18px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-sigvos-outline:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b !important;
        transform: translateY(-1px);
        text-decoration: none;
    }

    /* Banner de Cultivo Objetivo */
    .target-cultivo-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .target-cultivo-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
    .badge-chip-green {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        color: #065f46;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.825rem;
        font-weight: 700;
        border: 1px solid #a7f3d0;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .badge-chip-green:hover {
        background: #ecfdf5;
        color: #047857;
        text-decoration: none;
    }

    .badge-chip-blue {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        color: #0369a1;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.825rem;
        font-weight: 700;
        border: 1px solid #bae6fd;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .badge-chip-blue:hover {
        background: #f0f9ff;
        color: #0284c7;
        text-decoration: none;
    }

    /* Caja de Contenido / Descripción */
    .content-box-sigvos {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        color: #334155;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    /* Sección de Reporte de Ejecución */
    .reporte-completado-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border: 1px solid #a7f3d0;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 16px;
    }
    .reporte-pendiente-box {
        background-color: #fffbeb;
        border: 1px dashed #fcd34d;
        border-radius: 16px;
        padding: 32px 20px;
        text-align: center;
        color: #92400e;
    }
    .reporte-cancelado-box {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 16px;
        padding: 22px;
        color: #991b1b;
    }

    /* Avatar del usuario asignado */
    .user-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    .user-avatar-mini {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
    }

    /* Foto de Evidencia */
    .evidencia-card {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        margin-top: 16px;
        max-width: 320px;
    }
    .evidencia-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }
    .evidencia-img:hover {
        transform: scale(1.03);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Barra de Navegación y Acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 12px;">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('actividades.index') }}" class="btn-sigvos-outline">
                <i class="fa-solid fa-arrow-left"></i> Volver a Actividades
            </a>
            <h1 class="font-heading font-extrabold m-0 text-slate-800" style="font-size: 1.5rem; color: #0f172a;">
                Actividad <span style="color: #10b981;">#{{ $actividad->id }}</span>
            </h1>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if(auth()->user()->isAdmin() || auth()->id() == $actividad->asignado_a)
                <a href="{{ route('actividades.edit', $actividad) }}" class="btn-sigvos-outline" style="background: #10b981; color: white !important; border-color: #10b981;">
                    <i class="fa-solid fa-pen-to-square"></i> Actualizar / Reportar
                </a>
            @endif
        </div>
    </div>

    @php
        $codigoTipo = strtoupper($actividad->tipoActividad->codigo ?? '');
        $themeClass = match($codigoTipo) {
            'RIEGO' => 'theme-riego',
            'FERTILIZACION' => 'theme-fertilizacion',
            'PODA' => 'theme-poda',
            'FUMIGACION' => 'theme-fumigacion',
            'COSECHA' => 'theme-cosecha',
            default => 'theme-default'
        };
        $icono = match($codigoTipo) {
            'RIEGO' => 'fa-droplet',
            'FERTILIZACION' => 'fa-flask',
            'PODA' => 'fa-scissors',
            'FUMIGACION' => 'fa-spray-can-sparkles',
            'COSECHA' => 'fa-wheat-awn',
            default => 'fa-list-check'
        };
    @endphp

    <div class="row">
        <!-- Columna Izquierda: Tarjeta de Resumen -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card-sigvos">
                <div class="card-sigvos-body text-center pt-4">
                    
                    <!-- Icono Principal Estilizado -->
                    <div class="actividad-icon-hero {{ $themeClass }}">
                        <i class="fa-solid {{ $icono }}"></i>
                    </div>

                    <h3 class="font-heading font-bold mb-1" style="color: #0f172a; font-size: 1.35rem;">
                        {{ $actividad->tipoActividad->nombre }}
                    </h3>
                    <p class="text-muted text-sm mb-3">
                        {{ $actividad->tipoActividad->descripcion ?? 'Labor agrícola programada' }}
                    </p>

                    <!-- Lista de Atributos Clave -->
                    <ul class="info-list-sigvos text-left">
                        <li>
                            <span class="info-label">Estado</span>
                            <span class="badge-sigvos-status status-{{ $actividad->estado }}">
                                @if($actividad->estado === 'completada')
                                    <i class="fa-solid fa-circle-check"></i>
                                @elseif($actividad->estado === 'cancelada')
                                    <i class="fa-solid fa-circle-xmark"></i>
                                @else
                                    <i class="fa-regular fa-clock"></i>
                                @endif
                                {{ ucfirst($actividad->estado) }}
                            </span>
                        </li>
                        <li>
                            <span class="info-label">Fecha Programada</span>
                            <span class="info-value {{ $actividad->fecha_programada->isPast() && $actividad->estado === 'pendiente' ? 'text-danger font-bold' : '' }}">
                                <i class="fa-regular fa-calendar mr-1 text-muted"></i>
                                {{ $actividad->fecha_programada->format('d/m/Y') }}
                                @if($actividad->fecha_programada->isPast() && $actividad->estado === 'pendiente')
                                    <span class="badge badge-danger ml-1" style="font-size: 0.65rem;">Vencida</span>
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="info-label">Asignado a</span>
                            <div class="user-chip">
                                <span class="user-avatar-mini">
                                    {{ strtoupper(substr($actividad->asignadoA->nombre ?? 'N', 0, 1)) }}
                                </span>
                                <span>{{ $actividad->asignadoA->nombre ?? 'Sin asignar' }}</span>
                            </div>
                        </li>
                        @if($actividad->grupo_recurrencia)
                        <li>
                            <span class="info-label">Recurrencia</span>
                            <span class="badge-chip-green" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-repeat"></i> Periódica
                            </span>
                        </li>
                        @endif
                    </ul>

                    @if(auth()->user()->isAdmin() || auth()->id() == $actividad->asignado_a)
                    <div class="mt-4">
                        <a href="{{ route('actividades.edit', $actividad) }}" class="btn-sigvos-primary">
                            <i class="fa-solid fa-pen-to-square"></i> Actualizar / Reportar
                        </a>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- Columna Derecha: Información Detallada y Reporte -->
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card-sigvos">
                <div class="card-sigvos-header">
                    <h3 class="card-sigvos-title">
                        <i class="fa-solid fa-clipboard-list" style="color: #10b981;"></i>
                        Información Detallada de la Labor
                    </h3>
                    <span class="text-muted text-xs">
                        ID: #{{ $actividad->id }}
                    </span>
                </div>

                <div class="card-sigvos-body">
                    
                    <!-- Tarjeta de Cultivo y Lote Objetivo -->
                    <div class="target-cultivo-card">
                        <div class="target-cultivo-icon">
                            <i class="fa-solid fa-seedling"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-uppercase font-bold text-xs" style="color: #065f46; letter-spacing: 0.05em; margin-bottom: 4px;">
                                Cultivo y Lote Objetivo
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('cultivos.show', $actividad->cultivo) }}" class="badge-chip-green" title="Ver detalles del cultivo">
                                    <i class="fa-solid fa-leaf"></i> {{ $actividad->cultivo->codigo }}
                                </a>
                                @if($actividad->cultivo->lote)
                                <a href="{{ route('lotes.show', $actividad->cultivo->lote) }}" class="badge-chip-blue" title="Ver detalles del lote">
                                    <i class="fa-solid fa-map-location-dot"></i> Lote {{ $actividad->cultivo->lote->identificador }} - {{ $actividad->cultivo->lote->nombre }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Descripción de la Tarea -->
                    <div class="mb-4">
                        <h4 class="font-heading font-bold mb-2" style="font-size: 1rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-align-left" style="color: #10b981;"></i> Descripción de la Tarea
                        </h4>
                        <div class="content-box-sigvos">
                            {{ $actividad->descripcion ?: 'No se especificaron detalles adicionales para esta tarea.' }}
                        </div>
                    </div>

                    <!-- Reporte de Ejecución -->
                    <div class="mb-4">
                        <h4 class="font-heading font-bold mb-2" style="font-size: 1rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-clipboard-check" style="color: #10b981;"></i> Reporte de Ejecución
                        </h4>

                        @if($actividad->estado === 'completada')
                            <div class="reporte-completado-box">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="info-label mb-1">Fecha Real de Ejecución</div>
                                        <div class="font-bold text-success" style="font-size: 1rem;">
                                            <i class="fa-solid fa-calendar-check mr-1"></i>
                                            {{ $actividad->fecha_ejecucion ? $actividad->fecha_ejecucion->format('d/m/Y') : 'Fecha no registrada' }}
                                        </div>

                                        <div class="info-label mt-3 mb-1">Ejecutado por</div>
                                        <div class="user-chip">
                                            <span class="user-avatar-mini" style="background: #059669;">
                                                {{ strtoupper(substr($actividad->ejecutadoPor->nombre ?? 'D', 0, 1)) }}
                                            </span>
                                            <span>{{ $actividad->ejecutadoPor->nombre ?? 'Usuario no especificado' }}</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-label mb-1">Observaciones de Campo</div>
                                        <div class="text-muted" style="font-size: 0.9rem; line-height: 1.5; font-style: italic;">
                                            "{{ $actividad->observaciones ?: 'Sin observaciones registradas durante la labor.' }}"
                                        </div>
                                    </div>
                                </div>

                                @if($actividad->fotografia)
                                <div class="mt-4 pt-3 border-top">
                                    <div class="info-label mb-2"><i class="fa-solid fa-camera mr-1"></i> Evidencia Fotográfica de la Labor</div>
                                    <div class="evidencia-card">
                                        <a href="{{ asset('storage/' . $actividad->fotografia) }}" target="_blank" title="Ver fotografía en tamaño completo">
                                            <img src="{{ asset('storage/' . $actividad->fotografia) }}" alt="Evidencia de actividad" class="evidencia-img" onerror="this.onerror=null; this.src='/storage/actividades/default.svg';">
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>

                        @elseif($actividad->estado === 'cancelada')
                            <div class="reporte-cancelado-box">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.5rem; color: #e11d48;"></i>
                                    <div>
                                        <h5 class="font-heading font-bold m-0" style="color: #991b1b;">Actividad Cancelada</h5>
                                        <div class="text-sm">Esta labor fue suspendida o cancelada.</div>
                                    </div>
                                </div>
                                @if($actividad->observaciones)
                                <div class="mt-2 text-sm pt-2 border-top" style="border-color: #fecaca !important;">
                                    <strong>Motivo / Justificación:</strong> {{ $actividad->observaciones }}
                                </div>
                                @endif
                            </div>

                        @else
                            <div class="reporte-pendiente-box">
                                <i class="fa-regular fa-clock fa-2x mb-2" style="color: #d97706;"></i>
                                <h5 class="font-heading font-bold mb-1" style="color: #92400e;">Actividad Pendiente de Ejecución</h5>
                                <p class="mb-0 text-sm" style="color: #b45309;">
                                    El trabajador asignado debe completar la labor y registrar la fecha de cumplimiento.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Pie de Auditoría -->
                    <div class="d-flex justify-content-between align-items-center text-muted text-xs pt-3 border-top mt-4 flex-wrap" style="gap: 8px;">
                        <span>
                            <i class="fa-solid fa-user-pen mr-1"></i> Programada por:
                            <strong>{{ $actividad->creadoPor->nombre ?? 'Administrador' }}</strong>
                        </span>
                        <span>
                            <i class="fa-regular fa-clock mr-1"></i> Registrada el:
                            <strong>{{ $actividad->created_at ? $actividad->created_at->format('d/m/Y H:i') : 'N/A' }}</strong>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
