@extends('layouts.adminlte')

@section('title', 'Detalle de Lote ' . $lote->identificador)

@push('styles')
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

    /* Hero Photo Card del Lote */
    .lote-hero-media {
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
    .lote-hero-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .lote-hero-img:hover {
        transform: scale(1.03);
    }
    .lote-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.35) 0%, rgba(15, 23, 42, 0) 40%, rgba(15, 23, 42, 0.6) 100%);
        pointer-events: none;
    }
    .lote-hero-badges {
        position: absolute;
        top: 12px;
        left: 12px;
        display: flex;
        gap: 8px;
        z-index: 2;
    }
    .lote-hero-badge {
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
    .lote-hero-type {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    .type-alt { color: #0284c7; }
    .type-main { color: #059669; }

    .lote-hero-zoom {
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
    .lote-hero-zoom:hover {
        background: #10b981;
        color: white;
        transform: scale(1.08);
    }

    /* Placeholder cuando no tiene fotografía */
    .lote-placeholder-media {
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
    .lote-placeholder-avatar {
        width: 76px;
        height: 76px;
        border-radius: 20px;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 800;
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

    .lote-location-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        margin-top: 16px;
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

    /* Cultivo Actual Highlight Card */
    .cultivo-highlight-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        padding: 24px;
        position: relative;
    }

    /* Empty State Modern */
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
    .btn-add-cultivo {
        background-color: #10b981;
        color: white !important;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-add-cultivo:hover {
        background-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.35);
        text-decoration: none;
    }

    /* Table Modern */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-modern th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .table-modern td {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .table-modern tr:last-child td {
        border-bottom: none;
    }

    /* Progress Bar Modern */
    .progress-modern {
        background-color: #e2e8f0;
        border-radius: 9999px;
        height: 10px;
        overflow: hidden;
        margin: 8px 0;
    }
    .progress-modern-bar {
        background: linear-gradient(90deg, #34d399 0%, #059669 100%);
        height: 100%;
        border-radius: 9999px;
        transition: width 0.5s ease-in-out;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Lote {{ $lote->identificador }} - {{ $lote->nombre }}" 
        subtitle="Información general, historial y cultivos activos del lote." 
        icon="fa-solid fa-map-location-dot"
    >
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('lotes.index') }}" class="btn-header-back">
                <i class="fa-solid fa-arrow-left"></i> Volver a Lotes
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('lotes.edit', $lote) }}" class="btn-header-edit">
                <i class="fa-solid fa-pen-to-square"></i> Editar Lote
            </a>
            @endif
        </div>
    </x-module-header>

    <div class="row">
        <!-- Columna Izquierda: Fotografía, Info General y Trabajadores -->
        <div class="col-lg-4 col-md-5">
            
            <!-- Tarjeta de Info y Foto del Lote -->
            <div class="card-modern">
                <div class="card-modern-body text-center">
                    
                    {{-- Fotografía del Lote --}}
                    @if($lote->fotografia)
                        <div class="lote-hero-media">
                            <a href="{{ asset('storage/' . $lote->fotografia) }}" target="_blank" title="Ver fotografía en tamaño completo">
                                <img src="{{ asset('storage/' . $lote->fotografia) }}" alt="Fotografía del {{ $lote->nombre }}" class="lote-hero-img">
                                <div class="lote-hero-overlay"></div>
                                <div class="lote-hero-zoom">
                                    <i class="fa-solid fa-expand"></i>
                                </div>
                            </a>
                            <div class="lote-hero-badges">
                                <span class="lote-hero-badge">
                                    <i class="fa-solid fa-map-pin mr-1"></i> Lote {{ $lote->identificador }}
                                </span>
                                @if($lote->es_alternativo)
                                    <span class="lote-hero-type type-alt">Alternativo</span>
                                @else
                                    <span class="lote-hero-type type-main">Principal</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="lote-placeholder-media">
                            <div class="lote-placeholder-avatar">
                                {{ $lote->identificador }}
                            </div>
                            <div class="lote-hero-badges">
                                <span class="lote-hero-badge">
                                    <i class="fa-solid fa-map-pin mr-1"></i> Lote {{ $lote->identificador }}
                                </span>
                                @if($lote->es_alternativo)
                                    <span class="lote-hero-type type-alt">Alternativo</span>
                                @else
                                    <span class="lote-hero-type type-main">Principal</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <h3 style="font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 6px;">{{ $lote->nombre }}</h3>
                    
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        @php
                            $estadoClass = match($lote->estado) {
                                'disponible'    => 'success',
                                'ocupado'       => 'warning',
                                'mantenimiento' => 'danger',
                                'en_descanso'   => 'info',
                                default         => 'secondary'
                            };
                            $estadoDotClass = match($lote->estado) {
                                'disponible'    => 'text-success',
                                'ocupado'       => 'text-warning',
                                'mantenimiento' => 'text-danger',
                                'en_descanso'   => 'text-info',
                                default         => 'text-secondary'
                            };
                        @endphp
                        <span class="badge-soft-{{ $estadoClass }}">
                            <i class="fa-solid fa-circle {{ $estadoDotClass }} mr-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                            {{ ucfirst(str_replace('_', ' ', $lote->estado)) }}
                        </span>
                        @if(!$lote->activo)
                            <span class="badge-soft-secondary">Inactivo</span>
                        @endif
                    </div>

                    <ul class="info-list text-left">
                        <li>
                            <span class="info-label"><i class="fa-solid fa-chart-area text-emerald-600"></i> Área Total</span>
                            <span class="info-value">{{ number_format($lote->area_ha, 2) }} ha</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fa-solid fa-seedling text-emerald-600"></i> Cultivo Preferido</span>
                            <span class="info-value text-right">{{ $lote->tipoPreferido->nombre ?? 'Sin preferencia' }}</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fa-solid fa-layer-group text-emerald-600"></i> Clasificación</span>
                            <span class="info-value">{{ $lote->es_alternativo ? 'Lote Alternativo' : 'Lote Principal' }}</span>
                        </li>
                    </ul>
                    
                    <div class="lote-location-box text-left">
                        <span class="info-label d-block mb-1 font-weight-bold" style="color: #334155;">
                            <i class="fa-solid fa-location-dot text-danger mr-1"></i> Ubicación Geográfica
                        </span>
                        <p style="color: #475569; margin: 0; font-size: 0.9rem; font-weight: 500;">{{ $lote->ubicacion }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Trabajadores Asignados -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <h4 class="card-modern-title">
                        <i class="fa-solid fa-users text-emerald-600 mr-2"></i> Trabajadores Asignados
                    </h4>
                    <span class="badge badge-soft-info" style="font-size: 0.75rem;">
                        {{ $lote->trabajadores->count() }}
                    </span>
                </div>
                <div class="card-modern-body p-0">
                    @if($lote->trabajadores->isEmpty())
                        <div class="text-center py-4 px-3">
                            <i class="fa-solid fa-user-slash fa-2x mb-2" style="color: #cbd5e1;"></i>
                            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">No hay trabajadores asignados a este lote actualmente.</p>
                        </div>
                    @else
                        <ul class="info-list" style="padding: 0 20px;">
                            @foreach($lote->trabajadores as $trabajador)
                            <li>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div class="d-flex align-items-center">
                                        <div style="width: 34px; height: 34px; background: #d1fae5; color: #065f46; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-right: 12px; font-size: 0.85rem;">
                                            {{ substr($trabajador->nombre, 0, 1) }}
                                        </div>
                                        <div>
                                            <span style="font-weight: 600; color: #334155; display: block; font-size: 0.9rem;">{{ $trabajador->nombre }}</span>
                                            <span style="color: #64748b; font-size: 0.75rem;"><i class="fa-solid fa-circle text-success mr-1" style="font-size: 0.45rem;"></i> Asignado activo</span>
                                        </div>
                                    </div>
                                    @if(auth()->user()->isAdmin())
                                    <form action="{{ route('asignaciones.lotes.desactivar') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar la asignación de este trabajador? Se conservará el registro histórico.');">
                                        @csrf
                                        <input type="hidden" name="id_lote" value="{{ $lote->id }}">
                                        <input type="hidden" name="id_usuario" value="{{ $trabajador->id }}">
                                        <button type="submit" class="btn-action-danger" title="Desactivar asignación">
                                            <i class="fa-solid fa-user-minus"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @if(auth()->user()->isAdmin())
                <div class="card-modern-body" style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 14px 16px;">
                    <form action="{{ route('asignaciones.lotes.store') }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="id_lote" value="{{ $lote->id }}">
                        <div class="input-group input-group-sm">
                            <select name="id_usuario" class="form-control" required style="border-radius: 8px 0 0 8px; font-size: 0.85rem; border-color: #cbd5e1;">
                                <option value="">Asignar trabajador...</option>
                                @if(isset($trabajadoresDisponibles))
                                    @foreach($trabajadoresDisponibles as $t)
                                        @if(!$lote->trabajadores->contains('id', $t->id))
                                            <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success" style="border-radius: 0 8px 8px 0; font-size: 0.85rem; font-weight: 600; background-color: #10b981; border-color: #10b981;">
                                    <i class="fa-solid fa-plus mr-1"></i> Asignar
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('asignaciones.index') }}" class="btn-custom-outline d-block text-center" style="font-size: 0.8rem; padding: 6px 12px; border-radius: 8px;">
                        <i class="fa-solid fa-list-check mr-1 text-emerald-600"></i> Panel General de Asignaciones
                    </a>
                </div>
                @endif
            </div>
            
        </div>
        
        <!-- Columna Derecha: Tabs de Cultivos y Auditoría -->
        <div class="col-lg-8 col-md-7">
            <div class="card-modern" style="padding:0;">
                <ul class="nav nav-modern">
                    <li class="nav-item">
                        <a class="nav-link active" href="#actual" data-toggle="tab">
                            <i class="fa-solid fa-seedling text-emerald-600"></i> Cultivo Actual
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#historial" data-toggle="tab">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Historial de Cultivos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#modificaciones" data-toggle="tab">
                            <i class="fa-solid fa-pen-to-square text-amber-600"></i> Historial de Modificaciones
                            @if(isset($historialCambios) && $historialCambios->isNotEmpty())
                                <span class="badge badge-soft-primary ml-1" style="font-size: 0.75rem;">{{ $historialCambios->count() }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
                
                <div class="card-modern-body">
                    <div class="tab-content">
                        
                        <!-- Tab Cultivo Actual -->
                        <div class="active tab-pane" id="actual">
                            @if($lote->cultivoActivo)
                                <div class="cultivo-highlight-box">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 border-right">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="stat-icon-pastel bg-emerald-100 text-emerald-600 mr-3">
                                                    <i class="fa-solid fa-seedling"></i>
                                                </div>
                                                <div>
                                                    <h4 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.35rem;">
                                                        <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" style="color: inherit; text-decoration: none;">
                                                            {{ $lote->cultivoActivo->codigo }}
                                                        </a>
                                                    </h4>
                                                    <span style="color: #64748b; font-size: 0.85rem;">
                                                        <i class="fa-regular fa-calendar-days mr-1 text-emerald-600"></i> Sembrado el {{ $lote->cultivoActivo->fecha_siembra->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <ul class="info-list text-left pr-3">
                                                <li>
                                                    <span class="info-label"><i class="fa-solid fa-wheat-awn text-emerald-600"></i> Variedad</span>
                                                    <span class="info-value" style="font-size: 0.9rem;">{{ $lote->cultivoActivo->variedad->tipoCultivo->nombre ?? '' }} - {{ $lote->cultivoActivo->variedad->nombre ?? '' }}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label"><i class="fa-solid fa-spinner text-emerald-600"></i> Estado</span>
                                                    <span class="badge-soft-info">{{ ucfirst($lote->cultivoActivo->estado) }}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label"><i class="fa-regular fa-calendar-check text-amber-600"></i> Cosecha Estimada</span>
                                                    <span class="info-value text-amber-600">{{ $lote->cultivoActivo->fecha_cosecha_estimada->format('d/m/Y') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div class="col-md-5 text-center d-flex flex-column justify-content-center align-items-center pl-md-4 pt-3 pt-md-0">
                                            @php
                                                $inicio = $lote->cultivoActivo->fecha_siembra;
                                                $fin = $lote->cultivoActivo->fecha_cosecha_estimada;
                                                $hoy = now();
                                                $totalDias = $inicio->diffInDays($fin) ?: 1;
                                                $diasPasados = $inicio->diffInDays($hoy);
                                                $porcentaje = min(100, max(0, round(($diasPasados / $totalDias) * 100)));
                                            @endphp
                                            
                                            <div style="width: 100%;">
                                                <p style="color: #475569; font-weight: 600; margin-bottom: 6px; font-size: 0.875rem;">Progreso hacia la cosecha</p>
                                                <div class="progress-modern">
                                                    <div class="progress-modern-bar" style="width: {{ $porcentaje }}%"></div>
                                                </div>
                                                <span style="color: #059669; font-weight: 800; font-size: 1.25rem;">{{ $porcentaje }}%</span>
                                                <span style="color: #64748b; font-size: 0.8rem; display: block;">tiempo de desarrollo transcurrido</span>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" class="btn-custom-outline" style="border-color: #10b981; color: #059669; border-radius: 10px; font-weight: 600;">
                                                    <i class="fa-solid fa-eye mr-1"></i> Inspeccionar Cultivo
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="empty-state-card">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-leaf"></i>
                                    </div>
                                    <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 8px;">Este lote está disponible</h4>
                                    <p style="color: #64748b; max-width: 440px; margin: 0 auto 24px; font-size: 0.95rem;">
                                        No hay ningún cultivo activo sembrado en este momento. El lote se encuentra listo para iniciar un nuevo ciclo de producción.
                                    </p>
                                    @if(auth()->user()->isAdmin())
                                    <a href="{{ route('cultivos.create', ['lote' => $lote->id]) }}" class="btn-add-cultivo">
                                        <i class="fa-solid fa-plus"></i> Registrar Nueva Siembra
                                    </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Historial de Cultivos -->
                        <div class="tab-pane" id="historial">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-modern-title">
                                    <i class="fa-solid fa-clock-rotate-left text-emerald-600 mr-2"></i> Registro Histórico de Cultivos
                                </h5>
                                <span class="badge badge-soft-secondary">{{ $lote->cultivos->count() }} cultivo(s)</span>
                            </div>
                            
                            @if($lote->cultivos->isEmpty())
                                <div class="empty-state-card py-5">
                                    <i class="fa-solid fa-clock-rotate-left fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p style="color: #64748b; font-weight: 600; margin: 0;">No hay registro histórico de cultivos para este lote.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Fecha Siembra</th>
                                                <th>Estado</th>
                                                <th>Cosecha (Kg)</th>
                                                <th class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lote->cultivos as $cultivoHist)
                                            <tr>
                                                <td style="font-weight: 600; color: #1e293b;">
                                                    <i class="fa-solid fa-seedling text-emerald-600 mr-2"></i>{{ $cultivoHist->codigo }}
                                                </td>
                                                <td><i class="fa-regular fa-calendar-days text-muted mr-1"></i> {{ $cultivoHist->fecha_siembra->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="badge-soft-{{ $cultivoHist->estado == 'cosechado' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($cultivoHist->estado) }}
                                                    </span>
                                                </td>
                                                <td style="font-weight: 700; color: #065f46;">{{ $cultivoHist->cantidad_cosechada_kg ? number_format($cultivoHist->cantidad_cosechada_kg, 2) . ' Kg' : '—' }}</td>
                                                <td class="text-right">
                                                    <a href="{{ route('cultivos.show', $cultivoHist) }}" class="btn-action-info" title="Ver detalle de cultivo">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Tab Historial de Modificaciones (Auditoría) -->
                        <div class="tab-pane" id="modificaciones">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-modern-title">
                                    <i class="fa-solid fa-clipboard-check text-emerald-600 mr-2"></i> Registro de Modificaciones del Lote
                                </h5>
                                <span class="badge badge-soft-info">{{ isset($historialCambios) ? $historialCambios->count() : 0 }} modificación(es)</span>
                            </div>

                            @if(!isset($historialCambios) || $historialCambios->isEmpty())
                                <div class="empty-state-card py-5">
                                    <i class="fa-solid fa-clipboard-check fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <h6 style="font-weight: 700; color: #475569;">Sin modificaciones previas</h6>
                                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Este lote conserva sus datos originales desde su registro inicial.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table-modern" id="tabla-historial-modificaciones">
                                        <thead>
                                            <tr>
                                                <th>Fecha y Hora</th>
                                                <th>Responsable</th>
                                                <th>Acción</th>
                                                <th>Cambios Realizados</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($historialCambios as $registro)
                                            <tr>
                                                <td style="white-space: nowrap; font-size: 0.85rem; vertical-align: top;">
                                                    <i class="fa-regular fa-clock text-muted mr-1"></i>
                                                    {{ $registro->ocurrido_en ? $registro->ocurrido_en->format('d/m/Y H:i:s') : '—' }}
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <div class="d-flex align-items-center">
                                                        <div style="width: 28px; height: 28px; background: #dbeafe; color: #1e40af; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; margin-right: 8px;">
                                                            {{ substr($registro->usuario->nombre ?? 'A', 0, 1) }}
                                                        </div>
                                                        <span style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                                            {{ $registro->usuario->nombre ?? 'Administrador' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span class="badge badge-soft-primary" style="text-transform: uppercase;">{{ $registro->accion }}</span>
                                                </td>
                                                <td>
                                                    @if(is_array($registro->datos_despues) && count($registro->datos_despues) > 0)
                                                        <div class="d-flex flex-column" style="gap: 6px;">
                                                            @foreach($registro->datos_despues as $campo => $nuevoValor)
                                                                @php
                                                                    $anterior = $registro->datos_antes[$campo] ?? '—';
                                                                    $campoLabel = match($campo) {
                                                                        'nombre'            => 'Nombre',
                                                                        'ubicacion'         => 'Ubicación',
                                                                        'area_ha'           => 'Área (ha)',
                                                                        'id_tipo_preferido' => 'Cultivo Preferido',
                                                                        'es_alternativo'    => 'Alternativo',
                                                                        'activo'            => 'Estado Activo',
                                                                        'fotografia'        => 'Fotografía',
                                                                        default             => ucfirst($campo)
                                                                    };
                                                                @endphp
                                                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 0.85rem;">
                                                                    <strong style="color: #475569;">{{ $campoLabel }}:</strong>
                                                                    <span class="text-danger ml-1" style="text-decoration: line-through;">
                                                                        {{ is_bool($anterior) ? ($anterior ? 'Sí' : 'No') : $anterior }}
                                                                    </span>
                                                                    <i class="fa-solid fa-arrow-right mx-1 text-muted" style="font-size: 0.7rem;"></i>
                                                                    <span class="text-success font-weight-bold">
                                                                        {{ is_bool($nuevoValor) ? ($nuevoValor ? 'Sí' : 'No') : $nuevoValor }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted font-italic">Sin detalle disponible</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
