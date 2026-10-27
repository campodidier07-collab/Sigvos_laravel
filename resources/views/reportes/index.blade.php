@extends('layouts.adminlte')

@section('title', 'Reportes y Estadísticas')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 24px;
    }
    .metric-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .metric-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        margin-bottom: 12px;
    }
    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        font-family: 'Outfit', sans-serif;
    }
    .metric-label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
    }
    .metric-sub {
        font-size: 0.78rem;
        color: #94a3b8;
    }
    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Outfit', sans-serif;
    }
    .chart-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .icon-emerald { background: #dcfce7; color: #10b981; }
    .icon-amber   { background: #fef3c7; color: #f59e0b; }
    .icon-blue    { background: #dbeafe; color: #3b82f6; }
    .icon-purple  { background: #f3e8ff; color: #9333ea; }

    .filter-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px 24px;
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .custom-select-clean, .custom-input-clean {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 0.9rem;
        color: #334155;
    }
    .custom-select-clean:focus, .custom-input-clean:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
    }
    .table-custom thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .table-custom tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
@php
    $logoBase64 = '';
    $logoPath = public_path('img/icono.png');
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    
    $nombresReportes = [
        'productividad_lotes' => 'Productividad entre Lotes',
        'actividades_periodo' => 'Actividades por Periodo',
        'proximas_cosechas'   => 'Próximas Cosechas',
    ];
    $tituloReporteActual = $nombresReportes[$tipoReporte] ?? 'Reporte Agrícola';
@endphp
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Reportes y Estadísticas" 
        subtitle="Analiza la productividad comparativa de lotes, seguimiento operativo de actividades y planificación de cosechas." 
        icon="fa-solid fa-chart-column"
    >
        <div class="d-flex align-items-center">
            @if($tipoReporte === 'productividad_lotes')
                <div class="btn-group mr-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success" style="border-radius: 12px 0 0 12px; font-weight: 600; padding: 10px 18px; border: 1.5px solid #10b981; color: #059669; background: #ffffff;" title="Descargar reporte corporativo en formato Excel (.xlsx)">
                        <i class="fa-solid fa-file-excel mr-2 text-success"></i> Exportar a Excel
                    </a>
                    <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 0 12px 12px 0; border: 1.5px solid #10b981; border-left: none; color: #059669; background: #ffffff;">
                        <span class="sr-only">Opciones de exportación</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                        <a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                            <i class="fa-solid fa-file-excel text-success mr-2"></i> Reporte Excel estilizado (.xlsx)
                        </a>
                        <a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">
                            <i class="fa-solid fa-file-csv text-muted mr-2"></i> Formato datos plano (.csv)
                        </a>
                    </div>
                </div>
            @endif
            <button id="exportPdfBtn" class="btn btn-primary" style="background: #10b981; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(16,185,129,0.2);">
                <i class="fa-solid fa-file-pdf mr-2"></i> Exportar a PDF
            </button>
        </div>
    </x-module-header>

    <!-- PANEL DE FILTROS (Criterio 1 y Criterio 5) -->
    <div class="filter-panel">
        <form method="GET" action="{{ route('reportes.index') }}" id="formFiltroReportes">
            <div class="row align-items-end">
                <!-- Tipo de Reporte (Criterio 1) -->
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="form-label text-xs text-uppercase font-weight-bold text-muted mb-1">
                        <i class="fa-solid fa-layer-group mr-1 text-success"></i> Tipo de Análisis
                    </label>
                    <select name="tipo_reporte" id="tipo_reporte" class="form-control custom-select-clean" onchange="this.form.submit()">
                        <option value="productividad_lotes" {{ $tipoReporte === 'productividad_lotes' ? 'selected' : '' }}>
                            Productividad entre Lotes
                        </option>
                        <option value="actividades_periodo" {{ $tipoReporte === 'actividades_periodo' ? 'selected' : '' }}>
                            Actividades por Periodo
                        </option>
                        <option value="proximas_cosechas" {{ $tipoReporte === 'proximas_cosechas' ? 'selected' : '' }}>
                            Próximas Cosechas
                        </option>
                    </select>
                </div>

                <!-- Filtro por Tipo de Cultivo (Criterio 5) -->
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="form-label text-xs text-uppercase font-weight-bold text-muted mb-1">
                        <i class="fa-solid fa-seedling mr-1 text-success"></i> Tipo de Cultivo
                    </label>
                    <select name="id_tipo_cultivo" id="id_tipo_cultivo" class="form-control custom-select-clean">
                        <option value="">Todos los tipos de cultivo</option>
                        @foreach($tiposCultivo as $tCultivo)
                            <option value="{{ $tCultivo->id }}" {{ (string)$idTipoCultivo === (string)$tCultivo->id ? 'selected' : '' }}>
                                {{ $tCultivo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Inicio -->
                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                    <label class="form-label text-xs text-uppercase font-weight-bold text-muted mb-1">
                        <i class="fa-regular fa-calendar-days mr-1 text-muted"></i> Fecha Inicio
                    </label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}" class="form-control custom-input-clean">
                </div>

                <!-- Fecha Fin -->
                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                    <label class="form-label text-xs text-uppercase font-weight-bold text-muted mb-1">
                        <i class="fa-regular fa-calendar-days mr-1 text-muted"></i> Fecha Fin
                    </label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}" class="form-control custom-input-clean">
                </div>

                <!-- Botones Acción -->
                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 mr-2" style="border-radius: 10px; font-weight: 600; padding: 9px 15px;">
                        <i class="fa-solid fa-filter mr-1"></i> Filtrar
                    </button>
                    @if($idTipoCultivo || $fechaInicio || $fechaFin || $tipoReporte !== 'productividad_lotes')
                        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; padding: 9px 12px;" title="Limpiar Filtros">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- CONTENEDOR EXPORTABLE A PDF (Criterio 4) -->
    <div id="reportes-container" style="padding: 15px; background: #f8fafc; border-radius: 20px;">
        
        <!-- Header del reporte actual -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #1e293b; margin: 0;">
                    <i class="fa-solid {{ $tipoReporte === 'productividad_lotes' ? 'fa-chart-line text-emerald' : ($tipoReporte === 'actividades_periodo' ? 'fa-list-check text-blue' : 'fa-hourglass-half text-amber') }} mr-2"></i>
                    {{ $tituloReporteActual }}
                </h4>
                <p class="text-muted text-sm mb-0">
                    @if($tipoCultivoSeleccionado)
                        Filtro activo: <strong>Tipo de cultivo: {{ $tipoCultivoSeleccionado->nombre }}</strong>
                    @else
                        Mostrando todos los tipos de cultivo
                    @endif
                    @if($fechaInicio || $fechaFin)
                        | Periodo: {{ $fechaInicio ?: 'Inicio' }} a {{ $fechaFin ?: 'Hoy' }}
                    @endif
                </p>
            </div>
            <div>
                <span class="badge badge-light p-2 text-muted" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                    <i class="fa-regular fa-clock mr-1"></i> Generado: {{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        @if($tipoReporte === 'productividad_lotes')
            <!-- ========================================== -->
            <!-- 1. REPORTE PRODUCTIVIDAD ENTRE LOTES (Criterio 1, 5 y 6) -->
            <!-- ========================================== -->
            
            <!-- TARJETAS DE MÉTRICAS CLAVE (Criterio 6) -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-emerald-100 text-emerald-600"><i class="fa-solid fa-seedling"></i></div>
                            <div class="metric-label">Rendimiento Promedio</div>
                            <div class="metric-value">{{ number_format($metricasProductividad['rendimiento_promedio'], 2) }} <span style="font-size: 1rem; font-weight: 500; color: #64748b;">Kg/Ha</span></div>
                        </div>
                        <div class="metric-sub mt-2">Eficiencia global en lotes cosechados</div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-blue-100 text-blue-600"><i class="fa-solid fa-weight-hanging"></i></div>
                            <div class="metric-label">Total Cosechado</div>
                            <div class="metric-value">{{ number_format($metricasProductividad['total_kg'], 1) }} <span style="font-size: 1rem; font-weight: 500; color: #64748b;">Kg</span></div>
                        </div>
                        <div class="metric-sub mt-2">Producción total acumulada</div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-amber-100 text-amber-600"><i class="fa-solid fa-trophy"></i></div>
                            <div class="metric-label">Lote Más Productivo</div>
                            <div class="metric-value" style="font-size: 1.25rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $metricasProductividad['lote_mas_productivo_nombre'] }}">
                                {{ $metricasProductividad['lote_mas_productivo_nombre'] }}
                            </div>
                        </div>
                        <div class="metric-sub mt-2">
                            Líder: <strong>{{ number_format($metricasProductividad['lote_mas_productivo_rendimiento'], 2) }} Kg/Ha</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-purple-100 text-purple-600"><i class="fa-solid fa-map-location-dot"></i></div>
                            <div class="metric-label">Lotes Evaluados</div>
                            <div class="metric-value">{{ $metricasProductividad['total_lotes'] }} <span style="font-size: 1rem; font-weight: 500; color: #64748b;">Lotes</span></div>
                        </div>
                        <div class="metric-sub mt-2">{{ number_format($metricasProductividad['total_ha'], 1) }} Hectáreas analizadas</div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICOS COMPARATIVOS -->
            <div class="row mb-4">
                <!-- Gráfico de Barras: Rendimiento Kg/Ha por Lote -->
                <div class="col-lg-8 mb-4">
                    <div class="glass-card h-100">
                        <div class="chart-title">
                            <div class="chart-icon-box p-2 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-chart-column"></i></div>
                            Comparativa de Rendimiento por Lote (Kg/Ha)
                        </div>
                        <div class="chart-body">
                            @if(empty($datosGraficoLotes['labels']))
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p class="font-weight-bold">No hay registros de cosechas para los filtros seleccionados.</p>
                                </div>
                            @else
                                <canvas id="chartRendimientoLotes" style="min-height: 280px; height: 280px; max-height: 280px; max-width: 100%;"></canvas>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Dona: Distribución de Kg por Lote -->
                <div class="col-lg-4 mb-4">
                    <div class="glass-card h-100">
                        <div class="chart-title">
                            <div class="chart-icon-box p-2 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-chart-pie"></i></div>
                            Distribución de Producción (Kg)
                        </div>
                        <div class="chart-body">
                            @if(empty($datosGraficoLotes['labels']))
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-chart-pie fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p class="font-weight-bold">Sin datos para graficar</p>
                                </div>
                            @else
                                <canvas id="chartProduccionLotes" style="min-height: 280px; height: 280px; max-height: 280px; max-width: 100%;"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA COMPARATIVA DETALLADA DE PRODUCTIVIDAD -->
            <div class="glass-card">
                <div class="chart-title justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="chart-icon-box p-2 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-table"></i></div>
                        Tabla Comparativa de Eficiencia y Rendimiento de Lotes
                    </div>
                    <span class="badge badge-success px-3 py-2" style="font-size: 0.8rem; border-radius: 8px;">
                        {{ count($tablaProductividad) }} lotes registrados
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Área (Ha)</th>
                                <th>Variedad / Tipo Cultivo</th>
                                <th class="text-center">Cosechas</th>
                                <th class="text-right">Total Cosechado</th>
                                <th class="text-right">Rendimiento (Kg/Ha)</th>
                                <th class="text-center">Eficiencia Relativa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tablaProductividad as $loteRow)
                                <tr>
                                    <td>
                                        <strong>{{ $loteRow['identificador'] }}</strong>
                                        <div class="text-muted text-xs">{{ $loteRow['nombre'] }}</div>
                                    </td>
                                    <td>{{ number_format($loteRow['area_ha'], 2) }} Ha</td>
                                    <td>
                                        <span class="text-dark">{{ $loteRow['variedades'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light border">{{ $loteRow['num_cosechas'] }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-slate">
                                        {{ number_format($loteRow['total_kg'], 2) }} Kg
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: #059669; font-size: 0.95rem;">
                                        {{ number_format($loteRow['rendimiento_kg_ha'], 2) }} Kg/Ha
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $loteRow['badge_class'] }} px-2 py-1" style="border-radius: 6px;">
                                            {{ $loteRow['eficiencia'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No se encontraron datos de productividad para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($tipoReporte === 'actividades_periodo')
            <!-- ========================================== -->
            <!-- 2. REPORTE ACTIVIDADES POR PERIODO (Criterio 1 y 5) -->
            <!-- ========================================== -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-blue-100 text-blue-600"><i class="fa-solid fa-clipboard-list"></i></div>
                            <div class="metric-label">Total Actividades</div>
                            <div class="metric-value">{{ $metricasActividades['total'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Programadas en el periodo</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-emerald-100 text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
                            <div class="metric-label">Completadas</div>
                            <div class="metric-value" style="color: #059669;">{{ $metricasActividades['completadas'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Ejecutadas satisfactoriamente</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-amber-100 text-amber-600"><i class="fa-regular fa-clock"></i></div>
                            <div class="metric-label">Pendientes</div>
                            <div class="metric-value" style="color: #d97706;">{{ $metricasActividades['pendientes'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Por realizar o en curso</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-purple-100 text-purple-600"><i class="fa-solid fa-percent"></i></div>
                            <div class="metric-label">% Cumplimiento</div>
                            <div class="metric-value">{{ $metricasActividades['tasa_cumplimiento'] }}%</div>
                        </div>
                        <div class="metric-sub mt-2">Tasa de resolución de tareas</div>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="chart-title">
                    <div class="chart-icon-box p-2 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-list-check"></i></div>
                    Detalle de Actividades Operativas
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Lote / Cultivo</th>
                                <th>Tipo de Cultivo</th>
                                <th>Fecha Programada</th>
                                <th>Responsable</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tablaActividades as $actividad)
                                <tr>
                                    <td>
                                        <strong>{{ $actividad->tipoActividad ? $actividad->tipoActividad->nombre : 'Actividad' }}</strong>
                                        <div class="text-xs text-muted">{{ Str::limit($actividad->descripcion, 40) }}</div>
                                    </td>
                                    <td>
                                        {{ $actividad->cultivo && $actividad->cultivo->lote ? $actividad->cultivo->lote->identificador : 'N/A' }}
                                        <span class="text-xs text-muted">({{ $actividad->cultivo ? $actividad->cultivo->codigo : 'N/A' }})</span>
                                    </td>
                                    <td>
                                        {{ $actividad->cultivo && $actividad->cultivo->variedad && $actividad->cultivo->variedad->tipoCultivo ? $actividad->cultivo->variedad->tipoCultivo->nombre : 'N/A' }}
                                    </td>
                                    <td>{{ $actividad->fecha_programada ? \Carbon\Carbon::parse($actividad->fecha_programada)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $actividad->trabajador ? $actividad->trabajador->nombre : 'Sin asignar' }}</td>
                                    <td class="text-center">
                                        @if($actividad->estado === 'completada')
                                            <span class="badge badge-success px-2 py-1">Completada</span>
                                        @elseif($actividad->estado === 'pendiente')
                                            <span class="badge badge-warning px-2 py-1">Pendiente</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1">Cancelada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay actividades para el periodo y filtros indicados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($tipoReporte === 'proximas_cosechas')
            <!-- ========================================== -->
            <!-- 3. REPORTE PRÓXIMAS COSECHAS (Criterio 1 y 5) -->
            <!-- ========================================== -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-emerald-100 text-emerald-600"><i class="fa-solid fa-seedling"></i></div>
                            <div class="metric-label">Cultivos en Curso</div>
                            <div class="metric-value">{{ $metricasCosechas['total_cultivos'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Sembrados o creciendo</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-amber-100 text-amber-600"><i class="fa-solid fa-boxes-stacked"></i></div>
                            <div class="metric-label">Producción Estimada</div>
                            <div class="metric-value">{{ number_format($metricasCosechas['total_kg_estimados'], 1) }} <span style="font-size: 1rem; color: #64748b;">Kg</span></div>
                        </div>
                        <div class="metric-sub mt-2">Volumen proyectado</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-blue-100 text-blue-600"><i class="fa-regular fa-calendar-check"></i></div>
                            <div class="metric-label">Próximos 30 Días</div>
                            <div class="metric-value">{{ $metricasCosechas['proximos_30_dias'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Cosechas a recolectar pronto</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="metric-card">
                        <div>
                            <div class="metric-icon bg-purple-100 text-purple-600"><i class="fa-solid fa-map"></i></div>
                            <div class="metric-label">Lotes Involucrados</div>
                            <div class="metric-value">{{ $metricasCosechas['total_lotes'] }}</div>
                        </div>
                        <div class="metric-sub mt-2">Lotes con cultivos activos</div>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="chart-title">
                    <div class="chart-icon-box p-2 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fa-regular fa-calendar-days"></i></div>
                    Cronograma de Cosechas Estimadas
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Código Cultivo</th>
                                <th>Lote</th>
                                <th>Tipo de Cultivo / Variedad</th>
                                <th>Fecha Siembra</th>
                                <th>Fecha Estimada Cosecha</th>
                                <th class="text-right">Estimado (Kg)</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tablaProximasCosechas as $cultivo)
                                <tr>
                                    <td><strong>{{ $cultivo->codigo }}</strong></td>
                                    <td>{{ $cultivo->lote ? ($cultivo->lote->identificador . ' - ' . $cultivo->lote->nombre) : 'N/A' }}</td>
                                    <td>
                                        {{ $cultivo->variedad && $cultivo->variedad->tipoCultivo ? $cultivo->variedad->tipoCultivo->nombre : 'N/A' }}
                                        <span class="text-xs text-muted">({{ $cultivo->variedad ? $cultivo->variedad->nombre : 'N/A' }})</span>
                                    </td>
                                    <td>{{ $cultivo->fecha_siembra ? $cultivo->fecha_siembra->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $cultivo->fecha_cosecha_estimada ? $cultivo->fecha_cosecha_estimada->format('d/m/Y') : 'N/A' }}</strong>
                                        @if($cultivo->fecha_cosecha_estimada)
                                            @php
                                                $dias = (int) now()->startOfDay()->diffInDays($cultivo->fecha_cosecha_estimada->startOfDay(), false);
                                            @endphp
                                            <span class="text-xs {{ $dias <= 15 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                ({{ $dias > 0 ? "En $dias días" : ($dias === 0 ? 'Hoy' : "Hace " . abs($dias) . " días") }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: #059669;">
                                        {{ number_format($cultivo->cantidad_estimada_kg, 2) }} Kg
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info px-2 py-1 text-capitalize">{{ $cultivo->estado }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay cultivos sembrados ni pendientes de cosecha para los criterios seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/chart.js/Chart.min.js') }}"></script>
<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
  $(function () {
    @if($tipoReporte === 'productividad_lotes' && !empty($datosGraficoLotes['labels']))
        // 1. Gráfico Rendimiento por Lote (Bar Chart)
        var canvasRendimiento = $('#chartRendimientoLotes');
        if (canvasRendimiento.length) {
            var ctxBar = canvasRendimiento.get(0).getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: @json($datosGraficoLotes['labels']),
                    datasets: [{
                        label: 'Rendimiento (Kg/Ha)',
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1,
                        data: @json($datosGraficoLotes['rendimientos'])
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: { beginAtZero: true },
                            scaleLabel: { display: true, labelString: 'Kg cosechados por Hectárea' }
                        }]
                    }
                }
            });
        }

        // 2. Gráfico Producción por Lote (Doughnut Chart)
        var canvasProduccion = $('#chartProduccionLotes');
        if (canvasProduccion.length) {
            var ctxPie = canvasProduccion.get(0).getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: @json($datosGraficoLotes['labels']),
                    datasets: [{
                        data: @json($datosGraficoLotes['totales_kg']),
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom' }
                }
            });
        }
    @endif

    // --- Export to PDF Logic (Criterio 4) ---
    $('#exportPdfBtn').click(function() {
        var btn = $(this);
        var originalText = btn.html();
        btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Generando...');
        btn.prop('disabled', true);

        var targetElement = document.getElementById('reportes-container'); 
        
        setTimeout(function() {
            html2canvas(targetElement, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(function(canvas) {
                var imgData = canvas.toDataURL('image/png');
                var pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                var pdfWidth = pdf.internal.pageSize.getWidth();
                var pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                
                // 1. Header Background Block
                pdf.setFillColor(22, 51, 43); // Dark Green Theme (#16332b)
                pdf.rect(0, 0, pdfWidth, 24, 'F');
                
                // 2. Add System Logo
                var logoBase64 = '{!! $logoBase64 !!}';
                if (logoBase64) {
                    pdf.addImage(logoBase64, 'PNG', 10, 3, 18, 18);
                }

                // 3. Header Texts
                pdf.setTextColor(255, 255, 255);
                pdf.setFontSize(15);
                pdf.setFont("helvetica", "bold");
                pdf.text("SIGVOS", 32, 11);
                
                pdf.setFontSize(9);
                pdf.setFont("helvetica", "normal");
                pdf.text("Sistema de Gestión Agrícola - Reportes Oficiales", 32, 17);
                
                pdf.setFontSize(8);
                pdf.text("Generado: " + new Date().toLocaleDateString() + " " + new Date().toLocaleTimeString(), pdfWidth - 10, 14, { align: 'right' });
                
                // 4. Main Document Title
                pdf.setTextColor(30, 41, 59);
                pdf.setFontSize(13);
                pdf.setFont("helvetica", "bold");
                var tituloDoc = "{{ strtoupper($tituloReporteActual) }}";
                pdf.text(tituloDoc, pdfWidth / 2, 34, { align: 'center' });
                
                // 5. Colored Dividing Line
                pdf.setDrawColor(16, 185, 129);
                pdf.setLineWidth(0.8);
                pdf.line(pdfWidth / 2 - 45, 37, pdfWidth / 2 + 45, 37);

                // 6. Captured Content
                pdf.addImage(imgData, 'PNG', 8, 42, pdfWidth - 16, pdfHeight * ((pdfWidth - 16) / pdfWidth));
                
                // 7. Footer
                pdf.setFontSize(8);
                pdf.setTextColor(148, 163, 184);
                pdf.text("Documento generado automáticamente por SIGVOS - Módulo de Reportes", pdfWidth / 2, 290, { align: 'center' });
                
                pdf.save('Reporte_Oficial_SIGVOS.pdf');
                
                btn.html(originalText);
                btn.prop('disabled', false);
            }).catch(function(error) {
                console.error("Error generating PDF", error);
                alert("Hubo un error al generar el PDF.");
                btn.html(originalText);
                btn.prop('disabled', false);
            });
        }, 500);
    });
  });
</script>
@endpush
