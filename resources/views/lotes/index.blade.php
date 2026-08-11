@extends('layouts.adminlte')

@section('title', 'Lotes Agrícolas')

@push('styles')
<style>
    /* Estilos Gsigvos para el módulo Lotes */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(58, 165, 116, 0.15);
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(29, 69, 51, 0.04);
        margin-bottom: 24px;
    }
    
    .kpi-card {
        padding: 20px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(29, 69, 51, 0.08);
    }
    .kpi-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: #16332b;
        margin: 0;
        font-family: 'Outfit', sans-serif;
    }
    .kpi-label {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
        margin-top: 4px;
        text-transform: capitalize;
    }

    .table-agro { width: 100%; margin: 0; }
    .table-agro th {
        background-color: #f0fdf4;
        color: #6b9e8a;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #d8eee4;
        padding: 12px 20px;
        border-top: none;
    }
    .table-agro td {
        vertical-align: middle;
        border-bottom: 1px solid #f0fdf4;
        font-size: 0.875rem;
        padding: 12px 20px;
    }
    .table-agro tbody tr:hover {
        background-color: #f9fefb;
        transition: background-color 0.2s;
    }
    
    .status-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-disponible { background: #d1fae5; color: #047857; } 
    .status-ocupado { background: #dbeafe; color: #1d4ed8; } 
    .status-en_descanso { background: #fef3c7; color: #b45309; }
    .status-inactivo { background: #fee2e2; color: #b91c1c; }
    
    .btn-action-primary {
        background-color: #eff6ff;
        color: #2563eb;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
    }
    .btn-action-primary:hover { background-color: #dbeafe; color: #1d4ed8; }
    
    .btn-action-danger {
        background-color: #fef2f2;
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
    }
    .btn-action-danger:hover { background-color: #fee2e2; color: #b91c1c; }
    
    .btn-action-info {
        background-color: #f0fdfa;
        color: #0d9488;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
    }
    .btn-action-info:hover { background-color: #ccfbf1; color: #0f766e; }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        margin-top: 10px;
    }
    .header-actions h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #16332b;
        margin: 0;
        font-family: 'Outfit', sans-serif;
    }
    .header-actions p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 4px 0 0 0;
    }
    .btn-add-lote {
        background-color: #10b981;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 12px;
        border: none;
        transition: background-color 0.2s;
        text-decoration: none;
    }
    .btn-add-lote:hover {
        background-color: #059669;
        color: white;
        text-decoration: none;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4">

    <!-- Cabecera y acciones -->
    <div class="header-actions">
        <div>
            <h2>Lotes</h2>
            <p>Gestión y registro de lotes de la finca.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('lotes.index') }}" method="GET" class="mr-3" style="width: 250px;">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" style="border-radius: 12px 0 0 12px; border: 1px solid #e2e8f0; background: rgba(255,255,255,0.8);" placeholder="Buscar lote..." value="{{ request('buscar') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn" style="background: white; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 12px 12px 0; color: #64748b;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('lotes.create') }}" class="btn-add-lote shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nuevo Lote
            </a>
            @endif
        </div>
    </div>

    <!-- KPIs Resumen de estados -->
    @if(isset($estadosLotes))
    <div class="row mb-2">
        @foreach(['disponible', 'ocupado', 'en_descanso', 'inactivo'] as $estado)
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card kpi-card m-0">
                <p class="kpi-number">{{ $estadosLotes[$estado] ?? 0 }}</p>
                <p class="kpi-label">{{ str_replace('_', ' ', $estado) }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Tabla principal -->
    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-agro">
                <thead>
                    <tr>
                        <th style="width: 60px;">Img</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Área (ha)</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $l)
                    <tr>
                        <td>
                            @if($l->fotografia)
                                <img src="{{ asset('storage/' . $l->fotografia) }}" alt="Lote" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                            @else
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fas fa-map text-sm"></i>
                                </div>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: #065f46;">{{ $l->identificador }}</td>
                        <td style="color: #334155; font-weight: 500;">
                            {{ $l->nombre }}
                            @if($l->es_alternativo)
                                <span style="font-size: 0.65rem; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">Alt</span>
                            @endif
                        </td>
                        <td style="color: #64748b;">{{ Str::limit($l->ubicacion, 30) }}</td>
                        <td style="color: #334155;">{{ number_format($l->area_ha, 2) }}</td>
                        <td>
                            <span class="status-pill status-{{ $l->estado }}">
                                {{ ucfirst(str_replace('_', ' ', $l->estado)) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="d-flex justify-content-end" style="gap: 8px;">
                                <a href="{{ route('lotes.show', $l) }}" class="btn-action-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('lotes.edit', $l) }}" class="btn-action-primary" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('lotes.destroy', $l) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar el lote {{ addslashes($l->nombre) }}?');">
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
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-map" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 16px;"></i>
                            <p style="font-size: 0.875rem; color: #94a3b8; margin: 0;">No hay lotes registrados aún.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($lotes->hasPages())
        <div class="px-4 py-3" style="border-top: 1px solid #f0fdf4;">
            {{ $lotes->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>

</div>
@endsection
