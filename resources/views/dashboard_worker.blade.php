@extends('layouts.adminlte')

@section('title', 'Mi Panel')

@push('styles')
<style>
    /* Dashboard Trabajador CSS */

    .hero-panel-worker {
        background-color: #277953;
        background: linear-gradient(135deg, #1b543b 0%, #277953 100%);
        border-radius: 20px;
        padding: 32px 40px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(39, 121, 83, 0.2);
    }
    .hero-panel-worker .subtitle {
        color: rgba(255,255,255,0.7);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .hero-panel-worker h3 {
        font-size: 2rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        margin-bottom: 20px;
    }
    .hero-pill-worker {
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        margin-right: 12px;
        margin-bottom: 8px;
        backdrop-filter: blur(5px);
    }

    .stat-card-worker {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        transition: transform 0.2s;
    }
    .stat-card-worker:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }
    .stat-icon-worker {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-info-worker h4 {
        margin: 0 0 4px 0;
        font-size: 1.75rem;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
        line-height: 1;
    }
    .stat-info-worker p {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .section-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        height: 100%;
        min-height: 300px;
    }
    .section-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 16px;
    }
    .section-card-title h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }
    .section-card-title p {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0;
    }
    .link-primary {
        color: #10b981;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }
    .link-primary:hover {
        color: #059669;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 150px;
        color: #94a3b8;
        font-size: 0.95rem;
    }
    .empty-state-icon {
        width: 48px;
        height: 48px;
        background: #ecfdf5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #34d399;
        font-size: 1.5rem;
        margin-bottom: 12px;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Mi Panel" 
        subtitle="Aquí está tu resumen de trabajo de hoy." 
        icon="fa-home" 
        bannerTitle="Hola, {{ auth()->user()->nombre }}"
    />

    <!-- Hero Panel -->
    <div class="hero-panel-worker">
        <div class="subtitle">RESUMEN DEL DÍA</div>
        <h3>Tus tareas y cultivos asignados</h3>
        <div>
            <span class="hero-pill-worker">{{ $estadisticas['total_lotes'] ?? 0 }} lote(s) asignado(s)</span>
            <span class="hero-pill-worker">{{ $estadisticas['cultivos_activos'] ?? 0 }} cultivo(s) activo(s)</span>
            <span class="hero-pill-worker">{{ $estadisticas['actividades_pendientes'] ?? 0 }} actividad(es) hoy</span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-worker" style="background-color: #10b981;">
                    <i class="fas fa-map"></i>
                </div>
                <div class="stat-info-worker">
                    <h4>{{ $estadisticas['total_lotes'] ?? 0 }}</h4>
                    <p>Lotes asignados</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-worker" style="background-color: #10b981;">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="stat-info-worker">
                    <h4>{{ $estadisticas['cultivos_activos'] ?? 0 }}</h4>
                    <p>Cultivos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-worker" style="background-color: #f59e0b;">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="stat-info-worker">
                    <h4>{{ $estadisticas['actividades_pendientes'] ?? 0 }}</h4>
                    <p>Actividades hoy</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <h3>Mis cultivos</h3>
                        <p>Lotes bajo tu responsabilidad</p>
                    </div>
                    <a href="{{ route('cultivos.index') }}" class="link-primary">Ver todos</a>
                </div>
                <div class="section-card-body">
                    @if(count($cosechasProximas) > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-xs text-muted text-uppercase">Cultivo</th>
                                        <th class="text-xs text-muted text-uppercase">Lote</th>
                                        <th class="text-xs text-muted text-uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cosechasProximas as $cultivo)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #1e293b;">{{ $cultivo->codigo }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $cultivo->variedad->nombre ?? 'N/A' }}</div>
                                        </td>
                                        <td>{{ $cultivo->lote->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge" style="background-color: #ecfdf5; color: #047857;">{{ ucfirst($cultivo->estado) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            No hay cultivos activos en tus lotes.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <h3>Actividades de hoy</h3>
                        <p>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="section-card-body">
                    @php
                        $actividadesHoy = $actividadesProximas->filter(function($act) {
                            return \Carbon\Carbon::parse($act->fecha_programada)->isToday();
                        });
                    @endphp

                    @if($actividadesHoy->count() > 0)
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            @foreach($actividadesHoy as $act)
                            <li style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">{{ $act->tipoActividad->nombre ?? 'Actividad' }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $act->cultivo->codigo ?? '' }} ({{ $act->cultivo->lote->nombre ?? '' }})</div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            Sin actividades pendientes hoy
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
