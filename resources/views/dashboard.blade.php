@extends('layouts.adminlte')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Estilos del Dashboard Premium (Screenshot style) */
    .dashboard-header-title {
        margin-top: 10px;
        margin-bottom: 20px;
    }
    .dashboard-header-title h2 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #1e293b;
        font-size: 1.75rem;
        margin-bottom: 4px;
    }
    .dashboard-header-title p {
        color: #64748b;
        font-size: 0.875rem;
        margin: 0;
    }

    .hero-panel-solid {
        background-color: #164e3b;
        border-radius: 20px;
        padding: 32px 40px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(22, 78, 59, 0.15);
    }
    .hero-panel-solid .subtitle {
        color: rgba(255,255,255,0.6);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .hero-panel-solid h3 {
        font-size: 1.75rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        margin-bottom: 12px;
    }
    .hero-panel-solid p {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        max-width: 600px;
        line-height: 1.5;
        margin-bottom: 24px;
    }
    .hero-pill-solid {
        background: rgba(255,255,255,0.15);
        padding: 6px 16px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-right: 12px;
        margin-bottom: 8px;
    }

    .stat-card-solid {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        transition: transform 0.2s;
    }
    .stat-card-solid:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }
    .stat-icon-solid {
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
    /* Colores de íconos sólidos */
    .icon-blue { background-color: #3b82f6; }
    .icon-green { background-color: #10b981; }
    .icon-orange { background-color: #f59e0b; }
    .icon-purple { background-color: #8b5cf6; }

    .stat-info-solid h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
        line-height: 1.2;
    }
    .stat-info-solid p {
        margin: 0;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Tablas inferiores */
    .table-section-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .table-section-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-section-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
    }
    .table-section-header p {
        margin: 0;
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 2px;
    }
    .link-ver-todos {
        font-size: 0.8rem;
        font-weight: 700;
        color: #10b981;
        text-decoration: none;
    }
    .link-ver-todos:hover { color: #059669; }

    .clean-table {
        width: 100%;
    }
    .clean-table th {
        background: #ffffff;
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 12px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .clean-table td {
        padding: 16px 24px;
        font-size: 0.875rem;
        border-bottom: 1px solid #f8fafc;
        vertical-align: middle;
    }
    .clean-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .status-text {
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .status-text.ok { color: #10b981; }
    .status-text.warn { color: #f59e0b; }
    .status-text.mid { color: #3b82f6; }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    
    {{-- Título Fuera del Panel --}}
    <div class="dashboard-header-title">
        <h2>Bienvenido, {{ explode(' ', auth()->user()->nombre)[0] }}</h2>
        <p>Vista completa del sistema SIGVOS.</p>
    </div>

    {{-- Panel Verde Sólido (Hero) --}}
    <div class="hero-panel-solid">
        <div class="subtitle">ADMINISTRACIÓN GENERAL</div>
        <h3>Control total de la finca</h3>
        <p>Gestiona usuarios, cultivos, lotes, actividades y genera reportes estratégicos desde un solo lugar.</p>
        
        <div>
            <div class="hero-pill-solid">{{ $estadisticas['cultivos_activos'] }} cultivos activos</div>
            <div class="hero-pill-solid">{{ $estadisticas['total_trabajadores'] ?? 0 }} productores activos</div>
            <div class="hero-pill-solid">{{ $estadisticas['actividades_pendientes'] }} actividades pendientes</div>
        </div>
    </div>

    {{-- Tarjetas Cuadradas Blancas --}}
    <div class="row">
        @if(auth()->user()->isAdmin())
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-solid">
                <div class="stat-icon-solid icon-blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info-solid">
                    <h4>{{ $estadisticas['total_trabajadores'] ?? 0 }}<span style="font-size: 1rem; color: #94a3b8; font-weight: 600;">/{{ $estadisticas['total_usuarios'] ?? 0 }}</span></h4>
                    <p>Productores activos</p>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-solid">
                <div class="stat-icon-solid icon-green">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="stat-info-solid">
                    <h4>{{ $estadisticas['cultivos_activos'] }}</h4>
                    <p>Cultivos activos</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-solid">
                <div class="stat-icon-solid icon-orange">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="stat-info-solid">
                    <h4>{{ $estadisticas['actividades_pendientes'] }}</h4>
                    <p>Actividades pendientes</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-solid">
                <div class="stat-icon-solid icon-purple">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info-solid">
                    <h4>{{ $estadisticas['total_lotes'] }}</h4>
                    <p>Cosechas este mes</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Secciones Inferiores --}}
    <div class="row mt-2">
        <div class="col-xl-8">
            <div class="table-section-card">
                <div class="table-section-header">
                    <div>
                        <h3>Cultivos activos</h3>
                        <p>Vista resumida de lotes productivos</p>
                    </div>
                    <a href="{{ route('cultivos.index') }}" class="link-ver-todos">Ver todos</a>
                </div>
                <div class="table-responsive">
                    <table class="clean-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Variedad</th>
                                <th>Lote</th>
                                <th>Fecha estimada</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cosechasProximas->take(5) as $c)
                            <tr>
                                <td style="color: #10b981; font-weight: 700;">{{ $c->codigo }}</td>
                                <td style="color: #475569; font-weight: 500;">{{ $c->variedad->nombre ?? '—' }}</td>
                                <td style="color: #64748b;">{{ Str::limit($c->lote->nombre, 15) }}</td>
                                <td style="color: #64748b;"><i class="far fa-calendar-alt mr-1"></i> {{ $c->fecha_cosecha_estimada->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                        $cls = match($c->estado) {
                                            'sembrado' => 'mid',
                                            'desarrollo' => 'warn',
                                            'maduro' => 'ok',
                                            'cosechado' => 'ok',
                                            default => 'mid'
                                        };
                                    @endphp
                                    <span class="status-text {{ $cls }}">{{ ucfirst($c->estado) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No hay cultivos activos.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="table-section-card">
                <div class="table-section-header">
                    <div>
                        <h3>Pendientes</h3>
                        <p>Actividades que requieren atención</p>
                    </div>
                </div>
                <div class="p-4">
                    @forelse($actividadesProximas->take(4) as $p)
                    <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; margin-top: 6px;"></div>
                        <div>
                            <h5 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: #1e293b;">{{ $p->tipoActividad->nombre ?? 'Actividad' }}</h5>
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Lote {{ $p->cultivo->lote->identificador ?? '?' }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #ef4444; font-weight: 600;">
                                <i class="far fa-calendar-alt"></i> {{ $p->fecha_programada->format('d M') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <p class="text-muted" style="font-size: 0.9rem;">Sin actividades pendientes.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
