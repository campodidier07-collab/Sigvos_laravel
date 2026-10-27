@extends('layouts.adminlte')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Dashboard Premium CSS */

    .hero-panel-solid {
        background-color: #164e3b;
        border-radius: 20px;
        padding: 32px 40px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(22, 78, 59, 0.15);
        position: relative;
    }
    .hero-panel-solid .subtitle {
        color: rgba(255,255,255,0.7);
        font-size: 0.72rem;
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
        color: rgba(255,255,255,0.85);
        font-size: 0.9rem;
        max-width: 650px;
        line-height: 1.5;
        margin-bottom: 20px;
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
        backdrop-filter: blur(4px);
    }

    /* Barra de Accesos Rápidos */
    .quick-actions-bar {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .quick-action-btn {
        background: #ffffff;
        color: #164e3b;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        background: #f0fdf4;
        color: #15803d;
        transform: translateY(-2px);
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Tarjetas Estadísticas */
    .stat-card-link {
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .stat-card-link:hover {
        text-decoration: none;
        color: inherit;
    }
    .stat-card-solid {
        background: #ffffff;
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
    }
    .stat-card-solid:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08);
        border-color: #cbd5e1;
    }
    .stat-info-solid {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .stat-info-solid h4 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
        line-height: 1.15;
    }
    .stat-info-solid p {
        margin: 2px 0 0 0;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
    }
    @keyframes statNumberPulse {
        0% { transform: scale(1); }
        35% { transform: scale(1.15); color: #10b981; }
        100% { transform: scale(1); }
    }
    .stat-number-updated {
        display: inline-block;
        animation: statNumberPulse 0.6s ease-out;
    }
    .card-pulse-active {
        border-color: #10b981 !important;
        box-shadow: 0 4px 18px rgba(16, 185, 129, 0.22) !important;
    }

    /* Tablas y Secciones Inferiores */
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
    .link-ver-todos:hover { color: #059669; text-decoration: underline; }

    .clean-table { width: 100%; }
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
    .clean-table tbody tr.clickable-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .clean-table tbody tr.clickable-row:hover {
        background-color: #f0fdf4;
    }

    /* Badges de Estado y Prioridad */
    .status-text {
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .status-text.ok { color: #10b981; }
    .status-text.warn { color: #f59e0b; }
    .status-text.mid { color: #3b82f6; }

    .priority-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        display: inline-block;
    }
    .priority-alta, .priority-urgente { background: #fee2e2; color: #dc2626; }
    .priority-media { background: #fef3c7; color: #d97706; }
    .priority-baja { background: #dbeafe; color: #2563eb; }

    .badge-overdue {
        background: #dc2626;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 4px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    /* Actividades interactivas */
    .activity-item-interactive {
        display: flex;
        gap: 14px;
        margin-bottom: 16px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s, transform 0.2s;
        border: 1px solid transparent;
    }
    .activity-item-interactive:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        transform: translateX(4px);
        text-decoration: none;
        color: inherit;
    }

    /* Notificaciones */
    .notification-item-dashboard {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        margin-bottom: 8px;
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
    }
    .notification-item-dashboard:hover {
        background: #f1f5f9;
        text-decoration: none;
        color: inherit;
    }

    /* Indicador en vivo y banner offline */
    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #065f46;
        background: #ffffff;
        border: 1px solid #bbf7d0;
        padding: 6px 14px;
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.08);
    }
    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .btn-sync-refresh {
        background: transparent;
        border: none;
        color: #059669;
        cursor: pointer;
        padding: 0 2px;
        margin-left: 4px;
        transition: transform 0.3s;
    }
    .btn-sync-refresh:hover { transform: rotate(180deg); }

    .offline-banner-box {
        display: none;
        background: #fffbeb;
        border: 1px solid #f59e0b;
        color: #92400e;
        padding: 12px 20px;
        border-radius: 14px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1);
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="container-fluid px-4 pt-3">

    <!-- Alerta de Sincronización Pendiente Prolongada (HU DP-19 Criterio 7) -->
    <div id="prolongedSyncAlert" class="alert alert-warning mb-3" style="display: none; border-radius: 12px; border: 1px solid #fde68a; background: #fffbeb; padding: 14px 20px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 mr-3" style="font-size: 1.4rem;"></i>
                <div>
                    <strong style="color: #92400e; font-size: 0.95rem;">Alerta de sincronización pendiente prolongada:</strong>
                    <div id="prolongedSyncText" style="color: #78350f; font-size: 0.85rem;">
                        Han pasado varios días sin sincronizar con la nube. Existen datos y respaldos pendientes por sincronizar.
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-warning" onclick="fetchDashboardData()" style="font-weight: 700; border-radius: 8px;">
                <i class="fa-solid fa-arrows-rotate mr-1"></i> Sincronizar ahora
            </button>
        </div>
    </div>

    <!-- Banner Modo Offline (Escenario 6 / HU DP-19 Criterio 4) -->
    <div id="offlineBanner" class="offline-banner-box">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-plane-slash text-amber-600 mr-2" style="font-size: 1.25rem;"></i>
            <div>
                <strong>Modo fuera de línea activo:</strong> Visualizando datos almacenados localmente. Las funciones de consulta y registro local siguen disponibles en modo offline.
            </div>
        </div>
        <span class="badge badge-warning" id="offlineLastCachedText">Caché local</span>
    </div>

    @if(auth()->user()->isAdmin())
    <x-module-header 
        title="Dashboard Administrador" 
        subtitle="Vista completa del sistema SIGVOS y gestión de la finca." 
        icon="fa-solid fa-gauge-high" 
    >
        <!-- Indicador de sincronización en tiempo real (Escenario 3) -->
        <div class="live-status-pill" id="liveSyncStatus">
            <span class="live-dot" id="liveDot"></span>
            <span id="syncStatusText">Actualizado en vivo</span>
            <span id="syncTimeText" class="text-muted ml-1" style="font-size: 0.75rem;">({{ now()->format('H:i') }})</span>
            <button type="button" class="btn-sync-refresh" id="btnManualRefresh" title="Sincronizar ahora">
                <i class="fa-solid fa-arrows-rotate" id="refreshIcon"></i>
            </button>
        </div>
    </x-module-header>

    {{-- Panel Verde Sólido (Hero Admin) con Accesos Rápidos (Escenario 5) --}}
    <div class="hero-panel-solid">
        <div class="subtitle">ADMINISTRACIÓN GENERAL</div>
        <h3>Control total de la finca</h3>
        <p>Gestiona usuarios, cultivos, lotes, actividades y genera reportes estratégicos desde un solo lugar.</p>
        
        <div class="mb-2">
            <div class="hero-pill-solid" id="hero-cultivos">{{ $estadisticas['cultivos_activos'] }} cultivos activos</div>
            <div class="hero-pill-solid" id="hero-productores">{{ $estadisticas['total_trabajadores'] ?? 0 }} productores activos</div>
            <div class="hero-pill-solid" id="hero-pendientes">{{ $estadisticas['actividades_pendientes'] }} actividades pendientes</div>
        </div>

        {{-- Botonera de Acceso Rápido a Funciones Principales (Escenario 5) --}}
        <div class="quick-actions-bar">
            <a href="{{ route('cultivos.create') }}" class="quick-action-btn" title="Registrar un nuevo cultivo">
                <i class="fa-solid fa-circle-plus text-emerald-600"></i> Nuevo Cultivo
            </a>
            <a href="{{ route('actividades.create') }}" class="quick-action-btn" title="Programar una nueva labor">
                <i class="fa-solid fa-calendar-plus text-blue-600"></i> Nueva Actividad
            </a>
            <a href="{{ route('cosecha.index') }}" class="quick-action-btn" title="Registrar recolección de cosecha">
                <i class="fa-solid fa-basket-shopping text-amber-600"></i> Registrar Cosecha
            </a>
            <a href="{{ route('lotes.create') }}" class="quick-action-btn" title="Crear un nuevo lote">
                <i class="fa-solid fa-map-location-dot text-cyan-600"></i> Nuevo Lote
            </a>
            <a href="{{ route('reportes.index') }}" class="quick-action-btn" title="Ir a analítica y reportes">
                <i class="fa-solid fa-chart-line text-purple-600"></i> Reportes
            </a>
        </div>
    </div>
    @else
    <x-module-header 
        title="Panel del Trabajador" 
        subtitle="Bienvenido a tu espacio operativo de labores y actividades de campo." 
        icon="fa-solid fa-house" 
    />

    {{-- Panel Verde Sólido (Hero Trabajador) --}}
    <div class="hero-panel-solid">
        <div class="subtitle">MIS LABORES ASIGNADAS</div>
        <h3>Actividades y Monitoreo</h3>
        <p>Revisa tus lotes asignados, seguimiento a cultivos y registro de avance en tus tareas agrícolas.</p>
        
        <div>
            <div class="hero-pill-solid">{{ $estadisticas['cultivos_activos'] }} cultivos en seguimiento</div>
            <div class="hero-pill-solid">{{ $estadisticas['actividades_pendientes'] }} mis actividades pendientes</div>
            <div class="hero-pill-solid">{{ $estadisticas['actividades_completadas'] }} actividades completadas</div>
        </div>
    </div>
    @endif

    {{-- Tarjetas Estadísticas con Navegación Directa (Escenarios 2 y 5) --}}
    @php
        $cardColClass = auth()->user()->isAdmin() ? 'col-lg-3 col-md-6' : 'col-lg-4 col-md-6';
    @endphp
    <div class="row">
        @if(auth()->user()->isAdmin())
        <div class="{{ $cardColClass }}">
            <a href="{{ route('usuarios.index') }}" class="stat-card-link" title="Ir a Gestión de Usuarios">
                <div class="stat-card-solid" id="stat-card-usuarios">
                    <div class="stat-icon-pastel bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info-solid">
                        <h4 id="stat-usuarios">{{ $estadisticas['total_trabajadores'] ?? 0 }}<span style="font-size: 0.95rem; color: #94a3b8; font-weight: 600;">/{{ $estadisticas['total_usuarios'] ?? 0 }}</span></h4>
                        <p>Productores activos</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <div class="{{ $cardColClass }}">
            <a href="{{ route('cultivos.index') }}" class="stat-card-link" title="Ver listado de Cultivos">
                <div class="stat-card-solid" id="stat-card-cultivos">
                    <div class="stat-icon-pastel bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <div class="stat-info-solid">
                        <h4 id="stat-cultivos">{{ $estadisticas['cultivos_activos'] }}</h4>
                        <p>Cultivos activos</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="{{ $cardColClass }}">
            <a href="{{ route('actividades.index') }}" class="stat-card-link" title="Ver Actividades Pendientes">
                <div class="stat-card-solid" id="stat-card-pendientes">
                    <div class="stat-icon-pastel bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="stat-info-solid">
                        <h4 id="stat-pendientes">{{ $estadisticas['actividades_pendientes'] }}</h4>
                        <p>Actividades pendientes</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="{{ $cardColClass }}">
            <a href="{{ route('lotes.index') }}" class="stat-card-link" title="Ver Lotes de la Finca">
                <div class="stat-card-solid" id="stat-card-lotes">
                    <div class="stat-icon-pastel bg-purple-100 text-purple-600">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="stat-info-solid">
                        <h4 id="stat-lotes">{{ $estadisticas['total_lotes'] }}</h4>
                        <p>Lotes registrados</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Secciones Inferiores: Tablas y Paneles de Control --}}
    <div class="row">
        {{-- Tabla de Cultivos Activos (Navegación rápida a Detalle, Escenario 2) --}}
        <div class="col-xl-8">
            <div class="table-section-card">
                <div class="table-section-header">
                    <div>
                        <h3>Cultivos activos</h3>
                        <p>Haz clic en cualquier cultivo para inspeccionar su detalle</p>
                    </div>
                    <a href="{{ route('cultivos.index') }}" class="link-ver-todos">Ver todos <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="clean-table" id="tablaCultivosDashboard">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Variedad</th>
                                <th>Lote</th>
                                <th>Fecha estimada</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCultivos">
                            @forelse($cosechasProximas->take(5) as $c)
                            <tr class="clickable-row" onclick="window.location='{{ route('cultivo.show', $c) }}'" title="Ver detalle de cultivo {{ $c->codigo }}">
                                <td>
                                    <a href="{{ route('cultivo.show', $c) }}" class="font-weight-bold text-success" onclick="event.stopPropagation();">
                                        {{ $c->codigo }}
                                    </a>
                                </td>
                                <td style="color: #475569; font-weight: 500;">{{ $c->variedad->nombre ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('lotes.show', $c->lote) }}" class="text-secondary" onclick="event.stopPropagation();" title="Ver lote {{ $c->lote->nombre }}">
                                        {{ Str::limit($c->lote->nombre, 15) }}
                                    </a>
                                </td>
                                <td style="color: #64748b;">
                                    <i class="fa-regular fa-calendar-days mr-1"></i> {{ $c->fecha_cosecha_estimada ? $c->fecha_cosecha_estimada->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    @php
                                        $cls = match($c->estado) {
                                             'sembrado' => 'mid',
                                            'desarrollo' => 'warn',
                                            'maduro', 'cosechado' => 'ok',
                                            default => 'mid'
                                        };
                                    @endphp
                                    <span class="status-text {{ $cls }}">{{ ucfirst($c->estado) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No hay cultivos activos en este momento.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panel de Alertas y Notificaciones Importantes (Escenario 4) --}}
            <div class="table-section-card">
                <div class="table-section-header">
                    <div>
                        <h3>Alertas y Notificaciones del Sistema</h3>
                        <p>Mensajes prioritarios y eventos recientes</p>
                    </div>
                    <a href="{{ route('notificaciones.index') }}" class="link-ver-todos">Ir al Centro de Notificaciones <i class="fa-regular fa-bell ml-1"></i></a>
                </div>
                <div class="p-3" id="contenedorNotificaciones">
                    @forelse($notificaciones->take(3) as $notif)
                    <a href="{{ $notif->url ?? route('notificaciones.index') }}" class="notification-item-dashboard">
                        <i class="fa-solid fa-circle-info text-blue-600 mt-1"></i>
                        <div style="flex-grow: 1;">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong style="font-size: 0.85rem; color: #1e293b;">{{ $notif->titulo }}</strong>
                                <span style="font-size: 0.72rem; color: #94a3b8;">{{ $notif->creado_en ? $notif->creado_en->diffForHumans() : 'Reciente' }}</span>
                            </div>
                            <p style="margin: 2px 0 0; font-size: 0.8rem; color: #64748b;">{{ $notif->mensaje }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-3 text-muted" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> No tienes alertas pendientes por atender.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Columna Lateral: Actividades Pendientes e Indicadores de Prioridad (Escenarios 2 y 4) --}}
        <div class="col-xl-4">
            <div class="table-section-card">
                <div class="table-section-header">
                    <div>
                        <h3>Actividades Pendientes</h3>
                        <p>Labores que requieren ejecución</p>
                    </div>
                    <a href="{{ route('actividades.index') }}" class="link-ver-todos">Gestionar <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
                <div class="p-3" id="contenedorActividades">
                    @forelse($actividadesProximas->take(5) as $p)
                    <a href="{{ route('actividades.edit', $p) }}" class="activity-item-interactive" title="Ir a actividad: {{ $p->tipoActividad->nombre ?? 'Actividad' }}">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; margin-top: 6px; flex-shrink: 0;"></div>
                        <div style="flex-grow: 1;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 style="margin: 0; font-size: 0.88rem; font-weight: 700; color: #1e293b;">
                                    {{ $p->tipoActividad->nombre ?? 'Actividad' }}
                                </h5>
                                @php
                                    $prio = $p->prioridad ?? 'media';
                                    $prioCls = match($prio) {
                                        'alta', 'urgente' => 'priority-alta',
                                        'baja' => 'priority-baja',
                                        default => 'priority-media'
                                    };
                                @endphp
                                <span class="priority-badge {{ $prioCls }}">{{ ucfirst($prio) }}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.78rem; color: #64748b;">
                                Lote: <strong>{{ $p->cultivo->lote->identificador ?? $p->cultivo->lote->nombre ?? 'General' }}</strong>
                            </p>
                            <div class="d-flex align-items-center mt-1" style="font-size: 0.75rem; color: #64748b;">
                                <i class="fa-regular fa-calendar-days mr-1"></i> {{ $p->fecha_programada ? $p->fecha_programada->format('d M') : 'Sin fecha' }}
                                @if($p->fecha_programada && $p->fecha_programada->isPast())
                                <span class="badge-overdue ml-2"><i class="fa-regular fa-clock"></i> Atrasada</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-4">
                        <i class="fa-solid fa-check-double text-emerald-600 mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted" style="font-size: 0.85rem; margin: 0;">No hay labores pendientes programadas.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    /**
     * Motor de Sincronización Automática (Escenario 3) y Modo Offline (Escenario 6)
     */
    const CACHE_KEY = 'sigvos_dashboard_admin_cache';
    const ENDPOINT = "{{ route('dashboard.data') }}";

    // Detectar conectividad de red y tipo de conexión (WiFi vs Móvil)
    function updateNetworkStatus() {
        const offlineBanner = document.getElementById('offlineBanner');
        const syncStatusText = document.getElementById('syncStatusText');
        const liveDot = document.getElementById('liveDot');
        const prolongedAlert = document.getElementById('prolongedSyncAlert');

        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const isWifi = !connection || connection.type === 'wifi' || connection.effectiveType === '4g';

        if (!navigator.onLine) {
            if (offlineBanner) offlineBanner.style.display = 'flex';
            if (syncStatusText) syncStatusText.textContent = 'Sin conexión';
            if (liveDot) {
                liveDot.style.backgroundColor = '#f59e0b';
                liveDot.style.boxShadow = 'none';
            }
        } else {
            if (offlineBanner) offlineBanner.style.display = 'none';
            if (syncStatusText) {
                syncStatusText.textContent = isWifi ? 'Sincronizado (WiFi)' : 'Sincronizado (Red Móvil)';
            }
            if (liveDot) {
                liveDot.style.backgroundColor = '#10b981';
            }
        }

        // HU DP-19 Criterio 7: Verificar si han pasado varios días sin sincronizar
        verificarSincronizacionProlongada();
    }

    function verificarSincronizacionProlongada() {
        const prolongedAlert = document.getElementById('prolongedSyncAlert');
        const lastSyncTimestamp = localStorage.getItem('sigvos_last_sync_timestamp');
        if (lastSyncTimestamp) {
            const diasDiferencia = (Date.now() - parseInt(lastSyncTimestamp, 10)) / (1000 * 60 * 60 * 24);
            if (diasDiferencia >= 3 && prolongedAlert) {
                prolongedAlert.style.display = 'block';
            } else if (prolongedAlert) {
                prolongedAlert.style.display = 'none';
            }
        }
    }

    // HU DP-19 Criterio 1 y 5: Sincronización automática al detectar conexión WiFi o volver en línea
    window.addEventListener('online', () => {
        updateNetworkStatus();
        console.log('Conexión reanudada: Sincronización automática de datos con la nube iniciada.');
        fetchDashboardData(true);
    });

    window.addEventListener('offline', () => {
        updateNetworkStatus();
        loadFromLocalStorage();
    });

    // Función principal para obtener y actualizar datos en tiempo real
    let isFetchingDashboard = false;
    async function fetchDashboardData(force = false) {
        if (isFetchingDashboard) return;

        const refreshIcon = document.getElementById('refreshIcon');
        const syncStatusText = document.getElementById('syncStatusText');
        const syncTime = document.getElementById('syncTimeText');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');
        if (syncStatusText && navigator.onLine) syncStatusText.textContent = 'Actualizando...';

        if (!navigator.onLine) {
            loadFromLocalStorage();
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            return;
        }

        isFetchingDashboard = true;
        try {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            const isWifi = force || (!connection || connection.type === 'wifi' || connection.effectiveType === '4g');

            const response = await fetch(`${ENDPOINT}?tipo_red=${isWifi ? 'wifi' : 'movil'}&_t=${Date.now()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Connection-Type': isWifi ? 'wifi' : 'movil'
                }
            });

            if (!response.ok) throw new Error('Error al sincronizar datos');

            const data = await response.json();

            // 1. Guardar en caché local para soporte offline
            try {
                localStorage.setItem(CACHE_KEY, JSON.stringify(data));
                localStorage.setItem('sigvos_last_sync_timestamp', Date.now().toString());
            } catch (e) {}

            // 2. Actualizar KPIs y componentes del DOM
            updateDashboardDOM(data);

            if (syncTime && data.timestamp) {
                syncTime.textContent = `(${data.timestamp})`;
            }

            if (syncStatusText) {
                syncStatusText.textContent = isWifi ? 'Sincronizado (WiFi)' : 'Sincronizado (Red Móvil)';
            }

            const prolongedAlert = document.getElementById('prolongedSyncAlert');
            if (prolongedAlert) prolongedAlert.style.display = 'none';

        } catch (error) {
            console.warn('Fallo de red al sincronizar dashboard:', error);
            if (syncStatusText) syncStatusText.textContent = 'Modo local';
            if (!navigator.onLine) {
                loadFromLocalStorage();
            }
        } finally {
            isFetchingDashboard = false;
            if (refreshIcon) {
                setTimeout(() => refreshIcon.classList.remove('fa-spin'), 500);
            }
        }
    }

    function animateCounter(elementId, newText, isHtml = false, cardId = null) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const current = isHtml ? el.innerHTML.trim() : el.textContent.trim();
        const incoming = (newText || '').toString().trim();
        if (current !== incoming) {
            if (isHtml) el.innerHTML = incoming;
            else el.textContent = incoming;

            el.classList.remove('stat-number-updated');
            void el.offsetWidth;
            el.classList.add('stat-number-updated');

            if (cardId) {
                const card = document.getElementById(cardId);
                if (card) {
                    card.classList.remove('card-pulse-active');
                    void card.offsetWidth;
                    card.classList.add('card-pulse-active');
                    setTimeout(() => card.classList.remove('card-pulse-active'), 1200);
                }
            }
        }
    }

    function updateDashboardDOM(data) {
        if (!data || !data.estadisticas) return;

        const stats = data.estadisticas;

        // Actualizar tarjetas de conteo ejecutivo con animación
        animateCounter('stat-cultivos', stats.cultivos_activos, false, 'stat-card-cultivos');
        animateCounter('stat-pendientes', stats.actividades_pendientes, false, 'stat-card-pendientes');
        animateCounter('stat-lotes', stats.total_lotes, false, 'stat-card-lotes');

        const elUsuarios = document.getElementById('stat-usuarios');
        if (elUsuarios && stats.total_trabajadores !== undefined) {
            const userHtml = `${stats.total_trabajadores}<span style="font-size: 0.95rem; color: #94a3b8; font-weight: 600;">/${stats.total_usuarios || 0}</span>`;
            animateCounter('stat-usuarios', userHtml, true, 'stat-card-usuarios');
        }

        // Hero pills
        const pillCultivos = document.getElementById('hero-cultivos');
        if (pillCultivos) pillCultivos.textContent = `${stats.cultivos_activos} cultivos activos`;

        const pillProductores = document.getElementById('hero-productores');
        if (pillProductores && stats.total_trabajadores !== undefined) {
            pillProductores.textContent = `${stats.total_trabajadores} productores activos`;
        }

        const pillPendientes = document.getElementById('hero-pendientes');
        if (pillPendientes) pillPendientes.textContent = `${stats.actividades_pendientes} actividades pendientes`;

        // Actualizar tabla de cultivos activos si cambió
        if (Array.isArray(data.cosechasProximas)) {
            const tbody = document.getElementById('tbodyCultivos');
            if (tbody) {
                if (data.cosechasProximas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No hay cultivos activos en este momento.</td></tr>';
                } else {
                    tbody.innerHTML = data.cosechasProximas.map(c => `
                        <tr class="clickable-row" onclick="window.location='${c.url}'" title="Ver detalle de cultivo ${c.codigo}">
                            <td>
                                <a href="${c.url}" class="font-weight-bold text-success" onclick="event.stopPropagation();">
                                    ${c.codigo}
                                </a>
                            </td>
                            <td style="color: #475569; font-weight: 500;">${c.variedad || '—'}</td>
                            <td>
                                <a href="/lotes/${c.lote_id || ''}" class="text-secondary" onclick="event.stopPropagation();" title="Ver lote ${c.lote || ''}">
                                    ${c.lote || '—'}
                                </a>
                            </td>
                            <td style="color: #64748b;">
                                <i class="fa-regular fa-calendar-days mr-1"></i> ${c.fecha_cosecha || '—'}
                            </td>
                            <td>
                                <span class="status-text mid">${c.estado ? c.estado.charAt(0).toUpperCase() + c.estado.slice(1) : 'Sembrado'}</span>
                            </td>
                        </tr>
                    `).join('');
                }
            }
        }

        // Actualizar lista de actividades pendientes si cambió
        if (Array.isArray(data.actividadesProximas)) {
            const contenedor = document.getElementById('contenedorActividades');
            if (contenedor) {
                if (data.actividadesProximas.length === 0) {
                    contenedor.innerHTML = '<div class="text-center py-4 text-muted"><i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> No tienes actividades pendientes por atender.</div>';
                } else {
                    contenedor.innerHTML = data.actividadesProximas.slice(0, 5).map(p => `
                        <a href="${p.url}" class="activity-item-interactive" title="Ir a actividad: ${p.nombre}">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; margin-top: 6px; flex-shrink: 0;"></div>
                            <div style="flex-grow: 1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 style="margin: 0; font-size: 0.88rem; font-weight: 700; color: #1e293b;">
                                        ${p.nombre}
                                    </h5>
                                    <span class="badge ${p.prioridad === 'alta' ? 'badge-danger' : 'badge-warning'}" style="font-size: 0.65rem; text-transform: uppercase;">${p.prioridad}</span>
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b;">
                                    <span><i class="fa-solid fa-map-location-dot mr-1"></i> Lote ${p.lote}</span>
                                    <span class="ml-2"><i class="fa-regular fa-calendar-days mr-1"></i> ${p.fecha_programada}</span>
                                </div>
                            </div>
                        </a>
                    `).join('');
                }
            }
        }
    }

    function loadFromLocalStorage() {
        const cached = localStorage.getItem(CACHE_KEY);
        if (cached) {
            try {
                const data = JSON.parse(cached);
                updateDashboardDOM(data);
                const offlineLastCachedText = document.getElementById('offlineLastCachedText');
                if (offlineLastCachedText && data.timestamp) {
                    offlineLastCachedText.textContent = `Última sincronización: ${data.timestamp}`;
                }
            } catch (e) {
                console.error('Error al parsear caché local de dashboard', e);
            }
        }
    }

    // Inicialización y reactividad en tiempo real
    document.addEventListener('DOMContentLoaded', () => {
        updateNetworkStatus();

        // 1. Carga inmediata al montar la vista
        fetchDashboardData(true);

        // 2. Botón manual de refresco
        const btnManual = document.getElementById('btnManualRefresh');
        if (btnManual) {
            btnManual.addEventListener('click', (e) => {
                e.preventDefault();
                fetchDashboardData(true);
            });
        }

        // 3. Polling silencioso en segundo plano cada 8 segundos
        setInterval(() => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                fetchDashboardData();
            }
        }, 8000);

        // 4. Actualización instantánea al regresar a la pestaña o ventana
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                fetchDashboardData(true);
            }
        });

        window.addEventListener('focus', () => {
            if (navigator.onLine) {
                fetchDashboardData(true);
            }
        });

        // 5. Soporte para navegación Back/Forward Cache
        window.addEventListener('pageshow', (event) => {
            if (navigator.onLine) {
                fetchDashboardData(true);
            }
        });

        // 6. Sincronización instantánea entre pestañas cuando se crea/modifica un recurso
        window.addEventListener('storage', (e) => {
            if (e.key === 'sigvos_data_updated') {
                console.log('Detectado cambio de datos en el sistema desde otra pestaña. Actualizando dashboard...');
                fetchDashboardData(true);
            }
        });
    });
</script>
@endpush
