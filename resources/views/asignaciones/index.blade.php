@extends('layouts.adminlte')

@section('title', 'Asignaciones de Trabajadores')

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

    /* Worker Card Container */
    .worker-card {
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .worker-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(16, 185, 129, 0.08);
        border-color: #cbd5e1;
    }

    /* Worker Header */
    .worker-header {
        background: #ffffff;
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .worker-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #047857;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        border: 1.5px solid #a7f3d0;
        flex-shrink: 0;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.12);
    }
    .worker-info h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }
    .worker-info p {
        margin: 3px 0 0 0;
        font-size: 0.8rem;
        color: #64748b;
    }

    /* Badges */
    .badge-soft-success { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-info { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; }

    /* Lotes section */
    .lotes-section {
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        padding: 16px 20px;
    }
    .lotes-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .lote-item-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
        margin-bottom: 8px;
    }
    .lote-item-pill:last-child {
        margin-bottom: 0;
    }
    .lote-item-pill:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .empty-state-lotes {
        padding: 14px;
        text-align: center;
        background: #ffffff;
        border-radius: 10px;
        border: 1px dashed #cbd5e1;
        color: #94a3b8;
        font-size: 0.8rem;
    }

    /* Botones de acción compactos */
    .btn-action-primary {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #dbeafe !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action-primary:hover {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        transform: translateY(-1px);
    }
    .btn-action-danger {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fee2e2 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action-danger:hover {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
        transform: translateY(-1px);
    }

    /* Tasks section */
    .task-list {
        padding: 16px 20px;
        flex-grow: 1;
    }
    .task-list-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .task-item {
        padding: 12px 14px;
        border-radius: 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 4px solid #10b981;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
    }
    .task-item:last-child {
        margin-bottom: 0;
    }
    .task-item:hover {
        background: #ecfdf5;
        border-color: #86efac;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
    }
    .task-details h5 {
        margin: 0 0 3px 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #065f46;
    }
    .task-details p {
        margin: 0;
        font-size: 0.78rem;
        color: #047857;
        font-weight: 500;
    }
    .task-details .task-date {
        font-size: 0.75rem;
        margin-top: 3px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .task-status {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }

    .history-section {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed #cbd5e1;
    }
    .history-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .task-item.history-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #94a3b8;
    }
    .task-item.history-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .task-item.history-item .task-details h5 {
        color: #334155;
    }
    .task-item.history-item .task-details p {
        color: #64748b;
    }

    .empty-tasks {
        padding: 24px 16px;
        text-align: center;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed #e2e8f0;
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
    .custom-modal-header.header-blue {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    }
    .modal-icon-badge {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
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
    .modal-select {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 40px 12px 14px;
        font-size: 0.92rem;
        color: #1e293b;
        background-color: #ffffff;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px 16px;
        transition: all 0.2s ease;
        width: 100%;
        outline: none;
    }
    .modal-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
    }
    .modal-select-blue:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.18);
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
    .btn-modal-submit-blue {
        background: #0284c7;
        color: white !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 10px 22px;
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-modal-submit-blue:hover {
        background: #0369a1;
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
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
</style>
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Asignaciones de Trabajadores" 
        subtitle="Supervisa y gestiona la asignación de lotes y las labores de campo de cada trabajador." 
        icon="fa-solid fa-user-check"
    >
        @if(auth()->user()->isAdmin())
        <button type="button" class="btn-top-primary shadow-sm" data-toggle="modal" data-target="#modalAsignarLote">
            <i class="fa-solid fa-circle-plus"></i> Asignar Lote a Trabajador
        </button>
        @endif
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
    @php
        $totalTrabajadores = $trabajadores->count();
        $totalLotesAsignados = $trabajadores->sum(fn($t) => $t->lotes->count());
        $totalActividadesPendientes = $trabajadores->sum(fn($t) => $t->actividadesAsignadas->count());
        $totalActividadesCompletadas = $trabajadores->sum(fn($t) => $t->actividadesCompletadas->count());
    @endphp
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalTrabajadores }}</h3>
                    <p class="kpi-label">Trabajadores Activos</p>
                </div>
                <div class="kpi-icon-pastel kpi-emerald">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalLotesAsignados }}</h3>
                    <p class="kpi-label">Lotes Asignados</p>
                </div>
                <div class="kpi-icon-pastel kpi-blue">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalActividadesPendientes }}</h3>
                    <p class="kpi-label">Labores Pendientes</p>
                </div>
                <div class="kpi-icon-pastel kpi-amber">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-stat-card">
                <div>
                    <h3 class="kpi-value">{{ $totalActividadesCompletadas }}</h3>
                    <p class="kpi-label">Labores Completadas</p>
                </div>
                <div class="kpi-icon-pastel kpi-purple">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Trabajadores -->
    <div class="row">
        @forelse($trabajadores as $trabajador)
        <div class="col-md-6 col-xl-4 d-flex align-items-stretch">
            <div class="worker-card w-100">
                <!-- Cabecera del Trabajador -->
                <div class="worker-header">
                    <div class="worker-avatar">
                        @if($trabajador->foto_perfil)
                            <img src="{{ asset('storage/' . $trabajador->foto_perfil) }}" 
                                 alt="{{ $trabajador->nombre }}" 
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($trabajador->nombre, 0, 1)) }}
                            </span>
                        @else
                            {{ strtoupper(substr($trabajador->nombre, 0, 1)) }}
                        @endif
                    </div>
                    <div class="worker-info flex-grow-1">
                        <h4>
                            <a href="{{ route('usuarios.show', $trabajador) }}" class="text-dark text-decoration-none">
                                {{ $trabajador->nombre }}
                            </a>
                        </h4>
                        <p><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> {{ $trabajador->email }}</p>
                    </div>
                    <span class="badge-soft-success">
                        <i class="fa-solid fa-circle text-success mr-1" style="font-size: 0.45rem;"></i> Activo
                    </span>
                </div>
                
                <!-- Sección Lotes Asignados -->
                <div class="lotes-section">
                    <div class="lotes-section-title">
                        <span><i class="fa-solid fa-map-location-dot text-emerald-600 mr-1"></i> Lotes Asignados</span>
                        <span class="badge-soft-info" style="font-size: 0.7rem; padding: 2px 8px;">{{ $trabajador->lotes->count() }}</span>
                    </div>

                    @if($trabajador->lotes->isEmpty())
                        <div class="empty-state-lotes">
                            <i class="fa-solid fa-leaf text-emerald-400 mr-1"></i> Sin lotes asignados actualmente.
                        </div>
                    @else
                        <div class="d-flex flex-column">
                            @foreach($trabajador->lotes as $loteAsig)
                                @php
                                    $stClass = match($loteAsig->estado) {
                                        'disponible' => 'success',
                                        'ocupado' => 'info',
                                        'en_descanso' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <div class="lote-item-pill">
                                    <div class="pr-2">
                                        <a href="{{ route('lotes.show', $loteAsig) }}" style="font-weight: 700; color: #1e293b; font-size: 0.85rem; text-decoration: none;">
                                            <i class="fa-solid fa-location-dot text-emerald-600 mr-1"></i> Lote {{ $loteAsig->identificador }} - {{ $loteAsig->nombre }}
                                        </a>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            <span class="badge-soft-{{ $stClass }}" style="padding: 2px 6px; font-size: 0.65rem;">{{ str_replace('_', ' ', $loteAsig->estado) }}</span>
                                            <span class="ml-1 font-weight-bold">{{ number_format($loteAsig->area_ha, 2) }} ha</span>
                                        </div>
                                    </div>
                                    @if(auth()->user()->isAdmin())
                                    <div class="d-flex align-items-center" style="gap: 6px;">
                                        <!-- Reasignar lote -->
                                        <button type="button" class="btn-action-primary" 
                                                title="Reasignar lote a otro trabajador"
                                                onclick="abrirModalReasignar({{ $loteAsig->id }}, '{{ $loteAsig->identificador }} - {{ $loteAsig->nombre }}', {{ $trabajador->id }}, '{{ $trabajador->nombre }}')">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                        <!-- Desactivar lote -->
                                        <form action="{{ route('asignaciones.lotes.desactivar') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar la asignación de este lote para este trabajador? Se conservará el registro histórico.');">
                                            @csrf
                                            <input type="hidden" name="id_lote" value="{{ $loteAsig->id }}">
                                            <input type="hidden" name="id_usuario" value="{{ $trabajador->id }}">
                                            <button type="submit" class="btn-action-danger" title="Desactivar asignación (conserva historial)">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Sección Tareas y Labores -->
                <div class="task-list">
                    <div class="task-list-title">
                        <span><i class="fa-solid fa-list-check text-emerald-600 mr-1"></i> Actividades Asignadas</span>
                        <span class="badge-soft-secondary" style="font-size: 0.7rem; padding: 2px 8px;">{{ $trabajador->actividadesAsignadas->count() }}</span>
                    </div>

                    @if($trabajador->actividadesAsignadas->isEmpty())
                        <div class="empty-tasks">
                            <i class="fa-solid fa-circle-check mb-2" style="font-size: 1.5rem; color: #10b981;"></i><br>
                            <span class="font-weight-bold" style="color: #475569;">Al día en campo</span><br>
                            <small class="text-muted">Sin actividades pendientes actualmente</small>
                        </div>
                    @else
                        @foreach($trabajador->actividadesAsignadas as $actividad)
                        <div class="task-item">
                            <div class="task-details">
                                <h5>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</h5>
                                <p><i class="fa-solid fa-seedling mr-1 text-emerald-600"></i> {{ $actividad->cultivo->lote->nombre ?? 'N/A' }} - {{ $actividad->cultivo->codigo ?? 'N/A' }}</p>
                                <div class="task-date">
                                    <i class="fa-regular fa-calendar-days text-slate-400"></i>
                                    {{ $actividad->fecha_programada ? \Carbon\Carbon::parse($actividad->fecha_programada)->format('d/m/Y') : 'Sin fecha' }}
                                </div>
                            </div>
                            <div>
                                <span class="task-status {{ $actividad->estado == 'pendiente' ? 'status-pending' : 'status-progress' }}">
                                    {{ str_replace('_', ' ', $actividad->estado) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- Historial Reciente -->
                    @if($trabajador->actividadesCompletadas->isNotEmpty())
                        <div class="history-section">
                            <div class="history-title">
                                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Historial Reciente (Completadas)
                            </div>
                            @foreach($trabajador->actividadesCompletadas->take(3) as $actividad)
                            <div class="task-item history-item">
                                <div class="task-details">
                                    <h5>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</h5>
                                    <p><i class="fa-solid fa-seedling mr-1 text-slate-400"></i> {{ $actividad->cultivo->lote->nombre ?? 'N/A' }} - {{ $actividad->cultivo->codigo ?? 'N/A' }}</p>
                                    <div class="task-date">
                                        <i class="fa-regular fa-circle-check text-success"></i>
                                        Completada el {{ $actividad->fecha_programada ? \Carbon\Carbon::parse($actividad->fecha_programada)->format('d/m/Y') : 'Sin fecha' }}
                                    </div>
                                </div>
                                <div>
                                    <span class="task-status status-completed">
                                        COMPLETADA
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5" style="background: #ffffff; border-radius: 16px; border: 2px dashed #e2e8f0; padding: 48px 24px;">
                <i class="fa-solid fa-user-slash fa-3x text-muted mb-3" style="color: #cbd5e1;"></i>
                <h5 class="font-weight-bold" style="color: #475569;">No hay trabajadores registrados o disponibles.</h5>
                <p class="text-muted" style="font-size: 0.9rem;">Crea nuevos usuarios con rol de trabajador para gestionar asignaciones operativas.</p>
            </div>
        </div>
        @endforelse
    </div>

</div>

<!-- Modal Asignar Lote (Estilo Sigvos Premium) -->
<div class="modal fade" id="modalAsignarLote" tabindex="-1" role="dialog" aria-labelledby="modalAsignarLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('asignaciones.lotes.store') }}" method="POST" class="modal-content custom-modal-content">
            @csrf
            <div class="custom-modal-header">
                <div class="d-flex align-items-center" style="gap: 14px;">
                    <div class="modal-icon-badge">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold m-0" id="modalAsignarLoteLabel" style="font-family: 'Outfit', sans-serif;">
                            Asignar Lote a Trabajador
                        </h5>
                        <small style="color: rgba(255,255,255,0.9); font-size: 0.8rem;">Vincula la responsabilidad técnica y operativa del lote</small>
                    </div>
                </div>
                <button type="button" class="close-modal-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="custom-modal-body">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #065f46; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-info text-emerald-600" style="font-size: 1.1rem;"></i>
                    <span>El trabajador seleccionado será el responsable operativo de las labores asignadas en este lote.</span>
                </div>

                <div class="form-group mb-3">
                    <label class="modal-label" for="asig_id_usuario">
                        <i class="fa-solid fa-user-check text-emerald-600"></i> Trabajador Responsable *
                    </label>
                    <select name="id_usuario" id="asig_id_usuario" class="modal-select" required>
                        <option value="">Seleccionar trabajador activo...</option>
                        @foreach($trabajadores as $trab)
                            <option value="{{ $trab->id }}">{{ $trab->nombre }} ({{ $trab->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="modal-label" for="asig_id_lote">
                        <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Lote a Asignar *
                    </label>
                    <select name="id_lote" id="asig_id_lote" class="modal-select" required>
                        <option value="">Seleccionar lote disponible...</option>
                        @foreach($lotes as $l)
                            <option value="{{ $l->id }}">Lote {{ $l->identificador }} - {{ $l->nombre }} ({{ ucfirst($l->estado) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <i class="fa-solid fa-check"></i> Confirmar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reasignar Lote (Estilo Sigvos Premium) -->
<div class="modal fade" id="modalReasignarLote" tabindex="-1" role="dialog" aria-labelledby="modalReasignarLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('asignaciones.lotes.reasignar') }}" method="POST" class="modal-content custom-modal-content">
            @csrf
            <input type="hidden" name="id_lote" id="reasig_id_lote">
            <input type="hidden" name="id_usuario_anterior" id="reasig_id_usuario_anterior">
            
            <div class="custom-modal-header header-blue">
                <div class="d-flex align-items-center" style="gap: 14px;">
                    <div class="modal-icon-badge">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold m-0" id="modalReasignarLoteLabel" style="font-family: 'Outfit', sans-serif;">
                            Reasignación de Lote
                        </h5>
                        <small style="color: rgba(255,255,255,0.9); font-size: 0.8rem;">Transfiere el lote conservando trazabilidad histórica</small>
                    </div>
                </div>
                <button type="button" class="close-modal-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #065f46; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-info text-emerald-600" style="font-size: 1.1rem;"></i>
                    <span>Se cerrará la asignación actual del lote y se conservará su historial de auditoría intacto.</span>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                            <span class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em;">Lote a transferir</span>
                            <span id="reasig_nombre_lote" class="font-weight-bold d-block mt-1" style="color: #0284c7; font-size: 0.92rem;"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                            <span class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em;">Responsable actual</span>
                            <span id="reasig_nombre_trabajador_anterior" class="font-weight-bold text-danger d-block mt-1" style="font-size: 0.92rem;"></span>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="modal-label" for="reasig_id_usuario_nuevo">
                        <i class="fa-solid fa-user-plus text-primary"></i> Nuevo Trabajador Responsable *
                    </label>
                    <select name="id_usuario_nuevo" id="reasig_id_usuario_nuevo" class="modal-select modal-select-blue" required>
                        <option value="">Seleccionar nuevo trabajador...</option>
                        @foreach($trabajadores as $trab)
                            <option value="{{ $trab->id }}">{{ $trab->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-modal-submit-blue">
                    <i class="fa-solid fa-check"></i> Reasignar Lote
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function abrirModalReasignar(idLote, nombreLote, idUsuario, nombreUsuario) {
    document.getElementById('reasig_id_lote').value = idLote;
    document.getElementById('reasig_nombre_lote').textContent = nombreLote;
    document.getElementById('reasig_id_usuario_anterior').value = idUsuario;
    document.getElementById('reasig_nombre_trabajador_anterior').textContent = nombreUsuario;

    const selectNuevo = document.getElementById('reasig_id_usuario_nuevo');
    if (selectNuevo) {
        for (let i = 0; i < selectNuevo.options.length; i++) {
            selectNuevo.options[i].disabled = (selectNuevo.options[i].value == idUsuario);
        }
        selectNuevo.value = '';
    }

    $('#modalReasignarLote').modal('show');
}
</script>
@endpush
