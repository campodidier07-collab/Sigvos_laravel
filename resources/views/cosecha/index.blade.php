@extends('layouts.adminlte')

@section('title', 'Control de Cosechas')

@push('styles')
<style>
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
    .filters-container {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    .custom-input-filter {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.875rem;
        color: #475569;
        transition: all 0.2s;
        width: 300px;
    }
    .custom-input-filter:focus {
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
    .custom-table th:first-child { border-top-left-radius: 12px; }
    .custom-table th:last-child { border-top-right-radius: 12px; }
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
    
    .status-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-creciendo { background: #dbeafe; color: #1d4ed8; }
    .status-cosechado { background: #dcfce7; color: #15803d; }

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
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Control de Cosechas" 
        subtitle="Visualiza el progreso de cultivos listos o próximos a cosechar." 
        icon="fa-shopping-basket"
    >
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCosecha" style="background-color: #10b981; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600;">
            <i class="fas fa-wheat-awn mr-2"></i> Registrar Cosecha
        </button>
    </x-module-header>

    <!-- Filtros -->
    <div class="filters-container">
        <form action="{{ route('cosecha.index') }}" method="GET" id="form-filtro" style="display: flex;">
            <div style="display: flex;">
                <input type="text" name="buscar" class="custom-input-filter" placeholder="Buscar lote o cultivo..." value="{{ request('buscar') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                <button type="submit" class="btn-search" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        @if($cultivos->isEmpty())
            <div class="text-center p-5">
                <i class="fas fa-basket-shopping block mb-3" style="font-size: 3rem; color: #e2e8f0;"></i>
                <p class="text-muted">No se encontraron cultivos en etapa de cosecha.</p>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cultivos as $cultivo)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #16332b;">{{ $cultivo->codigo }}</div>
                                <div style="font-size: 0.75rem; color: #64748b;"><i class="fas fa-map-marker-alt mr-1"></i> {{ $cultivo->lote->nombre ?? 'N/A' }}</div>
                            </td>
                            <td>{{ $cultivo->variedad->nombre ?? 'N/A' }}</td>
                            <td>
                                <span style="color: #64748b; font-weight: 500;">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($cultivo->fecha_cosecha_estimada)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $cultivo->estado }}">
                                    {{ $cultivo->estado }}
                                </span>
                            </td>
                            <td>
                                @if($cultivo->cantidad_cosechada_kg)
                                    <span style="font-weight: 700; color: #047857;">{{ number_format($cultivo->cantidad_cosechada_kg, 2) }} Kg</span>
                                    <div style="font-size: 0.7rem; color: #64748b;">El {{ \Carbon\Carbon::parse($cultivo->fecha_cosecha_real)->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-muted" style="font-style: italic;">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn-action-primary" title="Registrar Rendimiento">
                                    <i class="fas fa-balance-scale mr-1"></i> Registrar
                                </a>
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

<!-- Modal Registrar Cosecha -->
<div class="modal fade" id="modalCosecha" tabindex="-1" aria-labelledby="modalCosechaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
      <div class="modal-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
        <h5 class="modal-title" id="modalCosechaLabel" style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #16332b;">Registrar Cosecha</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('cosecha.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 24px;">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Cultivo *</label>
                    @if($cultivosParaCosechar->isEmpty())
                        <div class="alert alert-warning" style="border-radius: 10px; font-size: 0.85rem;">No hay cultivos activos para cosechar.</div>
                    @else
                        <select name="id_cultivo" class="form-control" style="border-radius: 10px;" required>
                            <option value="">Seleccionar cultivo</option>
                            @foreach($cultivosParaCosechar as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->codigo }} — {{ $c->lote->nombre ?? 'Sin Lote' }} ({{ ucfirst($c->estado) }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Fecha de cosecha real *</label>
                    <input type="date" name="fecha_cosecha_real" class="form-control" style="border-radius: 10px;" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label class="font-weight-bold" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Cantidad cosechada (kg) *</label>
                <input type="number" name="cantidad_cosechada_kg" step="0.01" min="0.1" class="form-control" style="border-radius: 10px;" placeholder="Ej: 250.5" required>
            </div>

            <div class="form-group mb-3">
                <label class="font-weight-bold" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Observaciones</label>
                <textarea name="observaciones" rows="3" class="form-control" style="border-radius: 10px; resize: none;" placeholder="Condiciones, incidencias..."></textarea>
            </div>
            
            <div class="alert mt-3" style="background-color: #ecfdf5; border: 1px solid #d1fae5; color: #047857; border-radius: 10px; font-size: 0.85rem;">
                <i class="fas fa-info-circle mr-1"></i> Al registrar la cosecha el cultivo se cerrará y el lote quedará <strong>disponible</strong>.
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 16px 24px;">
            <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancelar</button>
            <button type="submit" class="btn btn-success" style="background-color: #10b981; border: none; border-radius: 8px; font-weight: 600;" {{ $cultivosParaCosechar->isEmpty() ? 'disabled' : '' }}>
                Registrar cosecha
            </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
