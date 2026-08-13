@extends('layouts.adminlte')

@section('title', 'Detalle de Lote')

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
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .card-modern-body {
        padding: 20px;
    }
    
    /* Icon Box */
    .lote-icon-box {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 800;
        margin: 0 auto 16px;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .lote-icon-box.secondary {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
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
    }
    .info-value {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.95rem;
    }
    
    /* Modern Tabs */
    .nav-modern {
        border-bottom: 2px solid #e2e8f0;
        padding: 0 20px;
        background: #ffffff;
    }
    .nav-modern .nav-item {
        margin-bottom: -2px;
    }
    .nav-modern .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 16px 20px;
        transition: all 0.2s;
    }
    .nav-modern .nav-link:hover {
        color: #10b981;
    }
    .nav-modern .nav-link.active {
        color: #10b981;
        border-bottom: 2px solid #10b981;
        background: transparent;
    }
    
    /* Badges */
    .badge-soft-success { background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-info { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-primary { background: #eff6ff; color: #1d4ed8; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    
    /* Buttons */
    .btn-custom-outline {
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-custom-outline:hover {
        background: #f1f5f9;
        color: #1e293b;
        text-decoration: none;
    }
    .btn-custom-primary {
        background: #10b981;
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-custom-primary:hover {
        background: #059669;
        color: white;
        text-decoration: none;
    }
    
    /* Progress Bar Modern */
    .progress-modern {
        background-color: #f1f5f9;
        border-radius: 9999px;
        height: 12px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .progress-modern-bar {
        background-color: #10b981;
        height: 100%;
        border-radius: 9999px;
        transition: width 0.5s ease-in-out;
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
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .table-modern td {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        vertical-align: middle;
    }
    .table-modern tr:last-child td {
        border-bottom: none;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Lote {{ $lote->identificador }} - {{ $lote->nombre }}" 
        subtitle="Información general, historial y cultivos activos del lote." 
        icon="fa-layer-group"
    >
        @if(auth()->user()->isAdmin())
        <a href="{{ route('lotes.edit', $lote) }}" class="btn-custom-primary" style="background: #2563eb;">
            <i class="fas fa-edit mr-1"></i> Editar Lote
        </a>
        @endif
    </x-module-header>

    <div class="row">
        <!-- Columna Izquierda: Info y Trabajadores -->
        <div class="col-md-4">
            
            <!-- Tarjeta de Info del Lote -->
            <div class="card-modern">
                <div class="card-modern-body text-center">
                    <div class="lote-icon-box {{ $lote->activo ? '' : 'secondary' }}">
                        {{ $lote->identificador }}
                    </div>
                    
                    <h3 style="font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 8px;">{{ $lote->nombre }}</h3>
                    
                    <div style="margin-bottom: 20px;">
                        @if($lote->es_alternativo)
                            <span class="badge-soft-info mr-1">Alternativo</span>
                        @else
                            <span class="badge-soft-primary mr-1">Principal</span>
                        @endif
                        @if(!$lote->activo)
                            <span class="badge-soft-secondary">Inactivo</span>
                        @endif
                    </div>

                    <ul class="info-list text-left">
                        <li>
                            <span class="info-label">Estado</span>
                            @php
                                $estadoClass = match($lote->estado) {
                                    'disponible' => 'success',
                                    'ocupado' => 'warning',
                                    'mantenimiento' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge-soft-{{ $estadoClass }}">{{ ucfirst($lote->estado) }}</span>
                        </li>
                        <li>
                            <span class="info-label">Área</span>
                            <span class="info-value">{{ number_format($lote->area_ha, 2) }} ha</span>
                        </li>
                        <li>
                            <span class="info-label">Cultivo Preferido</span>
                            <span class="info-value text-right">{{ $lote->tipoPreferido->nombre ?? 'Ninguno' }}</span>
                        </li>
                    </ul>
                    
                    <div class="mt-4 text-left" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span class="info-label d-block mb-1"><i class="fas fa-map-marker-alt text-danger mr-1"></i> Ubicación</span>
                        <p style="color: #475569; margin: 0; font-size: 0.9rem;">{{ $lote->ubicacion }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Trabajadores Asignados -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <h4 class="card-modern-title"><i class="fas fa-users text-muted mr-2"></i> Trabajadores Asignados</h4>
                </div>
                <div class="card-modern-body p-0">
                    @if($lote->trabajadores->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-2x mb-2" style="color: #cbd5e1;"></i>
                            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">No hay trabajadores asignados.</p>
                        </div>
                    @else
                        <ul class="info-list" style="padding: 0 20px;">
                            @foreach($lote->trabajadores as $trabajador)
                            <li>
                                <div class="d-flex align-items-center">
                                    <div style="width: 32px; height: 32px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                                        {{ substr($trabajador->nombre, 0, 1) }}
                                    </div>
                                    <span style="font-weight: 600; color: #334155;">{{ $trabajador->nombre }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @if(auth()->user()->isAdmin())
                <div class="card-modern-body text-center" style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 12px;">
                    <a href="{{ route('lotes.edit', $lote) }}" class="btn-custom-outline" style="font-size: 0.85rem; padding: 6px 12px;">Gestionar Asignaciones</a>
                </div>
                @endif
            </div>
            
        </div>
        
        <!-- Columna Derecha: Tabs -->
        <div class="col-md-8">
            <div class="card-modern" style="padding:0;">
                <ul class="nav nav-modern">
                    <li class="nav-item">
                        <a class="nav-link active" href="#actual" data-toggle="tab"><i class="fas fa-seedling mr-1"></i> Cultivo Actual</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#historial" data-toggle="tab"><i class="fas fa-history mr-1"></i> Historial</a>
                    </li>
                </ul>
                
                <div class="card-modern-body">
                    <div class="tab-content">
                        
                        <!-- Tab Cultivo Actual -->
                        <div class="active tab-pane" id="actual">
                            @if($lote->cultivoActivo)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; position: relative;">
                                    
                                    <div class="row">
                                        <div class="col-sm-7 border-right">
                                            <div class="d-flex align-items-center mb-4">
                                                <div style="width: 50px; height: 50px; background: #d1fae5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 16px;">
                                                    <i class="fas fa-leaf"></i>
                                                </div>
                                                <div>
                                                    <h4 style="margin: 0; font-weight: 800; color: #1e293b;">
                                                        <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" style="color: inherit; text-decoration: none;">
                                                            {{ $lote->cultivoActivo->codigo }}
                                                        </a>
                                                    </h4>
                                                    <span style="color: #64748b; font-size: 0.85rem;"><i class="far fa-calendar-alt mr-1"></i> Sembrado el {{ $lote->cultivoActivo->fecha_siembra->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                            
                                            <ul class="info-list text-left" style="margin-right: 20px;">
                                                <li>
                                                    <span class="info-label">Variedad</span>
                                                    <span class="info-value" style="font-size: 0.9rem;">{{ $lote->cultivoActivo->variedad->tipoCultivo->nombre ?? '' }} - {{ $lote->cultivoActivo->variedad->nombre ?? '' }}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label">Estado</span>
                                                    <span class="badge-soft-info">{{ ucfirst($lote->cultivoActivo->estado) }}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label">Cosecha est.</span>
                                                    <span class="info-value text-warning">{{ $lote->cultivoActivo->fecha_cosecha_estimada->format('d/m/Y') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div class="col-sm-5 text-center d-flex flex-column justify-content-center align-items-center pl-4">
                                            @php
                                                $inicio = $lote->cultivoActivo->fecha_siembra;
                                                $fin = $lote->cultivoActivo->fecha_cosecha_estimada;
                                                $hoy = now();
                                                $totalDias = $inicio->diffInDays($fin) ?: 1;
                                                $diasPasados = $inicio->diffInDays($hoy);
                                                $porcentaje = min(100, max(0, round(($diasPasados / $totalDias) * 100)));
                                            @endphp
                                            
                                            <div style="width: 100%;">
                                                <p style="color: #64748b; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Progreso hacia cosecha</p>
                                                <div class="progress-modern">
                                                    <div class="progress-modern-bar" style="width: {{ $porcentaje }}%"></div>
                                                </div>
                                                <span style="color: #10b981; font-weight: 700; font-size: 1.1rem;">{{ $porcentaje }}%</span>
                                                <span style="color: #94a3b8; font-size: 0.8rem; display: block;">completado</span>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" class="btn-custom-outline" style="border-color: #10b981; color: #10b981;">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5" style="background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                                    <div style="width: 80px; height: 80px; background: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                        <i class="fas fa-leaf fa-2x" style="color: #94a3b8;"></i>
                                    </div>
                                    <h4 style="font-weight: 700; color: #334155; margin-bottom: 8px;">Este lote está disponible</h4>
                                    <p style="color: #64748b; max-width: 400px; margin: 0 auto 24px;">No hay ningún cultivo activo sembrado actualmente. El lote está listo para un nuevo inicio.</p>
                                    @if(auth()->user()->isAdmin())
                                    <a href="{{ route('cultivos.create', ['lote' => $lote->id]) }}" class="btn-custom-primary">
                                        <i class="fas fa-plus mr-1"></i> Registrar Nueva Siembra
                                    </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Historial -->
                        <div class="tab-pane" id="historial">
                            <h5 class="card-modern-title mb-4">Registro Histórico de Cultivos</h5>
                            
                            @if($lote->cultivos->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p style="color: #64748b; font-weight: 600;">No hay registro histórico de cultivos para este lote.</p>
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
                                                    <i class="fas fa-seedling text-success mr-2"></i>{{ $cultivoHist->codigo }}
                                                </td>
                                                <td><i class="far fa-calendar-alt text-muted mr-1"></i> {{ $cultivoHist->fecha_siembra->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="badge-soft-{{ $cultivoHist->estado == 'cosechado' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($cultivoHist->estado) }}
                                                    </span>
                                                </td>
                                                <td style="font-weight: 600;">{{ $cultivoHist->cantidad_cosechada_kg ? number_format($cultivoHist->cantidad_cosechada_kg, 2) . ' Kg' : '—' }}</td>
                                                <td class="text-right">
                                                    <a href="{{ route('cultivos.show', $cultivoHist) }}" class="btn-custom-outline" style="padding: 4px 10px; font-size: 0.8rem;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
