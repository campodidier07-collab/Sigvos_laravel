@extends('layouts.adminlte')

@section('title', 'Control de Cosechas')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8fafc;
    }

    /* Botón Superior */
    .btn-top-primary {
        background-color: #10b981;
        color: white !important;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        text-decoration: none;
    }
    .btn-top-primary:hover {
        background-color: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.38);
        color: white !important;
        text-decoration: none;
    }

    /* KPI Summary Cards */
    .kpi-stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        transition: all 0.2s ease;
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }
    .kpi-icon-pastel {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .kpi-emerald { background: #d1fae5; color: #047857; }
    .kpi-blue { background: #e0f2fe; color: #0369a1; }
    .kpi-amber { background: #fef3c7; color: #b45309; }
    .kpi-purple { background: #ede9fe; color: #6d28d9; }
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
        margin: 0;
    }
    .kpi-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin: 2px 0 0 0;
    }

    /* Filtros y Buscador */
    .filters-container {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        align-items: center;
        flex-wrap: wrap;
    }
    .custom-input-filter {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 9px 16px;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s ease;
        width: 320px;
        max-width: 100%;
        outline: none;
    }
    .custom-input-filter:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .btn-search {
        background-color: #10b981;
        border: none;
        color: white;
        border-radius: 10px;
        padding: 9px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .btn-search:hover {
        background-color: #059669;
        color: white;
    }
    .btn-clear-search {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #64748b;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .btn-clear-search:hover {
        background-color: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
    }

    /* Tabla Glassmorphism */
    .glass-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table th {
        text-align: left;
        padding: 14px 20px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #065f46;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f0fdf4;
        border-bottom: 1px solid #d1fae5;
    }
    .custom-table td {
        padding: 16px 20px;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        background-color: transparent;
        transition: background-color 0.2s;
    }
    .custom-table tbody tr:hover td {
        background-color: #f9fefb;
    }

    /* Badges */
    .badge-soft-success { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-info { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }

    /* Botones de acción */
    .btn-action-harvest {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.1);
    }
    .btn-action-harvest:hover {
        background-color: #10b981;
        color: #ffffff;
        border-color: #10b981;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }
    .btn-action-info {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #dbeafe !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action-info:hover {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        transform: translateY(-1px);
    }
    .btn-action-warning {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #fffbeb !important;
        color: #d97706 !important;
        border: 1px solid #fef3c7 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action-warning:hover {
        background-color: #d97706 !important;
        color: #ffffff !important;
        border-color: #d97706 !important;
        transform: translateY(-1px);
    }

    /* Modales Estilo Sigvos Premium */
    .custom-modal-content {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .custom-modal-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 22px 26px;
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .custom-modal-body {
        padding: 26px 28px;
        background: #ffffff;
    }
    .custom-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 18px 28px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
    }
    .modal-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .modal-input, .modal-select {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 11px 14px;
        font-size: 0.92rem;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
        outline: none;
    }
    .modal-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px 16px;
        padding-right: 40px;
    }
    .modal-input:focus, .modal-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
    }
    .btn-modal-cancel {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-modal-cancel:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .btn-modal-submit {
        background: #10b981;
        color: white !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 10px 22px;
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-modal-submit:hover {
        background: #059669;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        transform: translateY(-1px);
    }
    .close-modal-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .close-modal-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        color: white;
    }

    /* Caja de Resumen de Cultivo Seleccionado */
    .crop-summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .crop-summary-item {
        display: flex;
        flex-direction: column;
    }
    .crop-summary-item span:first-child {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
    }
    .crop-summary-item span:last-child {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Control de Cosechas" 
        subtitle="Visualiza el progreso de cultivos listos o próximos a cosechar." 
        icon="fa-solid fa-basket-shopping"
    >
        <button type="button" class="btn-top-primary shadow-sm" onclick="abrirModalCosechaGeneral()">
            <i class="fa-solid fa-circle-plus"></i> Registrar Cosecha
        </button>
    </x-module-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #065f46; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
            @foreach($errors->all() as $error)
                <span>{{ $error }}</span><br>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Barra de Métricas Rápidas (KPIs) -->
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ number_format($totalKgCosechados ?? 0, 1) }} <small style="font-size: 0.9rem; font-weight: 600;">Kg</small></h3>
                    <p class="kpi-label">Total Cosechado</p>
                </div>
                <div class="kpi-icon-pastel kpi-emerald">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalCosechados ?? 0 }}</h3>
                    <p class="kpi-label">Cultivos Cosechados</p>
                </div>
                <div class="kpi-icon-pastel kpi-blue">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalPendientes ?? 0 }}</h3>
                    <p class="kpi-label">En Espera de Cosecha</p>
                </div>
                <div class="kpi-icon-pastel kpi-amber">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalLotesLiberados ?? 0 }}</h3>
                    <p class="kpi-label">Lotes Disponibles</p>
                </div>
                <div class="kpi-icon-pastel kpi-purple">
                    <i class="fa-solid fa-seedling"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-container">
        <form action="{{ route('cosecha.index') }}" method="GET" id="form-filtro" class="d-flex align-items-center" style="gap: 8px;">
            <div class="position-relative">
                <input type="text" name="buscar" class="custom-input-filter" placeholder="Buscar lote o cultivo..." value="{{ request('buscar') }}">
            </div>
            <button type="submit" class="btn-search">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
            </button>
            @if(request('buscar'))
                <a href="{{ route('cosecha.index') }}" class="btn-clear-search">
                    <i class="fa-solid fa-xmark"></i> Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Tabla Principal de Cosechas -->
    <div class="glass-card">
        @if($cultivos->isEmpty())
            <div class="text-center py-5">
                <i class="fa-solid fa-basket-shopping fa-3x text-muted mb-3" style="color: #cbd5e1;"></i>
                <h5 class="font-weight-bold" style="color: #475569;">No se encontraron cultivos en etapa de cosecha.</h5>
                <p class="text-muted" style="font-size: 0.9rem;">Cuando los cultivos alcancen su etapa de maduración aparecerán listos para cosechar aquí.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Cultivo / Lote</th>
                            <th>Variedad</th>
                            <th>Est. Cosecha</th>
                            <th>Estado</th>
                            <th>Rendimiento (Kg)</th>
                            <th class="text-right pr-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cultivos as $cultivo)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.92rem;">
                                    {{ $cultivo->codigo }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    <i class="fa-solid fa-location-dot text-emerald-600 mr-1"></i> {{ $cultivo->lote->nombre ?? 'Sin Lote' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #334155;">
                                    <i class="fa-solid fa-leaf text-emerald-500 mr-1"></i> {{ $cultivo->variedad->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span style="color: #64748b; font-weight: 500; font-size: 0.85rem;">
                                    <i class="fa-regular fa-calendar-days mr-1 text-slate-400"></i> {{ \Carbon\Carbon::parse($cultivo->fecha_cosecha_estimada)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                @if($cultivo->estado == 'cosechado')
                                    <span class="badge-soft-success">
                                        <i class="fa-solid fa-circle-check" style="font-size: 0.65rem;"></i> Cosechado
                                    </span>
                                @elseif($cultivo->estado == 'creciendo')
                                    <span class="badge-soft-info">
                                        <i class="fa-solid fa-arrow-trend-up" style="font-size: 0.65rem;"></i> Creciendo
                                    </span>
                                @else
                                    <span class="badge-soft-warning">
                                        <i class="fa-solid fa-clock" style="font-size: 0.65rem;"></i> {{ ucfirst($cultivo->estado) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($cultivo->cantidad_cosechada_kg)
                                    <span style="font-weight: 800; color: #047857; font-size: 0.95rem;">
                                        {{ number_format($cultivo->cantidad_cosechada_kg, 2) }} Kg
                                    </span>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                        El {{ \Carbon\Carbon::parse($cultivo->fecha_cosecha_real)->format('d/m/Y') }}
                                    </div>
                                    @if(!empty($cultivo->cantidad_estimada_kg))
                                        @php
                                            $dif = round($cultivo->cantidad_cosechada_kg - $cultivo->cantidad_estimada_kg, 2);
                                            $difClass = $dif >= 0 ? 'text-success' : 'text-danger';
                                            $difSign = $dif >= 0 ? "+{$dif}" : "{$dif}";
                                        @endphp
                                        <div class="mt-1 {{ $difClass }}" style="font-size: 0.75rem; font-weight: 700;">
                                            <i class="fa-solid fa-chart-line mr-1"></i> Dif: {{ $difSign }} Kg vs est. ({{ number_format($cultivo->cantidad_estimada_kg, 0) }} Kg)
                                        </div>
                                    @endif
                                @else
                                    <span class="badge-soft-secondary">
                                        <i class="fa-solid fa-hourglass mr-1"></i> Pendiente
                                    </span>
                                    @if(!empty($cultivo->cantidad_estimada_kg))
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 3px; font-weight: 500;">
                                            Est: {{ number_format($cultivo->cantidad_estimada_kg, 0) }} Kg
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-right pr-4">
                                @if($cultivo->estado !== 'cosechado')
                                    <!-- Botón que abre el modal directamente encima con el cultivo precargado -->
                                    <button type="button" class="btn-action-harvest" 
                                            onclick="abrirModalCosecha({{ $cultivo->id }}, '{{ $cultivo->codigo }}', '{{ $cultivo->lote->nombre ?? 'Sin Lote' }}', '{{ $cultivo->variedad->nombre ?? 'N/A' }}', {{ $cultivo->cantidad_estimada_kg ?? 0 }}, '{{ $cultivo->fecha_cosecha_estimada }}')"
                                            title="Registrar Cosecha de este Cultivo">
                                        <i class="fa-solid fa-scale-balanced"></i> Registrar Cosecha
                                    </button>
                                @else
                                    <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                        <!-- Ver Ficha de Cultivo -->
                                        <a href="{{ route('cultivos.show', $cultivo) }}" class="btn-action-info" title="Ver Ficha Técnica del Cultivo">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <!-- Modificar o ajustar cosecha registrada -->
                                        <button type="button" class="btn-action-warning" 
                                                onclick="abrirModalCosecha({{ $cultivo->id }}, '{{ $cultivo->codigo }}', '{{ $cultivo->lote->nombre ?? 'Sin Lote' }}', '{{ $cultivo->variedad->nombre ?? 'N/A' }}', {{ $cultivo->cantidad_estimada_kg ?? 0 }}, '{{ $cultivo->fecha_cosecha_real ?? date('Y-m-d') }}', {{ $cultivo->cantidad_cosechada_kg ?? 0 }}, '{{ addslashes($cultivo->observaciones ?? '') }}')"
                                                title="Ajustar Rendimiento Registrado">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($cultivos->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9;">
                {{ $cultivos->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal Registrar Cosecha (Estilo Sigvos Premium) -->
<div class="modal fade" id="modalCosecha" tabindex="-1" role="dialog" aria-labelledby="modalCosechaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('cosecha.store') }}" method="POST" id="formModalCosecha" class="modal-content custom-modal-content">
            @csrf
            <div class="custom-modal-header">
                <div class="d-flex align-items-center" style="gap: 14px;">
                    <div class="modal-icon-badge">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold m-0" id="modalCosechaLabel" style="font-family: 'Outfit', sans-serif;">
                            Registrar Cosecha y Rendimiento
                        </h5>
                        <small style="color: rgba(255,255,255,0.9); font-size: 0.8rem;">
                            Registra la producción final, libera el lote y actualiza la trazabilidad agronómica
                        </small>
                    </div>
                </div>
                <button type="button" class="close-modal-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <!-- Alerta Informativa de Sigvos -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #065f46; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fa-solid fa-circle-info text-emerald-600 mt-1" style="font-size: 1.1rem;"></i>
                    <div>
                        Al confirmar este registro, el cultivo pasará a estado <strong>cosechado</strong>, sus actividades pendientes huérfanas se cancelarán y el lote quedará <strong>disponible</strong> automáticamente para una nueva siembra.
                    </div>
                </div>

                <!-- Resumen Dinámico del Cultivo (Visible cuando se selecciona) -->
                <div id="boxResumenCultivo" class="crop-summary-card" style="display: none;">
                    <div class="crop-summary-item">
                        <span>Código Cultivo</span>
                        <span id="resumen_codigo" class="text-emerald-700"></span>
                    </div>
                    <div class="crop-summary-item">
                        <span>Lote Asignado</span>
                        <span id="resumen_lote"></span>
                    </div>
                    <div class="crop-summary-item">
                        <span>Variedad</span>
                        <span id="resumen_variedad"></span>
                    </div>
                    <div class="crop-summary-item">
                        <span>Estimación Proyectada</span>
                        <span id="resumen_estimacion" class="text-primary"></span>
                    </div>
                </div>

                <div class="row">
                    <!-- Selector de Cultivo -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="modal_id_cultivo">
                            <i class="fa-solid fa-seedling text-emerald-600"></i> Cultivo a Cosechar *
                        </label>
                        <select name="id_cultivo" id="modal_id_cultivo" class="modal-select" required onchange="alCambiarCultivoSelect(this)">
                            <option value="">Seleccionar cultivo activo...</option>
                            @foreach($cultivosParaCosechar as $c)
                                <option value="{{ $c->id }}" 
                                        data-codigo="{{ $c->codigo }}"
                                        data-lote="{{ $c->lote->nombre ?? 'Sin Lote' }}"
                                        data-variedad="{{ $c->variedad->nombre ?? 'N/A' }}"
                                        data-estimacion="{{ $c->cantidad_estimada_kg ?? 0 }}"
                                        data-fecha-estimada="{{ $c->fecha_cosecha_estimada }}">
                                    {{ $c->codigo }} — {{ $c->lote->nombre ?? 'Sin Lote' }} ({{ $c->variedad->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha de Cosecha Real -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="modal_fecha_cosecha_real">
                            <i class="fa-regular fa-calendar-check text-emerald-600"></i> Fecha de Cosecha Real *
                        </label>
                        <input type="date" name="fecha_cosecha_real" id="modal_fecha_cosecha_real" 
                               class="modal-input" 
                               max="{{ date('Y-m-d') }}" 
                               value="{{ date('Y-m-d') }}" 
                               required>
                    </div>
                </div>

                <div class="row">
                    <!-- Cantidad Cosechada en Kg -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="modal_cantidad_cosechada_kg">
                            <i class="fa-solid fa-scale-balanced text-emerald-600"></i> Cantidad Cosechada (Kg) *
                        </label>
                        <div class="position-relative">
                            <input type="number" name="cantidad_cosechada_kg" id="modal_cantidad_cosechada_kg" 
                                   step="0.01" min="0.1" 
                                   class="modal-input" 
                                   placeholder="Ej: 1250.50" 
                                   required
                                   oninput="calcularDiferenciaEnVivo(this.value)">
                        </div>
                        <div id="live_comparison_feedback" class="mt-2" style="font-size: 0.8rem; display: none;"></div>
                    </div>

                    <!-- Observaciones de Calidad / Cosecha -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="modal_observaciones">
                            <i class="fa-solid fa-clipboard-list text-emerald-600"></i> Observaciones de Cosecha
                        </label>
                        <textarea name="observaciones" id="modal_observaciones" 
                                  rows="2" 
                                  class="modal-input" 
                                  style="resize: none;" 
                                  placeholder="Calidad del grano/fruto, condiciones climáticas, mermas..."></textarea>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn-modal-submit" id="btnSubmitCosecha">
                    <i class="fa-solid fa-circle-check"></i> Guardar y Finalizar Cosecha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let estimacionActualKg = 0;

function abrirModalCosechaGeneral() {
    document.getElementById('formModalCosecha').reset();
    document.getElementById('modalCosechaLabel').textContent = 'Registrar Cosecha y Rendimiento';
    document.getElementById('modal_fecha_cosecha_real').value = '{{ date('Y-m-d') }}';
    document.getElementById('boxResumenCultivo').style.display = 'none';
    document.getElementById('live_comparison_feedback').style.display = 'none';
    estimacionActualKg = 0;

    $('#modalCosecha').modal('show');
}

function abrirModalCosecha(id, codigo, lote, variedad, cantidadEstimada, fechaReal, cantidadReal, observaciones) {
    document.getElementById('formModalCosecha').reset();

    const select = document.getElementById('modal_id_cultivo');
    let optionExiste = false;

    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value == id) {
            select.selectedIndex = i;
            optionExiste = true;
            break;
        }
    }

    if (!optionExiste) {
        const nuevaOpcion = new Option(`${codigo} — ${lote} (${variedad})`, id, true, true);
        nuevaOpcion.dataset.codigo = codigo;
        nuevaOpcion.dataset.lote = lote;
        nuevaOpcion.dataset.variedad = variedad;
        nuevaOpcion.dataset.estimacion = cantidadEstimada || 0;
        select.add(nuevaOpcion);
        select.value = id;
    }

    // Actualizar caja de resumen
    estimacionActualKg = parseFloat(cantidadEstimada) || 0;
    document.getElementById('resumen_codigo').textContent = codigo;
    document.getElementById('resumen_lote').textContent = lote;
    document.getElementById('resumen_variedad').textContent = variedad;
    document.getElementById('resumen_estimacion').textContent = estimacionActualKg > 0 ? (estimacionActualKg.toLocaleString('es-CO') + ' Kg') : 'Sin estimar';
    document.getElementById('boxResumenCultivo').style.display = 'flex';

    // Fecha
    if (fechaReal) {
        document.getElementById('modal_fecha_cosecha_real').value = fechaReal;
    } else {
        document.getElementById('modal_fecha_cosecha_real').value = '{{ date('Y-m-d') }}';
    }

    // Cantidad si existe
    if (cantidadReal && cantidadReal > 0) {
        document.getElementById('modal_cantidad_cosechada_kg').value = cantidadReal;
        document.getElementById('modalCosechaLabel').textContent = 'Modificar Cosecha — ' + codigo;
        calcularDiferenciaEnVivo(cantidadReal);
    } else {
        document.getElementById('modal_cantidad_cosechada_kg').value = '';
        document.getElementById('modalCosechaLabel').textContent = 'Registrar Cosecha — ' + codigo;
        document.getElementById('live_comparison_feedback').style.display = 'none';
    }

    // Observaciones
    if (observaciones) {
        document.getElementById('modal_observaciones').value = observaciones;
    } else {
        document.getElementById('modal_observaciones').value = '';
    }

    $('#modalCosecha').modal('show');

    setTimeout(() => {
        document.getElementById('modal_cantidad_cosechada_kg').focus();
    }, 400);
}

function alCambiarCultivoSelect(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (select.value && selectedOption) {
        const codigo = selectedOption.dataset.codigo || '';
        const lote = selectedOption.dataset.lote || '';
        const variedad = selectedOption.dataset.variedad || '';
        estimacionActualKg = parseFloat(selectedOption.dataset.estimacion) || 0;

        document.getElementById('resumen_codigo').textContent = codigo;
        document.getElementById('resumen_lote').textContent = lote;
        document.getElementById('resumen_variedad').textContent = variedad;
        document.getElementById('resumen_estimacion').textContent = estimacionActualKg > 0 ? (estimacionActualKg.toLocaleString('es-CO') + ' Kg') : 'Sin estimar';
        document.getElementById('boxResumenCultivo').style.display = 'flex';

        const cantidadIngresada = document.getElementById('modal_cantidad_cosechada_kg').value;
        if (cantidadIngresada) {
            calcularDiferenciaEnVivo(cantidadIngresada);
        }
    } else {
        document.getElementById('boxResumenCultivo').style.display = 'none';
        document.getElementById('live_comparison_feedback').style.display = 'none';
        estimacionActualKg = 0;
    }
}

function calcularDiferenciaEnVivo(valor) {
    const feedback = document.getElementById('live_comparison_feedback');
    const cantidad = parseFloat(valor);

    if (isNaN(cantidad) || cantidad <= 0 || estimacionActualKg <= 0) {
        feedback.style.display = 'none';
        return;
    }

    const dif = Math.round((cantidad - estimacionActualKg) * 100) / 100;
    const porcentaje = Math.round(((cantidad - estimacionActualKg) / estimacionActualKg) * 1000) / 10;
    const signo = dif >= 0 ? '+' : '';
    const color = dif >= 0 ? '#047857' : '#b91c1c';
    const icono = dif >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';

    feedback.style.display = 'block';
    feedback.style.color = color;
    feedback.innerHTML = `<i class="fa-solid ${icono} mr-1"></i> Rendimiento vs estimado: <strong>${signo}${dif.toLocaleString('es-CO')} Kg (${signo}${porcentaje}%)</strong>`;
}
</script>
@endpush
