@extends('layouts.adminlte')

@section('title', 'Cultivos')

@push('styles')
<style>
    /* Estilos Gsigvos para el módulo Cultivos */
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
    
    .btn-primary-custom {
        background-color: #10b981;
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
    .btn-primary-custom:hover {
        background-color: #059669;
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
    .status-sembrado { background: #dbeafe; color: #1d4ed8; }
    .status-creciendo { background: #fef3c7; color: #b45309; }
    .status-cosechado { background: #f3f4f6; color: #475569; }
    .status-perdido { background: #fee2e2; color: #b91c1c; }
    .status-default { background: #f1f5f9; color: #64748b; }
    
    .progress-wrapper {
        width: 100%;
        max-width: 120px;
    }
    .progress-bar-bg {
        width: 100%;
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 4px;
    }
    .progress-bar-fill {
        height: 100%;
        background-color: #10b981;
        border-radius: 999px;
    }
    .progress-text {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 500;
    }
    
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
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="header-actions">
        <div class="header-title">
            <h2>Cultivos</h2>
            <p>Registro y seguimiento de cultivos por lote.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('cultivos.create') }}" class="btn-primary-custom">
            <i class="fas fa-plus"></i> Nuevo Cultivo
        </a>
        @endif
    </div>

    <!-- Filtros -->
    <div class="filters-container">
        <form action="{{ route('cultivos.index') }}" method="GET" id="form-filtro" style="display: flex; gap: 12px;">
            <select name="estado" class="custom-select-filter" onchange="document.getElementById('form-filtro').submit();">
                <option value="">Todos los estados</option>
                <option value="sembrado" {{ request('estado') == 'sembrado' ? 'selected' : '' }}>Sembrado</option>
                <option value="creciendo" {{ request('estado') == 'creciendo' ? 'selected' : '' }}>Creciendo</option>
                <option value="cosechado" {{ request('estado') == 'cosechado' ? 'selected' : '' }}>Cosechado</option>
                <option value="perdido" {{ request('estado') == 'perdido' ? 'selected' : '' }}>Perdido</option>
            </select>
            
            <div style="display: flex;">
                <input type="text" name="buscar" class="custom-input-filter" placeholder="Buscar código..." value="{{ request('buscar') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                <button type="submit" class="btn-search" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        @if($cultivos->isEmpty())
            <div class="empty-state">
                <i class="fas fa-seedling block"></i>
                <p>No hay cultivos registrados actualmente.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Variedad</th>
                            <th>Tipo</th>
                            <th>Lote</th>
                            <th>Siembra</th>
                            <th>Estado / Progreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cultivos as $cultivo)
                        <tr>
                            <td>
                                <span style="font-weight: 700; color: #065f46;">{{ $cultivo->codigo }}</span>
                            </td>
                            <td>
                                <span style="color: #334155; font-weight: 500;">{{ $cultivo->variedad->nombre ?? '—' }}</span>
                            </td>
                            <td>
                                <span style="color: #64748b;">{{ $cultivo->variedad->tipoCultivo->nombre ?? '—' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('lotes.show', $cultivo->lote) }}" style="color: #64748b; text-decoration: none;">
                                    {{ $cultivo->lote->identificador }} — {{ $cultivo->lote->nombre }}
                                </a>
                            </td>
                            <td>
                                <span style="color: #64748b;">{{ $cultivo->fecha_siembra->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @php
                                        $statusClass = 'status-default';
                                        if($cultivo->estado == 'sembrado') $statusClass = 'status-sembrado';
                                        if($cultivo->estado == 'creciendo') $statusClass = 'status-creciendo';
                                        if($cultivo->estado == 'cosechado') $statusClass = 'status-cosechado';
                                        if($cultivo->estado == 'perdido') $statusClass = 'status-perdido';
                                    @endphp
                                    <div><span class="status-pill {{ $statusClass }}">{{ ucfirst($cultivo->estado) }}</span></div>
                                    
                                    @if($cultivo->estaActivo())
                                        @php
                                           $inicio = $cultivo->fecha_siembra;
                                           $fin = $cultivo->fecha_cosecha_estimada;
                                           $hoy = now();
                                           $total = max(1, $inicio->diffInDays($fin));
                                           $pasados = max(0, $inicio->diffInDays($hoy));
                                           $prog = min(100, round(($pasados / $total) * 100));
                                        @endphp
                                        <div class="progress-wrapper">
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: {{ $prog }}%"></div>
                                            </div>
                                            <span class="progress-text">{{ $prog }}% (Est: {{ $cultivo->fecha_cosecha_estimada->format('M Y') }})</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="actions-flex">
                                    <a href="{{ route('cultivos.show', $cultivo) }}" class="btn-action-info" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn-action-primary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Eliminar cultivo?', '¿Eliminar cultivo {{ addslashes($cultivo->codigo) }}? Esta acción es irreversible.');" style="margin: 0;">
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
            
            @if($cultivos->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f0fdf4;">
                {{ $cultivos->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
            @endif
            
        @endif
    </div>
</div>
@endsection
