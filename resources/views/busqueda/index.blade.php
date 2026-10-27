@extends('layouts.adminlte')

@section('title', 'Buscador Global y Filtros Avanzados')

@push('styles')
<style>
    .glass-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        padding: 24px;
        margin-bottom: 24px;
    }
    .search-pill-history {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: #334155;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    .search-pill-history:hover {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    .result-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .result-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Buscador Global y Filtros Avanzados" 
        subtitle="Localiza cultivos, actividades y lotes específicos en un solo lugar sin navegar entre pantallas." 
        icon="fa-solid fa-magnifying-glass"
    />

    <!-- Formulario de Búsqueda y Filtros Combinados -->
    <div class="glass-card">
        <form method="GET" action="{{ route('busqueda.index') }}" id="formBusquedaAvanzada">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label for="campo_q" class="font-weight-bold text-dark mb-1">Palabra clave / Nombre de cultivo *</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        </div>
                        <input type="text" name="q" id="campo_q" class="form-control" 
                               placeholder="Ej: CUL-2026, Maíz, Los Pinos, Riego..." 
                               value="{{ $q }}" style="border-radius: 0 8px 8px 0;">
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="filtro_lote" class="font-weight-bold text-dark mb-1">Filtrar por Lote Específico</label>
                    <select name="id_lote" id="filtro_lote" class="form-control" style="border-radius: 8px;">
                        <option value="">Todos los lotes...</option>
                        @foreach($lotes as $l)
                            <option value="{{ $l->id }}" {{ $idLote == $l->id ? 'selected' : '' }}>
                                Lote {{ $l->identificador }} - {{ $l->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="filtro_estado" class="font-weight-bold text-dark mb-1">Estado de Cultivo</label>
                    <select name="estado" id="filtro_estado" class="form-control" style="border-radius: 8px;">
                        <option value="">Todos los estados...</option>
                        @foreach($estadosCultivo as $est)
                            <option value="{{ $est }}" {{ $estadoCultivo == $est ? 'selected' : '' }}>
                                {{ ucfirst($est) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="fecha_desde" class="font-weight-bold text-dark mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ $fechaDesde }}" style="border-radius: 8px;">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="fecha_hasta" class="font-weight-bold text-dark mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ $fechaHasta }}" style="border-radius: 8px;">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="tipo_entidad" class="font-weight-bold text-dark mb-1">Tipo de Información</label>
                    <select name="tipo" id="tipo_entidad" class="form-control" style="border-radius: 8px;">
                        <option value="todos" {{ $tipoEntidad == 'todos' ? 'selected' : '' }}>Todos los registros</option>
                        <option value="cultivos" {{ $tipoEntidad == 'cultivos' ? 'selected' : '' }}>Solo Cultivos</option>
                        <option value="actividades" {{ $tipoEntidad == 'actividades' ? 'selected' : '' }}>Solo Actividades</option>
                        <option value="lotes" {{ $tipoEntidad == 'lotes' ? 'selected' : '' }}>Solo Lotes</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-success flex-grow-1 font-weight-bold" style="border-radius: 8px;">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
                    </button>
                    @if($haBuscado)
                        <a href="{{ route('busqueda.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;" title="Limpiar filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Historial de Búsquedas Recientes (Criterio 7) -->
            <div id="seccionHistorial" class="mt-2 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-clock-rotate-left text-success mr-1"></i> Búsquedas Recientes
                    </span>
                    <button type="button" class="btn btn-xs btn-link text-muted p-0" onclick="limpiarHistorialBusquedas()" style="font-size: 0.75rem;">
                        Borrar historial
                    </button>
                </div>
                <div id="contenedorHistorial">
                    <!-- Se llena vía JavaScript -->
                </div>
            </div>
        </form>
    </div>

    <!-- Resultados de Búsqueda -->
    @if($haBuscado)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="font-weight-bold text-dark m-0" style="font-size: 1.25rem;">
                Resultados de la búsqueda ({{ $totalResultados }})
            </h4>
            @if(!empty($q) || !empty($idLote) || !empty($estadoCultivo) || !empty($fechaDesde) || !empty($fechaHasta))
                <div class="text-sm text-muted">
                    Filtros activos:
                    @if(!empty($q)) <span class="badge badge-light border">Texto: "{{ $q }}"</span> @endif
                    @if(!empty($idLote)) <span class="badge badge-light border">Lote ID: {{ $idLote }}</span> @endif
                    @if(!empty($estadoCultivo)) <span class="badge badge-light border">Estado: {{ ucfirst($estadoCultivo) }}</span> @endif
                    @if(!empty($fechaDesde) || !empty($fechaHasta)) 
                        <span class="badge badge-light border">Rango: {{ $fechaDesde ?: '...' }} a {{ $fechaHasta ?: '...' }}</span> 
                    @endif
                </div>
            @endif
        </div>

        @if($totalResultados === 0)
            <!-- Criterio 6: Búsqueda sin resultados -->
            <div class="glass-card text-center py-5">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px;">
                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                </div>
                <h4 class="font-weight-bold text-dark mb-2">No se encontraron resultados</h4>
                <p class="text-muted mb-4" style="max-width: 500px; margin-inline: auto;">
                    No se encontraron registros que coincidan con los criterios de búsqueda seleccionados. Intenta modificar los términos, verificar las fechas o limpiar algunos filtros.
                </p>
                <a href="{{ route('busqueda.index') }}" class="btn btn-outline-primary" style="border-radius: 8px;">
                    <i class="fa-solid fa-filter mr-1"></i> Restablecer todos los filtros
                </a>
            </div>
        @else
            <!-- Bloques de Resultados -->
            <div class="row">
                <!-- Cultivos Encontrados (Criterios 1, 3, 4, 5) -->
                @if($cultivos->isNotEmpty())
                <div class="col-lg-6 mb-4">
                    <div class="card h-100" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-seedling text-success mr-2"></i> Cultivos ({{ $cultivos->count() }})</span>
                            <span class="badge badge-success">{{ $cultivos->count() }}</span>
                        </div>
                        <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                            @foreach($cultivos as $c)
                            <div class="result-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="font-weight-bold mb-1">
                                            <a href="{{ route('cultivos.show', $c) }}" class="text-dark text-decoration-none">
                                                {{ $c->codigo }}
                                            </a>
                                        </h5>
                                        <span class="text-muted text-sm">
                                            {{ $c->variedad->tipoCultivo->nombre ?? 'Cultivo' }} - {{ $c->variedad->nombre ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="status-badge badge-{{ $c->estado == 'sembrado' ? 'info' : ($c->estado == 'cosechado' ? 'success' : 'secondary') }}">
                                        {{ ucfirst($c->estado) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted text-sm pt-2 border-top">
                                    <span><i class="fa-solid fa-location-dot text-danger mr-1"></i> Lote {{ $c->lote->identificador ?? '—' }} - {{ $c->lote->nombre ?? '—' }}</span>
                                    <span><i class="fa-regular fa-calendar-days mr-1"></i> Siembra: {{ $c->fecha_siembra->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actividades Encontradas (Criterios 2, 3, 4) -->
                @if($actividades->isNotEmpty())
                <div class="col-lg-6 mb-4">
                    <div class="card h-100" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-list-check text-primary mr-2"></i> Actividades ({{ $actividades->count() }})</span>
                            <span class="badge badge-primary">{{ $actividades->count() }}</span>
                        </div>
                        <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                            @foreach($actividades as $a)
                            <div class="result-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="font-weight-bold mb-1">
                                            <a href="{{ route('actividades.show', $a) }}" class="text-dark text-decoration-none">
                                                {{ $a->tipoActividad->nombre ?? 'Actividad' }}
                                            </a>
                                        </h5>
                                        <p class="text-muted text-sm m-0">{{ Str::limit($a->descripcion, 75) }}</p>
                                    </div>
                                    <span class="status-badge badge-{{ $a->estado == 'pendiente' ? 'warning' : 'success' }}">
                                        {{ ucfirst($a->estado) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted text-sm pt-2 border-top">
                                    <span><i class="fa-solid fa-seedling text-success mr-1"></i> {{ $a->cultivo->codigo ?? '—' }} (Lote {{ $a->cultivo->lote->identificador ?? '—' }})</span>
                                    <span><i class="fa-regular fa-clock mr-1"></i> {{ $a->fecha_programada ? $a->fecha_programada->format('d/m/Y') : 'Sin fecha' }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Lotes Encontrados -->
                @if($lotesResultado->isNotEmpty())
                <div class="col-lg-12 mb-4">
                    <div class="card" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-layer-group text-info mr-2"></i> Lotes ({{ $lotesResultado->count() }})</span>
                            <span class="badge badge-info">{{ $lotesResultado->count() }}</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                @foreach($lotesResultado as $lr)
                                <div class="col-md-4 mb-2">
                                    <div class="result-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="font-weight-bold m-0">
                                                <a href="{{ route('lotes.show', $lr) }}" class="text-dark text-decoration-none">
                                                    Lote {{ $lr->identificador }} - {{ $lr->nombre }}
                                                </a>
                                            </h5>
                                            <span class="status-badge badge-{{ $lr->estado == 'disponible' ? 'success' : 'info' }}">
                                                {{ ucfirst($lr->estado) }}
                                            </span>
                                        </div>
                                        <p class="text-muted text-sm m-0"><i class="fa-solid fa-location-dot mr-1"></i> {{ $lr->ubicacion }}</p>
                                        <small class="text-muted d-block mt-1">Superficie: {{ number_format($lr->area_ha, 2) }} ha</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        @endif
    @endif

</div>
@endsection

@push('scripts')
<script>
    const HISTORIAL_STORAGE_KEY = 'sigvos_busquedas_recientes';

    function obtenerHistorial() {
        try {
            return JSON.parse(localStorage.getItem(HISTORIAL_STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function guardarEnHistorial(termino) {
        if (!termino || termino.trim().length === 0) return;
        termino = termino.trim();
        let historial = obtenerHistorial();
        historial = historial.filter(item => item.toLowerCase() !== termino.toLowerCase());
        historial.unshift(termino);
        if (historial.length > 8) historial = historial.slice(0, 8);
        localStorage.setItem(HISTORIAL_STORAGE_KEY, JSON.stringify(historial));
    }

    function renderizarHistorial() {
        const contenedor = document.getElementById('contenedorHistorial');
        if (!contenedor) return;

        const historial = obtenerHistorial();
        if (historial.length === 0) {
            contenedor.innerHTML = '<span class="text-muted text-sm" style="font-style: italic;">No tienes búsquedas recientes todavía.</span>';
            return;
        }

        contenedor.innerHTML = historial.map(item => `
            <span class="search-pill-history" onclick="aplicarBusquedaReciente('${item.replace(/'/g, "\\'")}')">
                <i class="fa-solid fa-magnifying-glass mr-1 text-muted" style="font-size: 0.7rem;"></i> ${item}
            </span>
        `).join('');
    }

    function aplicarBusquedaReciente(termino) {
        const inputQ = document.getElementById('campo_q');
        if (inputQ) {
            inputQ.value = termino;
            document.getElementById('formBusquedaAvanzada').submit();
        }
    }

    function limpiarHistorialBusquedas() {
        localStorage.removeItem(HISTORIAL_STORAGE_KEY);
        renderizarHistorial();
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderizarHistorial();

        const form = document.getElementById('formBusquedaAvanzada');
        if (form) {
            form.addEventListener('submit', () => {
                const qValue = document.getElementById('campo_q')?.value;
                if (qValue) {
                    guardarEnHistorial(qValue);
                }
            });
        }
    });
</script>
@endpush
