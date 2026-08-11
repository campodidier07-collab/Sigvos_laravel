@extends('layouts.adminlte')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Estilos migrados de Gsigvos (admin.php) */
    .hero-panel {
        background: linear-gradient(135deg, #1d4533 0%, #2b845a 100%);
        border-radius: 24px;
        padding: 32px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(43,132,90,0.2);
        margin-bottom: 24px;
        margin-top: 20px;
    }
    .hero-panel::after {
        content: "";
        position: absolute;
        right: -60px;
        bottom: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .hero-pill {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 4px 16px;
        border-radius: 999px;
        font-size: 0.75rem;
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 4px;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(58, 165, 116, 0.15);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 30px rgba(29, 69, 51, 0.04);
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .glass-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(29, 69, 51, 0.08);
    }
    .glass-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.125rem;
        flex-shrink: 0;
    }
    .glass-text-h4 {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.1;
        color: #1d4533;
        font-family: 'Outfit', sans-serif;
    }
    .glass-text-p {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
        margin-top: 2px;
    }
    
    .custom-table-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(58, 165, 116, 0.15);
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(29, 69, 51, 0.04);
        margin-bottom: 24px;
    }
    .custom-table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #d8eee4;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .custom-table-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #16332b;
    }
    .custom-table-header p {
        margin: 0;
        margin-top: 2px;
        font-size: 0.75rem;
        color: #64748b;
    }
    .custom-table-link {
        font-size: 0.75rem;
        font-weight: 600;
        color: #0f8f67;
        text-decoration: none;
    }
    .custom-table-link:hover { text-decoration: underline; color: #0b6b4f; }
    
    .table-agro { width: 100%; margin: 0; }
    .table-agro th {
        background-color: #f5fbf8;
        color: #6b9e8a;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #d8eee4;
        padding: 12px 24px;
        border-top: none;
    }
    .table-agro td {
        vertical-align: middle;
        border-bottom: 1px solid #eef4f1;
        font-size: 0.875rem;
        padding: 16px 24px;
    }
    .table-agro tbody tr:hover {
        background-color: #f5fbf8;
        transition: background-color 0.2s;
    }
    
    .status-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-ok { background:#e1f6e8; color:#25694a; } 
    .status-mid { background:#eef7ff; color:#2563eb; } 
    .status-warn { background:#fff6df; color:#b7791f; }
    
    .mini-dot { width:10px; height:10px; border-radius:999px; margin-top:5px; flex-shrink:0; }
    .list-item-custom {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
    }
    .list-item-custom:not(:last-child) { border-bottom: 1px solid #eef4f1; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="container-fluid px-4">
{{-- ══ PANEL HERO (Bienvenida) ════════════════════════════════════════════════ --}}
<div class="hero-panel">
    <div style="position: relative; z-index: 10;">
        <p style="color: rgba(255,255,255,0.7); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
            Administración General
        </p>
        <h3 style="font-size: 1.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; margin-bottom: 12px; line-height: 1.2;">
            Bienvenido, {{ auth()->user()->nombre }}
        </h3>
        <p style="color: rgba(255,255,255,0.8); font-size: 0.875rem; margin-bottom: 20px; max-width: 600px; line-height: 1.5;">
            Control total de tu finca. Gestiona usuarios, cultivos, lotes y actividades desde un solo lugar.
        </p>
        <div>
            <span class="hero-pill"><strong>{{ $estadisticas['cultivos_activos'] }}</strong> cultivos activos</span>
            <span class="hero-pill"><strong>{{ $estadisticas['actividades_pendientes'] }}</strong> actividades pendientes</span>
            @if(auth()->user()->isAdmin())
            <span class="hero-pill"><strong>{{ $estadisticas['total_usuarios'] ?? 0 }}</strong> usuarios en el sistema</span>
            @endif
        </div>
    </div>
</div>

{{-- ══ FILA 1: Tarjetas de Cristal (KPIs) ═════════════════════════════════════ --}}
<div class="row">
    @if(auth()->user()->isAdmin())
    <div class="col-lg-3 col-sm-6">
        <div class="glass-card">
            <div class="glass-icon-box" style="background: linear-gradient(135deg, #1d4ed8, #60a5fa);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h4 class="glass-text-h4">
                    {{ $estadisticas['total_trabajadores'] ?? 0 }}
                    <span style="font-size: 0.9rem; font-weight: 400; color: #94a3b8;">/{{ $estadisticas['total_usuarios'] ?? 0 }}</span>
                </h4>
                <p class="glass-text-p">Productores activos</p>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-3 col-sm-6">
        <div class="glass-card">
            <div class="glass-icon-box" style="background: linear-gradient(135deg, #1d4ed8, #60a5fa);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h4 class="glass-text-h4">{{ $estadisticas['actividades_completadas'] }}</h4>
                <p class="glass-text-p">Act. Completadas</p>
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-3 col-sm-6">
        <div class="glass-card">
            <div class="glass-icon-box" style="background: linear-gradient(135deg, #0b6b4f, #18b57a);">
                <i class="fas fa-seedling"></i>
            </div>
            <div>
                <h4 class="glass-text-h4">{{ $estadisticas['cultivos_activos'] }}</h4>
                <p class="glass-text-p">Cultivos activos</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="glass-card">
            <div class="glass-icon-box" style="background: linear-gradient(135deg, #b45309, #fbbf24);">
                <i class="fas fa-bolt"></i>
            </div>
            <div>
                <h4 class="glass-text-h4">{{ $estadisticas['actividades_pendientes'] }}</h4>
                <p class="glass-text-p">Pendientes</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="glass-card">
            <div class="glass-icon-box" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div>
                <h4 class="glass-text-h4">{{ $estadisticas['total_lotes'] }}</h4>
                <p class="glass-text-p">Lotes totales</p>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILA 2: Tablas y Listas ═══════════════════════════════════════════════ --}}
<div class="row">

    {{-- Cultivos y Cosechas (Columna Izquierda) --}}
    <div class="col-xl-8">
        <div class="custom-table-card">
            <div class="custom-table-header">
                <div>
                    <h3>Cosechas próximas (30 días)</h3>
                    <p>Vista resumida de lotes productivos listos para cosechar</p>
                </div>
                <a href="{{ route('cultivos.index') }}" class="custom-table-link">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-agro mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Código</th>
                            <th>Variedad</th>
                            <th>Lote</th>
                            <th>Fecha estimada</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cosechasProximas as $c)
                        <tr>
                            <td class="pl-4 font-weight-bold" style="color: #0f8f67;">{{ $c->codigo }}</td>
                            <td style="color: #334155;">{{ $c->variedad->nombre ?? '—' }}</td>
                            <td style="color: #64748b;">{{ $c->lote->identificador ?? '?' }} — {{ Str::limit($c->lote->nombre, 15) }}</td>
                            <td style="color: #64748b;">{{ $c->fecha_cosecha_estimada->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $cls = match($c->estado) {
                                        'sembrado' => 'status-mid',
                                        'desarrollo' => 'status-warn',
                                        'maduro' => 'status-ok',
                                        'cosechado' => 'status-ok',
                                        default => 'status-mid'
                                    };
                                @endphp
                                <span class="status-pill {{ $cls }}">{{ ucfirst($c->estado) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay cosechas programadas para los próximos 30 días.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pendientes y Notificaciones (Columna Derecha) --}}
    <div class="col-xl-4">
        
        {{-- Actividades Próximas --}}
        <div class="custom-table-card p-4">
            <div class="mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #16332b; margin: 0;">Pendientes (7 días)</h3>
                <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Actividades que requieren atención</p>
            </div>
            
            <div class="mt-2">
                @php $colores = ['#ef4444', '#fbbf24', '#10b981', '#3b82f6']; @endphp
                @forelse($actividadesProximas as $index => $p)
                <div class="list-item-custom">
                    <span class="mini-dot" style="background-color: {{ $colores[$index % 4] }};"></span>
                    <div>
                        <p style="font-weight: 600; font-size: 0.9rem; color: #16332b; margin: 0;">
                            {{ $p->tipoActividad->nombre ?? 'Actividad' }} — Lote {{ $p->cultivo->lote->identificador ?? '?' }}
                        </p>
                        <p style="font-size: 0.8rem; color: #94a3b8; margin: 0;">{{ Str::limit($p->descripcion, 45) }}</p>
                        <p style="font-size: 0.75rem; color: #ef4444; margin: 0; font-weight: 600; margin-top: 2px;">
                            <i class="far fa-calendar-alt"></i> {{ $p->fecha_programada->format('d M') }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3" style="font-size: 0.85rem;">Sin actividades próximas pendientes.</p>
                @endforelse
            </div>
        </div>

        {{-- Notificaciones Recientes --}}
        <div class="custom-table-card p-4">
            <div class="mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #16332b; margin: 0;">Notificaciones Recientes</h3>
                <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Avisos sin leer</p>
            </div>
            
            <div class="mt-2">
                @forelse($notificaciones as $index => $notif)
                <div class="list-item-custom">
                    <span class="mini-dot" style="background-color: {{ $notif->prioridad === 'alta' ? '#ef4444' : ($notif->prioridad === 'media' ? '#fbbf24' : '#94a3b8') }};"></span>
                    <div>
                        <p style="font-weight: 600; font-size: 0.9rem; color: #16332b; margin: 0;">
                            {{ $notif->titulo }}
                        </p>
                        <p style="font-size: 0.8rem; color: #94a3b8; margin: 0;">{{ Str::limit($notif->mensaje, 45) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3" style="font-size: 0.85rem;">
                    <i class="fas fa-check-circle text-success mb-1"></i><br>
                    Estás al día
                </p>
                @endforelse
            </div>
        </div>

    </div>

</div>
</div>

@endsection
