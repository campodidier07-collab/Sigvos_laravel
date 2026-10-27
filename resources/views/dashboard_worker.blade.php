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
        gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        padding: 18px 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .stat-card-worker:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08);
        border-color: #cbd5e1;
    }
    .stat-info-worker {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .stat-info-worker h4 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
        line-height: 1.15;
    }
    .stat-info-worker p {
        margin: 2px 0 0 0;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
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

    /* Indicador en vivo y banner offline */
    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    .offline-banner-worker {
        display: none;
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 20px;
        align-items: center;
        justify-content: space-between;
        font-size: 0.85rem;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Mi Panel" 
        subtitle="Aquí está tu resumen de trabajo de hoy." 
        icon="fa-solid fa-house" 
        bannerTitle="Hola, {{ auth()->user()->nombre }}"
    >
        <div class="d-flex align-items-center" style="gap: 10px;">
            <div class="live-status-pill" id="workerSyncStatus">
                <span class="live-dot" id="workerLiveDot"></span>
                <span id="workerSyncStatusText">Sincronizado</span>
                <small id="workerSyncTimeText" class="text-muted ml-1"></small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" onclick="fetchWorkerData()" title="Sincronizar ahora" style="border-radius: 9999px; gap: 6px; background: #ffffff; border: 1px solid #e2e8f0; padding: 6px 14px;">
                <i class="fa-solid fa-arrows-rotate" id="workerRefreshIcon"></i>
                <span class="d-none d-sm-inline" style="font-size: 0.8rem; font-weight: 600; color: #475569;">Sincronizar ahora</span>
            </button>
        </div>
    </x-module-header>

    <!-- Alerta de Error en Sincronización y Reintento (Criterio 5) -->
    <div id="syncWorkerErrorAlert" class="alert alert-danger mb-3" style="display: none; border-radius: 12px; border: 1px solid #fecaca; background: #fef2f2; padding: 12px 18px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation mr-2 text-danger"></i>
                <span id="syncWorkerErrorMessage" style="font-size: 0.88rem; color: #991b1b; font-weight: 500;">
                    Ocurrió un error durante la sincronización de datos con el servidor.
                </span>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="fetchWorkerData()" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                <i class="fa-solid fa-arrows-rotate mr-1"></i> Reintentar sincronización
            </button>
        </div>
    </div>

    <!-- Banner Modo Offline (Criterio 4 y 6) -->
    <div id="offlineWorkerBanner" class="offline-banner-worker">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-plane-slash fa-lg mr-3 text-warning"></i>
            <div>
                <strong>Modo Offline activado:</strong> Te encuentras sin conexión a internet: Los datos locales están protegidos y se sincronizarán al recuperar la conexión.
            </div>
        </div>
        <span class="badge badge-warning" id="workerOfflineCachedText">Caché local</span>
    </div>

    <!-- Banner Sin Asignaciones Activas (Criterio 5) -->
    @if(($estadisticas['total_lotes'] ?? 0) === 0)
    <div class="alert alert-warning mb-4" id="bannerSinAsignaciones" style="border-radius: 14px; border: 1px solid #fde68a; background: #fffbeb; padding: 18px 24px;">
        <div class="d-flex align-items-center">
            <div class="p-3 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mr-3 flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div>
                <h5 class="font-weight-bold mb-1" style="color: #92400e; font-size: 1.05rem;">Sin asignaciones activas</h5>
                <p class="mb-0" style="color: #78350f; font-size: 0.9rem;">
                    Actualmente no tienes lotes activos asignados bajo tu responsabilidad. El administrador debe asignarte un lote para comenzar a gestionar tus cultivos y actividades.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Hero Panel -->
    <div class="hero-panel-worker">
        <div class="subtitle">RESUMEN DEL DÍA</div>
        <h3>Tus tareas y cultivos asignados</h3>
        <div>
            <span class="hero-pill-worker" id="worker-hero-lotes">{{ $estadisticas['total_lotes'] ?? 0 }} lote(s) asignado(s)</span>
            <span class="hero-pill-worker" id="worker-hero-cultivos">{{ $estadisticas['cultivos_activos'] ?? 0 }} cultivo(s) activo(s)</span>
            <span class="hero-pill-worker" id="worker-hero-actividades">{{ $estadisticas['actividades_pendientes'] ?? 0 }} actividad(es) pendiente(s)</span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-pastel bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="stat-info-worker">
                    <h4 id="worker-stat-lotes">{{ $estadisticas['total_lotes'] ?? 0 }}</h4>
                    <p>Lotes asignados</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-pastel bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div class="stat-info-worker">
                    <h4 id="worker-stat-cultivos">{{ $estadisticas['cultivos_activos'] ?? 0 }}</h4>
                    <p>Cultivos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-worker">
                <div class="stat-icon-pastel bg-amber-100 text-amber-600">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div class="stat-info-worker">
                    <h4 id="worker-stat-actividades">{{ $estadisticas['actividades_pendientes'] ?? 0 }}</h4>
                    <p>Actividades pendientes</p>
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
                    <div id="worker-cultivos-container">
                        @if(count($cosechasProximas) > 0)
                            <div class="table-responsive">
                                <table class="table table-borderless table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th class="text-xs text-muted text-uppercase">Cultivo</th>
                                            <th class="text-xs text-muted text-uppercase">Lote</th>
                                            <th class="text-xs text-muted text-uppercase">Cosecha Estimada</th>
                                            <th class="text-xs text-muted text-uppercase">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="worker-cultivos-tbody">
                                        @foreach($cosechasProximas as $cultivo)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: #1e293b;">
                                                    <a href="{{ route('cultivos.show', $cultivo) }}" style="color: inherit; text-decoration: none;">
                                                        {{ $cultivo->codigo }}
                                                    </a>
                                                </div>
                                                <div style="font-size: 0.75rem; color: #64748b;">{{ $cultivo->variedad->nombre ?? 'N/A' }}</div>
                                            </td>
                                            <td>{{ $cultivo->lote->nombre ?? 'N/A' }}</td>
                                            <td>
                                                <span class="text-warning font-weight-bold" style="font-size: 0.85rem;">
                                                    {{ $cultivo->fecha_cosecha_estimada ? $cultivo->fecha_cosecha_estimada->format('d/m/Y') : '—' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: #ecfdf5; color: #047857; text-transform: uppercase;">{{ ucfirst($cultivo->estado) }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state" id="worker-cultivos-empty">
                                <i class="fa-solid fa-seedling fa-2x mb-2 text-muted"></i>
                                No hay cultivos activos en tus lotes asignados.
                            </div>
                        @endif
                    </div>
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
                    <a href="{{ route('actividades.index') }}" class="link-primary">Ver agenda</a>
                </div>
                <div class="section-card-body">
                    <div id="worker-actividades-container">
                        @if(isset($actividadesHoy) && $actividadesHoy->count() > 0)
                            <ul id="worker-actividades-list" style="list-style: none; padding: 0; margin: 0;">
                                @foreach($actividadesHoy as $act)
                                <li style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px;">
                                    <div class="p-2 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="fa-regular fa-clock"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">{{ $act->tipoActividad->nombre ?? 'Actividad' }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            {{ $act->cultivo->codigo ?? '' }} ({{ $act->cultivo->lote->nombre ?? 'Lote' }})
                                        </div>
                                    </div>
                                    <span class="badge badge-warning text-uppercase" style="font-size: 0.65rem;">Pendiente</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state" id="worker-actividades-empty">
                                <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3" style="width: 48px; height: 48px;">
                                    <i class="fa-solid fa-check text-xl"></i>
                                </div>
                                Sin actividades pendientes hoy
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /**
     * Motor de Sincronización Automática y Soporte Offline para Trabajador (HU DP-8 Criterio 6)
     */
    const WORKER_CACHE_KEY = 'sigvos_dashboard_worker_cache';
    const WORKER_ENDPOINT = "{{ route('dashboard.data') }}";

    function updateWorkerNetworkStatus() {
        const offlineBanner = document.getElementById('offlineWorkerBanner');
        const syncStatusText = document.getElementById('workerSyncStatusText');
        const liveDot = document.getElementById('workerLiveDot');
        const syncErrorAlert = document.getElementById('syncWorkerErrorAlert');

        if (!navigator.onLine) {
            if (offlineBanner) offlineBanner.style.display = 'flex';
            if (syncErrorAlert) syncErrorAlert.style.display = 'none';
            if (syncStatusText) syncStatusText.textContent = 'Sin conexión';
            if (liveDot) {
                liveDot.style.backgroundColor = '#f59e0b';
                liveDot.style.boxShadow = 'none';
            }
        } else {
            if (offlineBanner) offlineBanner.style.display = 'none';
            if (syncStatusText && syncStatusText.textContent === 'Sin conexión') {
                syncStatusText.textContent = 'Sincronizado';
            }
            if (liveDot) {
                liveDot.style.backgroundColor = '#10b981';
            }
        }
    }

    window.addEventListener('online', () => {
        updateWorkerNetworkStatus();
        console.log('Dispositivo en línea: Ejecutando sincronización de registros y estado...');
        fetchWorkerData();
    });

    window.addEventListener('offline', () => {
        updateWorkerNetworkStatus();
        loadWorkerFromLocalStorage();
    });

    async function fetchWorkerData() {
        const refreshIcon = document.getElementById('workerRefreshIcon');
        const syncStatusText = document.getElementById('workerSyncStatusText');
        const liveDot = document.getElementById('workerLiveDot');
        const syncErrorAlert = document.getElementById('syncWorkerErrorAlert');
        const syncErrorMessage = document.getElementById('syncWorkerErrorMessage');

        if (refreshIcon) refreshIcon.classList.add('fa-spin');
        if (syncStatusText && navigator.onLine) syncStatusText.textContent = 'Sincronizando...';

        if (!navigator.onLine) {
            updateWorkerNetworkStatus();
            loadWorkerFromLocalStorage();
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            return;
        }

        try {
            const sep = WORKER_ENDPOINT.includes('?') ? '&' : '?';
            const response = await fetch(`${WORKER_ENDPOINT}${sep}_t=${Date.now()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Error en servidor (${response.status}): No fue posible sincronizar los datos de campo.`);
            }

            const data = await response.json();

            // 1. Persistir en caché local (localStorage) para modo offline
            try {
                localStorage.setItem(WORKER_CACHE_KEY, JSON.stringify(data));
            } catch (e) {}

            // 2. Actualizar vista y estado exitoso
            updateWorkerDOM(data);

            if (syncErrorAlert) syncErrorAlert.style.display = 'none';
            if (syncStatusText) syncStatusText.textContent = 'Sincronizado';
            if (liveDot) {
                liveDot.style.backgroundColor = '#10b981';
            }

            const syncTime = document.getElementById('workerSyncTimeText');
            if (syncTime && data.timestamp) {
                syncTime.textContent = `(${data.timestamp})`;
            }

        } catch (error) {
            console.error('Error al sincronizar datos de trabajador:', error);
            if (syncErrorAlert) {
                syncErrorAlert.style.display = 'block';
                if (syncErrorMessage) {
                    syncErrorMessage.textContent = error.message || 'Ocurrió un error durante la sincronización. Por favor verifica tu señal y reintenta.';
                }
            }
            if (syncStatusText) syncStatusText.textContent = 'Modo local';
            if (liveDot) {
                liveDot.style.backgroundColor = '#ef4444';
            }
            if (!navigator.onLine) {
                loadWorkerFromLocalStorage();
            }
        } finally {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
        }
    }

    function updateWorkerDOM(data) {
        if (!data || !data.estadisticas) return;

        const stats = data.estadisticas;

        // Actualizar contadores
        const elLotes = document.getElementById('worker-stat-lotes');
        if (elLotes) elLotes.textContent = stats.total_lotes;

        const elCultivos = document.getElementById('worker-stat-cultivos');
        if (elCultivos) elCultivos.textContent = stats.cultivos_activos;

        const elActividades = document.getElementById('worker-stat-actividades');
        if (elActividades) elActividades.textContent = stats.actividades_pendientes;

        // Hero pills
        const heroLotes = document.getElementById('worker-hero-lotes');
        if (heroLotes) heroLotes.textContent = `${stats.total_lotes} lote(s) asignado(s)`;

        const heroCultivos = document.getElementById('worker-hero-cultivos');
        if (heroCultivos) heroCultivos.textContent = `${stats.cultivos_activos} cultivo(s) activo(s)`;

        const heroActividades = document.getElementById('worker-hero-actividades');
        if (heroActividades) heroActividades.textContent = `${stats.actividades_pendientes} actividad(es) pendiente(s)`;

        // Control de banner sin asignaciones
        const bannerSin = document.getElementById('bannerSinAsignaciones');
        if (bannerSin) {
            bannerSin.style.display = (stats.total_lotes === 0) ? 'block' : 'none';
        }
    }

    function loadWorkerFromLocalStorage() {
        const cached = localStorage.getItem(WORKER_CACHE_KEY);
        if (cached) {
            try {
                const data = JSON.parse(cached);
                updateWorkerDOM(data);
                const offlineCachedText = document.getElementById('workerOfflineCachedText');
                if (offlineCachedText && data.timestamp) {
                    offlineCachedText.textContent = `Última sincronización: ${data.timestamp}`;
                }
            } catch (e) {
                console.error('Error al parsear caché local de trabajador', e);
            }
        }
    }

    // Inicializar estado de red y programar sondeo reactivo
    document.addEventListener('DOMContentLoaded', () => {
        updateWorkerNetworkStatus();
        if (!navigator.onLine) {
            loadWorkerFromLocalStorage();
        } else {
            fetchWorkerData();
        }

        // 1. Polling silencioso cada 8 segundos
        setInterval(() => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                fetchWorkerData();
            }
        }, 8000);

        // 2. Reactividad al volver a la pestaña o ventana
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                fetchWorkerData();
            }
        });

        window.addEventListener('focus', () => {
            if (navigator.onLine) fetchWorkerData();
        });

        window.addEventListener('pageshow', () => {
            if (navigator.onLine) fetchWorkerData();
        });

        // 3. Sincronización entre pestañas
        window.addEventListener('storage', (e) => {
            if (e.key === 'sigvos_data_updated' && navigator.onLine) {
                fetchWorkerData();
            }
        });
    });
</script>
@endpush
