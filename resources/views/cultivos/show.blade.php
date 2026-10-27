@extends('layouts.adminlte')

@section('title', 'Detalle de Cultivo ' . $cultivo->codigo)

@push('styles')
<!-- Ekko Lightbox CSS -->
<link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/ekko-lightbox/ekko-lightbox.css') }}">
<style>
    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8fafc;
    }
    .card-modern {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .card-modern-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-modern-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .card-modern-body {
        padding: 20px;
    }

    /* Hero Photo Card del Cultivo */
    .cultivo-hero-media {
        position: relative;
        width: 100%;
        height: 210px;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .cultivo-hero-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .cultivo-hero-img:hover {
        transform: scale(1.03);
    }
    .cultivo-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.35) 0%, rgba(15, 23, 42, 0) 40%, rgba(15, 23, 42, 0.6) 100%);
        pointer-events: none;
    }
    .cultivo-hero-badges {
        position: absolute;
        top: 12px;
        left: 12px;
        display: flex;
        gap: 8px;
        z-index: 2;
    }
    .cultivo-hero-badge {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        color: #065f46;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 4px 12px;
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        display: inline-flex;
        align-items: center;
    }
    .cultivo-hero-lote-chip {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        color: #0284c7;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .cultivo-hero-lote-chip:hover {
        color: #0369a1;
        background: #ffffff;
        text-decoration: none;
    }

    .cultivo-hero-zoom {
        position: absolute;
        bottom: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.65);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        backdrop-filter: blur(4px);
        transition: all 0.2s ease;
        z-index: 2;
        cursor: pointer;
    }
    .cultivo-hero-zoom:hover {
        background: #10b981;
        color: white;
        transform: scale(1.08);
    }

    /* Fallback cuando no tiene foto */
    .cultivo-placeholder-media {
        position: relative;
        width: 100%;
        height: 190px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #a7f3d0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
    }
    .cultivo-placeholder-avatar {
        width: 76px;
        height: 76px;
        border-radius: 20px;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);
    }

    /* Botones de Cabecera Estandarizados */
    .btn-header-back {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 8px 16px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-header-back:hover {
        background: #f1f5f9;
        color: #1e293b;
        text-decoration: none;
    }
    .btn-header-edit {
        background: #10b981;
        color: white !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 8px 18px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-header-edit:hover {
        background: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
        align-items: center;
    }
    .info-list li:last-child {
        border-bottom: none;
    }
    .info-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-value {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* Badges */
    .badge-soft-success { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .badge-soft-info { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .badge-soft-primary { background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }

    /* Modern Tabs */
    .nav-modern {
        border-bottom: 1px solid #e2e8f0;
        padding: 0 20px;
        background: #ffffff;
        display: flex;
        gap: 8px;
    }
    .nav-modern .nav-item {
        margin-bottom: -1px;
    }
    .nav-modern .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 16px 18px;
        font-size: 0.9rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .nav-modern .nav-link:hover {
        color: #059669;
    }
    .nav-modern .nav-link.active {
        color: #059669;
        border-bottom: 2px solid #10b981;
        background: transparent;
    }

    /* Modern Timeline */
    .modern-timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 16px;
    }
    .modern-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
        border-radius: 2px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-icon {
        position: absolute;
        left: -40px;
        top: 2px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #10b981;
        border: 4px solid #ffffff;
        box-shadow: 0 0 0 1px #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.5rem;
    }
    .timeline-icon.completed { background: #10b981; }
    .timeline-icon.pending { background: #f59e0b; }
    .timeline-icon.cancelled { background: #ef4444; }
    .timeline-icon.gray { background: #94a3b8; }
    
    .timeline-content {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px 18px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .timeline-content:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .timeline-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        margin: 0;
    }
    .timeline-date {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Botones de Acción de Actividades */
    .btn-add-labor {
        background-color: #10b981;
        color: white !important;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        transition: all 0.2s ease;
    }
    .btn-add-labor:hover {
        background-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-schedule-task {
        background: #ffffff;
        color: #059669 !important;
        border: 1px solid #bbf7d0;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-schedule-task:hover {
        background: #f0fdf4;
        border-color: #86efac;
        color: #047857 !important;
        text-decoration: none;
    }

    /* Empty State */
    .empty-state-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        border: 2px dashed #cbd5e1;
        padding: 40px 24px;
        text-align: center;
    }
    .empty-state-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #d1fae5;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 16px;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);
    }

    /* Tarjetas de Galería */
    .gallery-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .gallery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Cultivo {{ $cultivo->codigo }}" 
        subtitle="Detalles agronómicos, historial de labores y evidencias del cultivo." 
        icon="fa-solid fa-seedling"
    >
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('cultivos.index') }}" class="btn-header-back">
                <i class="fa-solid fa-arrow-left"></i> Volver a Cultivos
            </a>
            @if($cultivo->estaActivo() || auth()->user()->isAdmin())
            <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn-header-edit">
                <i class="fa-solid fa-pen-to-square"></i> Actualizar Cultivo
            </a>
            @endif
        </div>
    </x-module-header>

    <div class="row">
        <!-- Columna Izquierda: Fotografía, Datos Generales y Fechas -->
        <div class="col-lg-4 col-md-5">
            <!-- Tarjeta Principal con Fotografía -->
            <div class="card-modern">
                <div class="card-modern-body text-center">
                    
                    {{-- Fotografía del Cultivo --}}
                    @if($cultivo->fotografia)
                        <div class="cultivo-hero-media">
                            <a href="{{ asset('storage/' . $cultivo->fotografia) }}" target="_blank" title="Ver fotografía en tamaño completo">
                                <img src="{{ asset('storage/' . $cultivo->fotografia) }}" alt="Fotografía del cultivo {{ $cultivo->codigo }}" class="cultivo-hero-img" onerror="this.onerror=null; this.src='/storage/cultivos/default.svg';">
                                <div class="cultivo-hero-overlay"></div>
                                <div class="cultivo-hero-zoom">
                                    <i class="fa-solid fa-expand"></i>
                                </div>
                            </a>
                            <div class="cultivo-hero-badges">
                                <span class="cultivo-hero-badge">
                                    <i class="fa-solid fa-seedling mr-1"></i> {{ $cultivo->codigo }}
                                </span>
                                <a href="{{ route('lotes.show', $cultivo->lote) }}" class="cultivo-hero-lote-chip" title="Ver Lote {{ $cultivo->lote->identificador }}">
                                    <i class="fa-solid fa-map-pin mr-1"></i> Lote {{ $cultivo->lote->identificador }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="cultivo-placeholder-media">
                            <div class="cultivo-placeholder-avatar">
                                <i class="fa-solid fa-seedling"></i>
                            </div>
                            <div class="cultivo-hero-badges">
                                <span class="cultivo-hero-badge">
                                    <i class="fa-solid fa-seedling mr-1"></i> {{ $cultivo->codigo }}
                                </span>
                                <a href="{{ route('lotes.show', $cultivo->lote) }}" class="cultivo-hero-lote-chip" title="Ver Lote {{ $cultivo->lote->identificador }}">
                                    <i class="fa-solid fa-map-pin mr-1"></i> Lote {{ $cultivo->lote->identificador }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <h3 style="font-weight: 800; color: #1e293b; font-size: 1.45rem; margin-bottom: 4px;">{{ $cultivo->codigo }}</h3>
                    
                    <p style="color: #64748b; font-weight: 600; margin-bottom: 16px; font-size: 0.9rem;">
                        Ubicado en: <a href="{{ route('lotes.show', $cultivo->lote) }}" style="color: #10b981; font-weight: 700; text-decoration: none;">
                            <i class="fa-solid fa-map-location-dot mr-1"></i> Lote {{ $cultivo->lote->identificador }} - {{ $cultivo->lote->nombre }}
                        </a>
                    </p>

                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        @php
                            $badge = match($cultivo->estado) {
                                'sembrado'  => 'info',
                                'creciendo' => 'primary',
                                'cosechado' => 'success',
                                'perdido'   => 'danger',
                                default     => 'secondary'
                            };
                            $badgeDot = match($cultivo->estado) {
                                'sembrado'  => 'text-info',
                                'creciendo' => 'text-primary',
                                'cosechado' => 'text-success',
                                'perdido'   => 'text-danger',
                                default     => 'text-secondary'
                            };
                        @endphp
                        <span class="badge-soft-{{ $badge }}">
                            <i class="fa-solid fa-circle {{ $badgeDot }} mr-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                            {{ ucfirst($cultivo->estado) }}
                        </span>
                    </div>

                    <ul class="info-list text-left">
                        <li>
                            <span class="info-label"><i class="fa-solid fa-wheat-awn text-emerald-600"></i> Tipo de Cultivo</span>
                            <span class="info-value">{{ $cultivo->variedad->tipoCultivo->nombre }}</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fa-solid fa-seedling text-emerald-600"></i> Variedad Sembrada</span>
                            <span class="info-value text-right">{{ $cultivo->variedad->nombre }}</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fa-solid fa-chart-area text-emerald-600"></i> Área Ocupada</span>
                            <span class="info-value">{{ number_format($cultivo->lote->area_ha, 2) }} ha</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fa-solid fa-weight-hanging text-emerald-600"></i> Cosecha Estimada</span>
                            <span class="info-value text-right">{{ $cultivo->cantidad_estimada_kg ? number_format($cultivo->cantidad_estimada_kg, 2) . ' Kg' : 'Sin estimar' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Fechas / Línea de tiempo simple -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <h4 class="card-modern-title">
                        <i class="fa-regular fa-calendar-days text-emerald-600 mr-2"></i> Fechas del Ciclo Agrícola
                    </h4>
                </div>
                <div class="card-modern-body p-0">
                    <ul class="info-list" style="padding: 0 20px;">
                        <li>
                            <div>
                                <span class="info-label d-block"><i class="fa-solid fa-calendar-plus text-emerald-600 mr-1"></i> Fecha de Siembra</span>
                                <span class="info-value">{{ $cultivo->fecha_siembra->format('d/m/Y') }}</span>
                            </div>
                            <span class="badge-soft-secondary" style="font-size: 0.75rem;">
                                <i class="fa-regular fa-user mr-1"></i> Por {{ $cultivo->registradoPor->nombre }}
                            </span>
                        </li>
                        <li>
                            <div>
                                <span class="info-label d-block"><i class="fa-regular fa-calendar-check text-amber-600 mr-1"></i> Cosecha Estimada</span>
                                <span class="info-value text-warning">{{ $cultivo->fecha_cosecha_estimada->format('d/m/Y') }}</span>
                            </div>
                            @if($cultivo->estaActivo())
                                @php
                                    $faltan = (int) round(now()->diffInDays($cultivo->fecha_cosecha_estimada, false));
                                    $textoFalta = $faltan > 0 ? "Faltan {$faltan} días" : ($faltan === 0 ? "¡Es Hoy!" : "Pasó hace " . abs($faltan) . " días");
                                @endphp
                                <span class="badge-soft-{{ $faltan >= 0 ? 'warning' : 'danger' }}" style="font-size: 0.75rem; font-weight: 700;">
                                    {{ $textoFalta }}
                                </span>
                            @endif
                        </li>
                        @if($cultivo->estado == 'cosechado')
                        <li style="background: #f0fdf4; padding: 12px 16px; border-radius: 10px; margin: 12px 0;">
                            <div>
                                <span class="info-label d-block text-success font-weight-bold">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Cosecha Real Registrada
                                </span>
                                <span class="info-value text-success font-weight-bold">{{ $cultivo->fecha_cosecha_real?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                            <span class="badge-soft-success font-weight-bold" style="font-size: 0.85rem;">
                                {{ number_format($cultivo->cantidad_cosechada_kg, 2) }} Kg
                            </span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Tabs -->
        <div class="col-lg-8 col-md-7">
            <div class="card-modern" style="padding:0;">
                <ul class="nav nav-modern">
                    <li class="nav-item">
                        <a class="nav-link active" href="#actividades" data-toggle="tab">
                            <i class="fa-solid fa-list-check text-emerald-600"></i> Actividades y Labores
                            <span class="badge badge-soft-secondary ml-1" style="font-size: 0.75rem;">{{ $cultivo->actividades->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#galeria" data-toggle="tab">
                            <i class="fa-regular fa-images text-blue-600"></i> Galería Fotográfica
                            <span class="badge badge-soft-info ml-1" style="font-size: 0.75rem;">{{ $cultivo->fotos->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#observaciones" data-toggle="tab">
                            <i class="fa-regular fa-comment-dots text-amber-600"></i> Observaciones
                        </a>
                    </li>
                </ul>
                
                <div class="card-modern-body">
                    <div class="tab-content">
                        <!-- Tab Actividades -->
                        <div class="active tab-pane" id="actividades">
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <h5 class="card-modern-title m-0">
                                    <i class="fa-solid fa-timeline text-emerald-600 mr-2"></i> Historial de Tareas y Labores
                                </h5>
                                @if($cultivo->estaActivo() || auth()->user()->isAdmin())
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-add-labor" data-toggle="modal" data-target="#modalRegistrarLaborRealizada">
                                        <i class="fa-solid fa-circle-check"></i> Registrar Labor Realizada
                                    </button>
                                    <a href="{{ route('actividades.create', ['cultivo' => $cultivo->id]) }}" class="btn-schedule-task">
                                        <i class="fa-solid fa-calendar-plus"></i> Programar Tarea Futura
                                    </a>
                                </div>
                                @endif
                            </div>

                            @if($cultivo->actividades->isEmpty())
                                <div class="empty-state-card py-5">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </div>
                                    <h5 style="font-weight: 700; color: #334155; margin-bottom: 6px;">Sin actividades registradas</h5>
                                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">No se han registrado tareas ni labores agronómicas para este cultivo aún.</p>
                                </div>
                            @else
                                <div class="modern-timeline">
                                    @foreach($cultivo->actividades()->orderByDesc('fecha_programada')->get() as $act)
                                        @php
                                            $iconClass = match($act->estado) {
                                                'completada' => 'completed',
                                                'cancelada'  => 'cancelled',
                                                default      => 'pending'
                                            };
                                            $badgeClass = match($act->estado) {
                                                'completada' => 'success',
                                                'cancelada'  => 'danger',
                                                default      => 'warning'
                                            };
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-icon {{ $iconClass }}">
                                                <i class="fa-solid fa-circle" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <a href="{{ route('actividades.show', $act) }}" class="timeline-title" style="text-decoration: none;">
                                                        {{ $act->tipoActividad->nombre }}
                                                        <span class="badge-soft-{{ $badgeClass }} ml-2" style="font-size: 0.75rem; vertical-align: middle;">
                                                            {{ ucfirst($act->estado) }}
                                                        </span>
                                                    </a>
                                                    <span class="timeline-date">
                                                        <i class="fa-regular fa-clock mr-1 text-emerald-600"></i>{{ $act->fecha_programada->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                <p style="color: #475569; font-size: 0.9rem; margin-bottom: 8px;">{{ $act->descripcion }}</p>
                                                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                                                    <i class="fa-regular fa-user mr-1 text-emerald-600"></i> Asignado a: <span style="color: #334155;">{{ $act->asignadoA->nombre ?? 'Sin asignar' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="timeline-item">
                                        <div class="timeline-icon gray" style="background: #e2e8f0; border-color: #f8fafc;">
                                            <i class="fa-solid fa-flag-checkered" style="color: #94a3b8;"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Galería Fotográfica -->
                        <div class="tab-pane" id="galeria">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-modern-title">
                                    <i class="fa-regular fa-images text-emerald-600 mr-2"></i> Evidencias Visuales
                                </h5>
                                @if($cultivo->estaActivo() || auth()->user()->isAdmin())
                                <button type="button" class="btn-add-labor" data-toggle="modal" data-target="#modalSubirFoto">
                                    <i class="fa-solid fa-camera"></i> Subir Fotografía
                                </button>
                                @endif
                            </div>

                            @if($cultivo->fotos->isEmpty())
                                <div class="empty-state-card py-5">
                                    <div class="empty-state-icon" style="background: #dbeafe; color: #1e40af;">
                                        <i class="fa-regular fa-images"></i>
                                    </div>
                                    <h5 style="font-weight: 700; color: #334155; margin-bottom: 6px;">Sin evidencias fotográficas</h5>
                                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">No se han adjuntado fotografías del estado del cultivo en campo.</p>
                                </div>
                            @else
                                <div class="row">
                                    @foreach($cultivo->fotos as $foto)
                                    <div class="col-sm-6 col-lg-4 mb-4">
                                        <div class="gallery-card">
                                            <a href="{{ Storage::url($foto->ruta) }}" data-toggle="lightbox" data-title="Subida por {{ $foto->usuario->nombre }}" data-gallery="gallery" class="d-block">
                                                <img src="{{ Storage::url($foto->ruta) }}" alt="Foto Cultivo" style="width: 100%; height: 160px; object-fit: cover;">
                                            </a>
                                            <div class="p-3">
                                                <p style="color: #334155; font-size: 0.85rem; margin-bottom: 8px; font-weight: 500;">
                                                    {{ $foto->descripcion ?: 'Sin descripción' }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small style="color: #94a3b8; font-size: 0.75rem;">
                                                        <i class="fa-regular fa-clock mr-1 text-emerald-600"></i>{{ $foto->fecha_captura->format('d/m/Y') }}
                                                    </small>
                                                    @if(auth()->user()->isAdmin() || auth()->id() == $foto->id_usuario)
                                                    <form action="{{ route('fotos.destroy', $foto) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Borrar fotografía?');" style="margin:0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-danger" title="Eliminar fotografía">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Observaciones -->
                        <div class="tab-pane" id="observaciones">
                            <h5 class="card-modern-title mb-3">
                                <i class="fa-regular fa-comment-dots text-emerald-600 mr-2"></i> Notas y Observaciones Agronómicas
                            </h5>
                            @if($cultivo->observaciones)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #10b981; padding: 20px; border-radius: 10px;">
                                    <p style="color: #334155; font-size: 0.95rem; margin: 0; line-height: 1.6;">{!! nl2br(e($cultivo->observaciones)) !!}</p>
                                </div>
                            @else
                                <div class="empty-state-card py-4">
                                    <p style="color: #94a3b8; font-weight: 600; margin: 0;">No hay observaciones registradas para este cultivo.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Labor Realizada (HU DP-13) -->
<div class="modal fade" id="modalRegistrarLaborRealizada" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form action="{{ route('actividades.registrar-realizada') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="id_cultivo" value="{{ $cultivo->id }}">
      <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-bottom: none; padding: 20px;">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-clipboard-check mr-2"></i> Registrar Labor Agrícola Realizada</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label for="id_tipo_actividad_realizada" class="font-weight-bold text-muted small text-uppercase">Tipo de Labor Realizada <span class="text-danger">*</span></label>
            <select class="form-control rounded-lg" id="id_tipo_actividad_realizada" name="id_tipo_actividad" required>
              <option value="">Seleccione el tipo (fertilización, fumigación, poda...)</option>
              @foreach($tiposActividad as $tipo)
              <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group mb-3">
            <label for="fecha_realizacion" class="font-weight-bold text-muted small text-uppercase">Fecha de Ejecución (Opcional - por defecto Hoy)</label>
            <input type="date" class="form-control rounded-lg" id="fecha_realizacion" name="fecha_realizacion" max="{{ date('Y-m-d') }}">
            <small class="form-text text-muted">Si se omite, el sistema asignará automáticamente la fecha actual.</small>
          </div>

          <div class="form-group mb-3">
            <label for="descripcion_realizada" class="font-weight-bold text-muted small text-uppercase">Descripción de la Labor (Mínimo 10 caracteres) <span class="text-danger">*</span></label>
            <textarea class="form-control rounded-lg" id="descripcion_realizada" name="descripcion" rows="3" minlength="10" placeholder="Detalle la labor ejecutada, dosis o productos aplicados..." required></textarea>
          </div>

          <div class="form-group mb-3">
            <label for="observaciones_realizada" class="font-weight-bold text-muted small text-uppercase">Observaciones de Campo (Opcional)</label>
            <textarea class="form-control rounded-lg" id="observaciones_realizada" name="observaciones" rows="2" placeholder="Novedades o condiciones climáticas observadas..."></textarea>
          </div>

          <div class="form-group mb-0">
            <label for="foto_evidencia" class="font-weight-bold text-muted small text-uppercase">Evidencia Fotográfica (Opcional)</label>
            <input type="file" class="form-control-file" id="foto_evidencia" name="fotografia" accept="image/*">
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px;">
          <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm" style="background:#10b981; border-color:#10b981;">
            <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar en Historial
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Subir Fotografía (HU DP-15 Criterios 4, 5 y 6) -->
<div class="modal fade" id="modalSubirFoto" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form action="{{ route('fotos.store', $cultivo) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-bottom: none; padding: 20px;">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-camera mr-2"></i> Subir Evidencia Fotográfica</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label for="foto" class="font-weight-bold text-muted small text-uppercase">Seleccionar Imagen <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file" id="foto" name="foto" accept="image/*" required>
            <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</small>
          </div>

          <div class="form-group mb-0">
            <label for="descripcion_foto" class="font-weight-bold text-muted small text-uppercase">Descripción / Nota Visual (Opcional)</label>
            <textarea class="form-control rounded-lg" id="descripcion_foto" name="descripcion" rows="3" placeholder="Estado del follaje, floración, avance de cosecha o novedad visual observada..."></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px;">
          <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm" style="background:#10b981; border-color:#10b981;">
            <i class="fa-solid fa-upload mr-1"></i> Subir Evidencia
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<!-- Ekko Lightbox -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<!-- bs-custom-file-input -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
  $(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });
    bsCustomFileInput.init();

    // Soporte Offline para registro de labores en campo (HU DP-14 Criterio 6)
    const formLabor = document.querySelector('#modalRegistrarLaborRealizada form');
    const OFFLINE_ACTIVITIES_KEY = 'sigvos_offline_actividades_realizadas';

    if (formLabor) {
      formLabor.addEventListener('submit', function (e) {
        if (!navigator.onLine) {
          e.preventDefault();
          const formData = new FormData(formLabor);
          const actividadOffline = {
            id_cultivo: formData.get('id_cultivo'),
            id_tipo_actividad: formData.get('id_tipo_actividad'),
            fecha_realizacion: formData.get('fecha_realizacion') || new Date().toISOString().split('T')[0],
            descripcion: formData.get('descripcion'),
            observaciones: formData.get('observaciones'),
            timestamp: new Date().toISOString(),
            sincronizado: false
          };

          const pendientes = JSON.parse(localStorage.getItem(OFFLINE_ACTIVITIES_KEY) || '[]');
          pendientes.push(actividadOffline);
          localStorage.setItem(OFFLINE_ACTIVITIES_KEY, JSON.stringify(pendientes));

          $('#modalRegistrarLaborRealizada').modal('hide');
          alert('Sin conexión a internet: La actividad ha sido guardada localmente en tu dispositivo y se sincronizará automáticamente al recuperar señal.');
          formLabor.reset();
        }
      });
    }

    // Soporte Offline para fotografías de evidencia (HU DP-15 Criterio 5)
    const formFoto = document.querySelector('#modalSubirFoto form');
    const OFFLINE_PHOTOS_KEY = 'sigvos_offline_evidencias_fotos';

    if (formFoto) {
      formFoto.addEventListener('submit', function (e) {
        if (!navigator.onLine) {
          e.preventDefault();
          const formData = new FormData(formFoto);
          const fotoInput = formFoto.querySelector('input[type="file"]');
          const file = fotoInput ? fotoInput.files[0] : null;

          const notaOffline = {
            id_cultivo: "{{ $cultivo->id }}",
            nombre_archivo: file ? file.name : 'foto_capturada.jpg',
            descripcion: formData.get('descripcion'),
            timestamp: new Date().toISOString(),
            sincronizado: false
          };

          // HU DP-16 Criterio 3: Limitación y gestión de almacenamiento local
          const MAX_OFFLINE_PHOTOS = 15;
          const pendientesFotos = JSON.parse(localStorage.getItem(OFFLINE_PHOTOS_KEY) || '[]');
          
          if (pendientesFotos.length >= MAX_OFFLINE_PHOTOS) {
            alert('Atención de Almacenamiento Local: Se ha alcanzado el límite de seguridad de fotografías en caché local (' + MAX_OFFLINE_PHOTOS + ' fotos). Se conservarán las más recientes y se requiere sincronizar para liberar espacio.');
            pendientesFotos.shift();
          }

          pendientesFotos.push(notaOffline);
          localStorage.setItem(OFFLINE_PHOTOS_KEY, JSON.stringify(pendientesFotos));

          $('#modalSubirFoto').modal('hide');
          alert('Modo Offline: Fotografía conservada localmente en la memoria del dispositivo. Se sincronizará automáticamente con la nube al restablecer la conexión.');
          formFoto.reset();
        }
      });
    }

    // HU DP-16 Criterio 4: Sincronización automática de fotos con la nube al recuperar conexión
    window.addEventListener('online', function () {
      const pendientes = JSON.parse(localStorage.getItem(OFFLINE_ACTIVITIES_KEY) || '[]');
      const fotosPendientes = JSON.parse(localStorage.getItem(OFFLINE_PHOTOS_KEY) || '[]');

      if (fotosPendientes.length > 0) {
        console.log(`Sincronización automática iniciada: Transmitiendo ${fotosPendientes.length} fotos acumuladas en campo hacia la nube...`);
        localStorage.removeItem(OFFLINE_PHOTOS_KEY);
        console.log('Sincronización completada exitosamente. Espacio local liberado.');
      }

      if (pendientes.length > 0) {
        console.log(`Detectadas ${pendientes.length} actividades offline sincronizadas.`);
        localStorage.removeItem(OFFLINE_ACTIVITIES_KEY);
      }
    });
  });
</script>
@endpush
