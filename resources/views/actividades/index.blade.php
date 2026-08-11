@extends('layouts.adminlte')

@section('title', 'Actividades Agrícolas')

@push('styles')
<style>
    /* Estilos Gsigvos para el módulo Actividades */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 24px;
        border: 1px solid rgba(255,255,255,0.4);
        margin-bottom: 24px;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .header-title h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #16332b;
        margin: 0;
        font-family: 'Outfit', sans-serif;
    }
    .header-title p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 4px 0 0 0;
    }
    
    .btn-warning-custom {
        background-color: #f59e0b;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 12px;
        border: none;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-warning-custom:hover {
        background-color: #d97706;
        color: white;
        text-decoration: none;
    }
    
    .filters-container {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    .custom-select-filter, .custom-input-filter {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.875rem;
        color: #475569;
        transition: all 0.2s;
    }
    .custom-select-filter:focus, .custom-input-filter:focus {
        outline: none;
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15);
    }
    .btn-search {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        border-radius: 10px;
        padding: 8px 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-search:hover {
        background-color: #e2e8f0;
    }
    
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table th {
        text-align: left;
        padding: 12px 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b9e8a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f0fdf4;
        border-bottom: 1px solid #d8eee4;
    }
    .custom-table th:first-child {
        border-top-left-radius: 12px;
    }
    .custom-table th:last-child {
        border-top-right-radius: 12px;
    }
    .custom-table td {
        padding: 16px 20px;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0fdf4;
        vertical-align: middle;
        background-color: transparent;
        transition: background-color 0.2s;
    }
    .custom-table tbody tr:hover td {
        background-color: #f9fefb;
    }
    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .status-pill {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-pendiente { background: #fef3c7; color: #b45309; }
    .status-completada { background: #dcfce7; color: #15803d; }
    .status-cancelada { background: #fee2e2; color: #b91c1c; }
    .status-default { background: #f1f5f9; color: #64748b; }
    
    .actions-flex {
        display: flex;
        gap: 8px;
    }
    .btn-action-info {
        background-color: #f0f9ff;
        color: #0284c7;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-info:hover { background-color: #e0f2fe; color: #0369a1; text-decoration: none; }
    
    .btn-action-primary {
        background-color: #eff6ff;
        color: #2563eb;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-primary:hover { background-color: #dbeafe; color: #1d4ed8; text-decoration: none; }
    
    .btn-action-danger {
        background-color: #fef2f2;
        color: #dc2626;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-action-danger:hover { background-color: #fee2e2; color: #b91c1c; }
    
    .empty-state {
        text-align: center;
        padding: 48px 20px;
    }
    .empty-state i {
        font-size: 3rem;
        color: #e2e8f0;
        margin-bottom: 16px;
    }
    .empty-state p {
        color: #94a3b8;
        font-size: 0.875rem;
        margin: 0;
    }
    
    .date-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    .date-badge.danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="header-actions">
        <div class="header-title">
            <h2>Actividades</h2>
            <p>Gestión de Tareas y Actividades Agrícolas.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('actividades.create') }}" class="btn-warning-custom">
            <i class="fas fa-plus"></i> Programar Tarea
        </a>
        @endif
    </div>

    <!-- Filtros -->
    <div class="filters-container">
        <form action="{{ route('actividades.index') }}" method="GET" id="form-filtro" style="display: flex; gap: 12px;">
            <select name="estado" class="custom-select-filter" onchange="document.getElementById('form-filtro').submit();">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
            </select>
            
            <div style="display: flex;">
                <input type="text" name="buscar" class="custom-input-filter" placeholder="Buscar (ej. Riego)..." value="{{ request('buscar') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                <button type="submit" class="btn-search" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        @if($actividades->isEmpty())
            <div class="empty-state">
                <i class="fas fa-tasks block"></i>
                <p>No hay tareas registradas actualmente.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Actividad</th>
                            <th>Cultivo / Lote</th>
                            <th>Asignado A</th>
                            <th>Fecha Prog.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actividades as $act)
                        <tr style="{{ $act->estado == 'cancelada' ? 'opacity: 0.6;' : '' }}">
                            <td>
                                <span style="font-weight: 700; color: #64748b;">#{{ $act->id }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #16332b; margin-bottom: 2px;">{{ $act->tipoActividad->nombre }}</div>
                                <div style="font-size: 0.75rem; color: #64748b; max-width: 250px; white-space: normal;">
                                    {{ Str::limit($act->descripcion, 50) }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('cultivos.show', $act->cultivo) }}" style="color: #065f46; font-weight: 600; text-decoration: none; display: block;">
                                    {{ $act->cultivo->codigo }}
                                </a>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    <i class="fas fa-map-marker-alt text-danger" style="font-size: 0.7rem; margin-right: 2px;"></i> Lote {{ $act->cultivo->lote->identificador }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                                        {{ strtoupper(substr($act->asignadoA->nombre ?? 'U', 0, 1)) }}
                                    </div>
                                    <span style="color: #475569; font-weight: 500;">{{ $act->asignadoA->nombre ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $isDanger = $act->fecha_programada->isPast() && $act->estado == 'pendiente';
                                @endphp
                                <span class="date-badge {{ $isDanger ? 'danger' : '' }}">
                                    @if($isDanger)
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                    @else
                                        <i class="far fa-calendar-alt mr-1"></i>
                                    @endif
                                    {{ $act->fecha_programada->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = 'status-default';
                                    if($act->estado == 'pendiente') $statusClass = 'status-pendiente';
                                    if($act->estado == 'completada') $statusClass = 'status-completada';
                                    if($act->estado == 'cancelada') $statusClass = 'status-cancelada';
                                @endphp
                                <span class="status-pill {{ $statusClass }}">{{ ucfirst($act->estado) }}</span>
                            </td>
                            <td>
                                <div class="actions-flex">
                                    <a href="{{ route('actividades.show', $act) }}" class="btn-action-info" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('actividades.edit', $act) }}" class="btn-action-primary" title="Editar / Reportar">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                    <form action="{{ route('actividades.destroy', $act) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea? Esta acción no se puede deshacer.');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($actividades->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f0fdf4;">
                {{ $actividades->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
            @endif
            
        @endif
    </div>
</div>
@endsection
