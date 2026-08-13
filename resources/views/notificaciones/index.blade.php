@extends('layouts.adminlte')

@section('title', 'Notificaciones')

@section('content')

<style>
    /* ── Page header ─────────────────────────────────────────────────── */
    .notif-page-hero {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .notif-page-hero::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:160px; height:160px; border-radius:50%;
        background: rgba(255,255,255,0.05);
    }
    .notif-page-hero-title {
        color:#fff; font-size:1.5rem; font-weight:800;
        margin:0 0 4px; font-family:'Outfit',sans-serif;
        position:relative; z-index:1;
    }
    .notif-page-hero-sub {
        color:rgba(255,255,255,0.65); font-size:0.85rem;
        margin:0; position:relative; z-index:1;
    }
    .notif-page-mark-btn {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.22);
        color: rgba(255,255,255,0.95);
        font-size: 0.83rem; font-weight: 600;
        padding: 10px 20px; border-radius: 12px;
        cursor: pointer; transition: all 0.25s ease;
        white-space: nowrap; position:relative; z-index:1;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .notif-page-mark-btn:hover {
        background: rgba(255,255,255,0.22);
        color: #fff;
        transform: translateY(-1px);
    }

    /* ── Filters bar ─────────────────────────────────────────────────── */
    .notif-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .notif-filter-btn {
        padding: 7px 16px;
        border-radius: 20px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .notif-filter-btn:hover, .notif-filter-btn.active {
        background: #064e3b;
        border-color: #064e3b;
        color: #fff;
    }

    /* ── Notification cards ──────────────────────────────────────────── */
    .notif-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        padding: 18px 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .notif-card:hover {
        border-color: #d1fae5;
        box-shadow: 0 4px 20px rgba(16,185,129,0.08);
        transform: translateX(3px);
    }
    .notif-card.unread {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
    .notif-card.unread::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, #10b981, #34d399);
        border-radius: 4px 0 0 4px;
    }
    /* Priority left bar */
    .notif-card.prio-alta::before   { background: linear-gradient(to bottom, #ef4444, #f97316); }
    .notif-card.prio-media::before  { background: linear-gradient(to bottom, #f59e0b, #eab308); }
    .notif-card.prio-baja::before   { background: linear-gradient(to bottom, #10b981, #34d399); }

    .notif-card-icon {
        width: 46px; height: 46px; flex-shrink: 0;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }
    .icon-info    { background:#eff6ff; color:#3b82f6; }
    .icon-success { background:#f0fdf4; color:#22c55e; }
    .icon-warning { background:#fffbeb; color:#f59e0b; }
    .icon-danger  { background:#fef2f2; color:#ef4444; }
    .icon-system  { background:#f5f3ff; color:#8b5cf6; }

    .notif-card-body { flex: 1; min-width: 0; }
    .notif-card-title {
        font-size: 0.92rem; font-weight: 700;
        color: #1e293b; margin: 0 0 5px;
    }
    .notif-card-msg {
        font-size: 0.83rem; color: #64748b;
        margin: 0 0 8px; line-height: 1.5;
    }
    .notif-card-meta {
        display: flex; align-items: center; gap: 12px;
        flex-wrap: wrap;
    }
    .notif-card-time {
        font-size: 0.75rem; color: #94a3b8;
        display: flex; align-items: center; gap: 4px;
    }
    .notif-type-badge {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        padding: 2px 9px; border-radius: 10px; letter-spacing: 0.04em;
    }
    .badge-actividad { background:#eff6ff; color:#3b82f6; }
    .badge-cultivo   { background:#f0fdf4; color:#059669; }
    .badge-alerta    { background:#fffbeb; color:#d97706; }
    .badge-cosecha   { background:#f0fdf4; color:#16a34a; }
    .badge-sistema   { background:#f5f3ff; color:#7c3aed; }
    .badge-error     { background:#fef2f2; color:#dc2626; }
    .badge-default   { background:#f1f5f9; color:#64748b; }

    .prio-badge {
        font-size: 0.68rem; font-weight: 700;
        padding: 2px 8px; border-radius: 8px;
    }
    .prio-alta  { background:#fef2f2; color:#dc2626; }
    .prio-media { background:#fffbeb; color:#d97706; }
    .prio-baja  { background:#f0fdf4; color:#16a34a; }

    .notif-card-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .btn-mark-read {
        background: #10b981;
        border: none;
        color: white;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-mark-read:hover { background: #059669; transform: translateY(-1px); }
    .btn-go-link {
        background: #f1f5f9;
        border: none;
        color: #475569;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-go-link:hover { background: #e2e8f0; color: #1e293b; text-decoration: none; }

    /* ── Empty state ─────────────────────────────────────────────────── */
    .notif-empty-page {
        text-align: center;
        padding: 80px 20px;
    }
    .notif-empty-page-icon {
        width: 90px; height: 90px; border-radius: 50%;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #10b981;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(16,185,129,0.15);
    }

    /* ── Pagination ──────────────────────────────────────────────────── */
    .notif-pagination {
        display: flex; justify-content: center;
        margin-top: 24px;
    }
    .notif-pagination .page-link {
        border-radius: 10px !important;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        margin: 0 3px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .notif-pagination .page-item.active .page-link {
        background: #10b981 !important;
        border-color: #10b981 !important;
        color: white !important;
    }
    .notif-pagination .page-link:hover { background: #f0fdf4; color: #059669; border-color: #bbf7d0; }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2 mb-0">
            <i class="fas fa-bell text-muted"></i>
            <span class="text-muted small">Panel &rsaquo; Notificaciones</span>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

    <x-module-header 
        title="Centro de Notificaciones" 
        subtitle="Estás al día con todas tus notificaciones" 
        icon="fa-bell"
    >
        @php $totalUnread = auth()->user()->notificaciones()->where('leida', false)->count(); @endphp
        @if($totalUnread > 0)
            <form method="POST" action="{{ route('notificaciones.marcar_todas') }}" style="margin:0">
                @csrf
                <button type="submit" class="notif-page-mark-btn">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
            </form>
        @endif
    </x-module-header>



    {{-- Filters --}}
    <div class="notif-filters" id="notifFilters">
        <button class="notif-filter-btn active" data-filter="all" onclick="filterNotifs('all', this)">
            <i class="fas fa-th-large mr-1"></i> Todas
        </button>
        <button class="notif-filter-btn" data-filter="unread" onclick="filterNotifs('unread', this)">
            <i class="fas fa-circle mr-1" style="font-size:0.5rem;vertical-align:middle;color:#10b981;"></i> Sin leer
        </button>
        <button class="notif-filter-btn" data-filter="actividad" onclick="filterNotifs('actividad', this)">
            <i class="fas fa-tasks mr-1"></i> Actividades
        </button>
        <button class="notif-filter-btn" data-filter="cultivo" onclick="filterNotifs('cultivo', this)">
            <i class="fas fa-seedling mr-1"></i> Cultivos
        </button>
        <button class="notif-filter-btn" data-filter="alerta" onclick="filterNotifs('alerta', this)">
            <i class="fas fa-exclamation-triangle mr-1"></i> Alertas
        </button>
        <button class="notif-filter-btn" data-filter="cosecha" onclick="filterNotifs('cosecha', this)">
            <i class="fas fa-leaf mr-1"></i> Cosecha
        </button>
    </div>

    {{-- Notification list --}}
    <div id="notifList">
        @forelse($notificaciones as $noti)
            @php
                $iconMap = [
                    'actividad' => ['icon' => 'fa-tasks',                'class' => 'icon-info',    'badge' => 'badge-actividad'],
                    'cultivo'   => ['icon' => 'fa-seedling',             'class' => 'icon-success', 'badge' => 'badge-cultivo'],
                    'alerta'    => ['icon' => 'fa-exclamation-triangle', 'class' => 'icon-warning', 'badge' => 'badge-alerta'],
                    'cosecha'   => ['icon' => 'fa-leaf',                 'class' => 'icon-success', 'badge' => 'badge-cosecha'],
                    'sistema'   => ['icon' => 'fa-cog',                  'class' => 'icon-system',  'badge' => 'badge-sistema'],
                    'error'     => ['icon' => 'fa-times-circle',         'class' => 'icon-danger',  'badge' => 'badge-error'],
                ];
                $icono = $iconMap[$noti->tipo] ?? ['icon' => 'fa-bell', 'class' => 'icon-info', 'badge' => 'badge-default'];
                $prioClass = match($noti->prioridad ?? '') {
                    'alta'  => 'prio-alta',
                    'media' => 'prio-media',
                    'baja'  => 'prio-baja',
                    default => ''
                };
            @endphp

            <div class="notif-card {{ !$noti->leida ? 'unread ' . $prioClass : '' }}"
                 data-tipo="{{ $noti->tipo }}"
                 data-leida="{{ $noti->leida ? '1' : '0' }}">

                <div class="notif-card-icon {{ $icono['class'] }}">
                    <i class="fas {{ $icono['icon'] }}"></i>
                </div>

                <div class="notif-card-body">
                    <p class="notif-card-title">{{ $noti->titulo }}</p>
                    @if($noti->mensaje)
                        <p class="notif-card-msg">{{ $noti->mensaje }}</p>
                    @endif
                    <div class="notif-card-meta">
                        <span class="notif-card-time">
                            <i class="far fa-clock"></i>
                            {{ $noti->creado_en->diffForHumans() }}
                        </span>
                        @if($noti->tipo)
                            <span class="notif-type-badge {{ $icono['badge'] }}">{{ $noti->tipo }}</span>
                        @endif
                        @if($noti->prioridad)
                            <span class="prio-badge prio-{{ $noti->prioridad }}">
                                {{ ucfirst($noti->prioridad) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="notif-card-actions">
                    @if(!$noti->leida)
                        <form action="{{ route('notificaciones.leida', $noti) }}" method="POST" style="margin:0">
                            @csrf
                            <button type="submit" class="btn-mark-read" title="Marcar como leída">
                                <i class="fas fa-check mr-1"></i> Leída
                            </button>
                        </form>
                    @elseif($noti->url)
                        <a href="{{ $noti->url }}" class="btn-go-link">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver
                        </a>
                    @else
                        <span style="font-size:0.75rem; color:#94a3b8; white-space:nowrap;">
                            <i class="fas fa-check-circle text-success mr-1"></i> Leída
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="notif-empty-page">
                <div class="notif-empty-page-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h4 style="color:#1e293b; font-weight:700; margin-bottom:8px;">Sin notificaciones</h4>
                <p style="color:#94a3b8; font-size:0.9rem;">No tienes notificaciones registradas todavía.</p>
                <a href="{{ route('dashboard') }}" style="color:#059669; font-weight:600; font-size:0.88rem;">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al panel
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notificaciones->hasPages())
        <div class="notif-pagination">
            {{ $notificaciones->links('pagination::bootstrap-4') }}
        </div>
    @endif

</div>
</section>

<script>
function filterNotifs(tipo, btn) {
    // Update active button
    document.querySelectorAll('.notif-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter cards
    document.querySelectorAll('.notif-card').forEach(card => {
        if (tipo === 'all') {
            card.style.display = '';
        } else if (tipo === 'unread') {
            card.style.display = card.dataset.leida === '0' ? '' : 'none';
        } else {
            card.style.display = card.dataset.tipo === tipo ? '' : 'none';
        }
    });
}
</script>

@endsection
