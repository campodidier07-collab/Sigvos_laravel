<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SIGVOS | @yield('title', 'Panel')</title>
  
  {{-- Favicon --}}
  <link href="{{ asset('img/icono.png') }}" rel="icon" type="image/png">

  {{-- Google Font --}}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  {{-- Font Awesome --}}
  <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
  {{-- AdminLTE --}}
  <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.min.css') }}">
  {{-- Estilos personalizados SIGVOS --}}
  <style>
    :root {
      --sigvos-green:  #1e6043;
      --sigvos-lime:   #34d399;
      --sigvos-dark:   #164e3b;
      --sigvos-bg:     #f8fafc;
    }
    
    /* Layout Base */
    body, .content-wrapper { background-color: var(--sigvos-bg) !important; }
    
    /* Sidebar */
    .brand-link { 
        background: var(--sigvos-dark) !important; 
        border-bottom: none !important;
        padding: 24px 16px 12px 16px !important;
    }
    .main-sidebar { background: var(--sigvos-dark) !important; }
    
    /* Sidebar Links */
    .sidebar .nav-sidebar .nav-item {
        margin-bottom: 2px;
    }
    .sidebar .nav-sidebar > .nav-item > .nav-link {
        color: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        padding: 8px 16px;
        transition: all 0.2s;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        letter-spacing: 0.3px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
    }
    .sidebar .nav-sidebar > .nav-item > .nav-link i.nav-icon {
        color: rgba(255, 255, 255, 0.7);
        margin-right: 12px;
        width: 24px;
        text-align: center;
        font-size: 1.1rem;
    }
    .sidebar .nav-sidebar > .nav-item > .nav-link.active,
    .sidebar .nav-sidebar > .nav-item > .nav-link:hover {
        background: var(--sigvos-green) !important;
        color: #ffffff !important;
    }
    .sidebar .nav-sidebar > .nav-item > .nav-link.active i.nav-icon,
    .sidebar .nav-sidebar > .nav-item > .nav-link:hover i.nav-icon {
        color: #ffffff !important;
    }
    
    .nav-header {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.7rem !important;
        color: rgba(255,255,255,0.4) !important;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 16px 16px 8px 16px !important;
    }
    
    /* Top Navbar */
    .main-header {
        border-bottom: none !important;
        background: transparent !important;
        padding: 16px 24px;
        box-shadow: none !important;
    }
    
    .navbar-title-section {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .navbar-icon-box {
        width: 40px;
        height: 40px;
        background-color: #10b981;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
    .navbar-title h1 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        color: #1e293b;
    }
    .navbar-title p {
        margin: 0;
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* Top Search & Actions */
    .top-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .top-search-input {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 16px 8px 36px;
        font-size: 0.875rem;
        width: 280px;
        outline: none;
        color: #475569;
    }
    .top-search-wrapper {
        position: relative;
    }
    .top-search-wrapper i.fa-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .btn-top-action {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #10b981;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-top-action:hover { background: #f1f5f9; }
    .btn-top-icon {
        width: 40px;
        height: 40px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        cursor: pointer;
    }
    .btn-top-icon:hover { background: #f1f5f9; }

    /* Unified Search Bar UI */
    .unified-search-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        padding: 4px 12px 4px 20px;
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 600px;
        margin-bottom: 24px;
        transition: all 0.2s;
    }
    .unified-search-bar:focus-within {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15);
        background: #ffffff;
    }
    .unified-search-form {
        display: flex;
        width: 100%;
        align-items: center;
        margin: 0;
    }
    .unified-search-icon {
        color: #94a3b8;
        font-size: 1.1rem;
        margin-right: 12px;
    }
    .unified-search-input {
        border: none;
        background: transparent;
        flex-grow: 1;
        font-size: 0.95rem;
        color: #334155;
        outline: none;
        padding: 8px 0;
        min-width: 0;
    }
    .unified-search-input::placeholder {
        color: #94a3b8;
    }
    .unified-search-filters-wrapper {
        position: relative;
    }

    /* Estilos Globales del Dashboard y Módulos Premium */
    .module-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 0;
    }
    .module-icon-box {
        width: 52px;
        height: 52px;
        background-color: #10b981; /* agro-500 */
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }
    .module-header-info h2 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #1e293b;
        font-size: 1.5rem;
        margin: 0 0 2px 0;
        line-height: 1.2;
    }
    .module-header-info p {
        color: #94a3b8;
        font-size: 0.85rem;
        margin: 0;
        font-weight: 500;
    }
    .module-banner {
        background-color: #f1fcf5;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
        margin-left: -24px;
        margin-right: -24px;
        margin-bottom: 30px;
    }
    .module-banner h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #0f3d2d;
        font-size: 1.75rem;
        margin: 0 0 4px 0;
    }
    .module-banner p {
        color: #475569;
        font-size: 0.95rem;
        margin: 0;
    }
    .btn-filtros-toggle {
        background: transparent;
        border: none;
        color: #10b981;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 9999px;
        transition: background 0.2s;
        margin-left: 8px;
    }
    .btn-filtros-toggle:hover {
        background: #ecfdf5;
    }
    .filtros-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        padding: 16px;
        border: 1px solid #e2e8f0;
        z-index: 1000;
        min-width: 250px;
    }
    .filtros-dropdown-menu.show {
        display: block;
    }

  </style>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  {{-- ══ NAVBAR SUPERIOR ══════════════════════════════════════════════════ --}}
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    
    {{-- Left: Icon and Title --}}
    <ul class="navbar-nav align-items-center">
      <li class="nav-item">
        <a class="nav-link text-slate-400 mr-2" data-widget="pushmenu" href="#" role="button" style="color: #94a3b8;">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block ml-3">
        @yield('navbar-title')
      </li>
    </ul>

    {{-- Right: Search, Filters, Notifications --}}
    <ul class="navbar-nav ml-auto top-actions">
        
        @if (Route::is('dashboard'))
        <li class="nav-item d-none d-md-block">
            <div class="top-search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" class="top-search-input" placeholder="Buscar cultivos, actividades...">
            </div>
        </li>

        <li class="nav-item d-none d-md-block">
            <button class="btn-top-action">
                <i class="fas fa-sliders-h"></i> Filtros
            </button>
        </li>
        @endif

        {{-- ══ Campana de Notificaciones ═══════════════════════════════════════ --}}
        <style>
            /* Bell button */
            .notif-bell-btn {
                position: relative !important;
                width: 40px !important;
                height: 40px !important;
                border-radius: 10px !important;
                background: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #64748b !important;
                font-size: 1rem !important;
                transition: all 0.25s ease !important;
                cursor: pointer !important;
                text-decoration: none !important;
                padding: 0 !important;
                margin: 0 !important;
                line-height: 1 !important;
            }
            .notif-bell-btn:hover {
                background: #f0fdf4 !important;
                color: #059669 !important;
                border-color: #bbf7d0 !important;
            }
            .notif-badge {
                position: absolute;
                top: -5px; right: -5px;
                min-width: 18px; height: 18px;
                border-radius: 9px;
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
                font-size: 0.65rem;
                font-weight: 700;
                display: flex; align-items: center; justify-content: center;
                padding: 0 4px;
                border: 2px solid #fff;
                animation: pulse-badge 2s infinite;
                z-index: 10;
            }
            @keyframes pulse-badge {
                0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
                50%       { box-shadow: 0 0 0 5px rgba(239,68,68,0); }
            }
            /* Dropdown panel */
            .notif-dropdown {
                width: 360px !important;
                min-width: 360px !important;
                border: none !important;
                border-radius: 20px !important;
                padding: 0 !important;
                box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.1) !important;
                overflow: hidden;
                animation: notifDrop 0.22s cubic-bezier(0.34,1.56,0.64,1);
                background: #fff !important;
            }
            @keyframes notifDrop {
                from { opacity: 0; transform: translateY(-12px) scale(0.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }
            /* Header */
            .notif-header {
                background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #047857 100%);
                padding: 16px 20px;
                display: flex; align-items: center; justify-content: space-between;
                position: relative; overflow: hidden;
            }
            .notif-header::after {
                content:''; position:absolute; top:-20px; right:-20px;
                width:80px; height:80px; border-radius:50%;
                background: rgba(255,255,255,0.05);
            }
            .notif-header-title {
                color: #fff; font-weight: 700; font-size: 0.95rem; margin: 0;
                display: flex; align-items: center; gap: 8px;
            }
            .notif-count-pill {
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: #fff; font-size: 0.72rem; font-weight: 700;
                padding: 2px 9px; border-radius: 12px;
            }
            .notif-mark-all-btn {
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.2);
                color: rgba(255,255,255,0.9);
                font-size: 0.72rem; font-weight: 600;
                padding: 5px 12px; border-radius: 10px;
                cursor: pointer; transition: all 0.2s ease;
                white-space: nowrap;
            }
            .notif-mark-all-btn:hover { background: rgba(255,255,255,0.22); color: #fff; }
            /* Items */
            .notif-list { max-height: 320px; overflow-y: auto; }
            .notif-list::-webkit-scrollbar { width: 4px; }
            .notif-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
            .notif-item {
                display: flex !important;
                align-items: flex-start;
                gap: 12px;
                padding: 13px 18px !important;
                border-bottom: 1px solid #f8fafc;
                transition: background 0.18s ease !important;
                text-decoration: none !important;
                position: relative;
            }
            .notif-item:hover { background: #f8fafc !important; }
            .notif-item.unread { background: #f0fdf4 !important; }
            .notif-item.unread:hover { background: #dcfce7 !important; }
            .notif-item-icon {
                width: 36px; height: 36px; flex-shrink: 0;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 0.85rem;
            }
            .icon-info    { background:#eff6ff; color:#3b82f6; }
            .icon-success { background:#f0fdf4; color:#22c55e; }
            .icon-warning { background:#fffbeb; color:#f59e0b; }
            .icon-danger  { background:#fef2f2; color:#ef4444; }
            .icon-system  { background:#f5f3ff; color:#8b5cf6; }
            .notif-item-body { flex: 1; min-width: 0; }
            .notif-item-title {
                font-size: 0.83rem; font-weight: 600; color: #1e293b;
                margin: 0 0 3px;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .notif-item-time {
                font-size: 0.72rem; color: #94a3b8;
                display: flex; align-items: center; gap: 4px;
            }
            .notif-unread-dot {
                width: 8px; height: 8px; border-radius: 50%;
                background: #10b981; flex-shrink: 0; margin-top: 4px;
            }
            /* Footer */
            .notif-footer {
                padding: 12px 18px;
                border-top: 1px solid #f1f5f9;
                text-align: center;
            }
            .notif-footer a {
                color: #059669 !important; font-size: 0.83rem; font-weight: 600;
                display: inline-flex; align-items: center; gap: 6px;
                text-decoration: none !important;
                transition: color 0.2s;
            }
            .notif-footer a:hover { color: #047857 !important; }
            /* Empty state */
            .notif-empty {
                padding: 36px 20px; text-align: center;
            }
            .notif-empty-icon {
                width: 60px; height: 60px; border-radius: 50%;
                background: #f0fdf4; color: #10b981;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.4rem; margin: 0 auto 12px;
            }
        </style>

        @php
            $unreadCount   = auth()->user()->notificaciones()->where('leida', false)->count();
            $recentNotifs  = auth()->user()->notificaciones()->latest('creado_en')->take(5)->get();
        @endphp

        <li class="nav-item dropdown" style="display:flex; align-items:center;">
            <a class="nav-link notif-bell-btn" data-toggle="dropdown" href="#" id="notif-toggle" title="Notificaciones"
               style="display:flex !important; align-items:center; justify-content:center; visibility:visible !important; opacity:1 !important;">
                <i class="fas fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="notif-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-right mt-2 notif-dropdown">
                {{-- Header --}}
                <div class="notif-header">
                    <p class="notif-header-title">
                        <i class="fas fa-bell"></i>
                        Notificaciones
                        @if($unreadCount > 0)
                            <span class="notif-count-pill">{{ $unreadCount }} nuevas</span>
                        @endif
                    </p>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notificaciones.marcar_todas') }}" style="margin:0">
                            @csrf
                            <button type="submit" class="notif-mark-all-btn">
                                <i class="fas fa-check-double mr-1"></i> Leer todas
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Lista --}}
                <div class="notif-list">
                    @forelse($recentNotifs as $noti)
                        @php
                            $iconMap = [
                                'actividad' => ['icon' => 'fa-tasks',        'class' => 'icon-info'],
                                'cultivo'   => ['icon' => 'fa-seedling',     'class' => 'icon-success'],
                                'alerta'    => ['icon' => 'fa-exclamation-triangle', 'class' => 'icon-warning'],
                                'cosecha'   => ['icon' => 'fa-leaf',         'class' => 'icon-success'],
                                'sistema'   => ['icon' => 'fa-cog',          'class' => 'icon-system'],
                                'error'     => ['icon' => 'fa-times-circle', 'class' => 'icon-danger'],
                            ];
                            $icono = $iconMap[$noti->tipo] ?? ['icon' => 'fa-bell', 'class' => 'icon-info'];
                        @endphp
                        <a href="{{ route('notificaciones.index') }}"
                           class="notif-item {{ !$noti->leida ? 'unread' : '' }}">
                            <div class="notif-item-icon {{ $icono['class'] }}">
                                <i class="fas {{ $icono['icon'] }}"></i>
                            </div>
                            <div class="notif-item-body">
                                <p class="notif-item-title">{{ $noti->titulo }}</p>
                                <div class="notif-item-time">
                                    <i class="far fa-clock"></i>
                                    {{ $noti->creado_en->diffForHumans() }}
                                </div>
                            </div>
                            @if(!$noti->leida)
                                <div class="notif-unread-dot"></div>
                            @endif
                        </a>
                    @empty
                        <div class="notif-empty">
                            <div class="notif-empty-icon"><i class="fas fa-check"></i></div>
                            <p style="color:#1e293b; font-weight:600; margin:0 0 4px;">¡Todo al día!</p>
                            <p style="color:#94a3b8; font-size:0.8rem; margin:0;">No tienes notificaciones pendientes.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer --}}
                <div class="notif-footer">
                    <a href="{{ route('notificaciones.index') }}">
                        Ver todas las notificaciones <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </li>

        {{-- Dropdown de Usuario --}}
        <style>
            .user-dropdown-toggle {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px !important;
                height: 40px !important;
                padding: 0 !important;
                border-radius: 50%;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                transition: all 0.2s ease;
                cursor: pointer;
                text-decoration: none !important;
                margin-top: 1px;
            }
            .user-dropdown-toggle:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
                transform: translateY(-1px);
            }
            .user-avatar-btn {
                width: 36px; height: 36px;
                border-radius: 50%;
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: 0.9rem;
                box-shadow: 0 2px 8px rgba(16,185,129,0.5);
                flex-shrink: 0;
                overflow: hidden;
            }
            .user-name-label {
                color: #334155;
                font-size: 0.85rem;
                font-weight: 600;
                max-width: 110px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: none;
            }
            @media(min-width: 768px) { .user-name-label { display: block; } }
            .user-chevron {
                color: #94a3b8;
                font-size: 0.75rem;
                transition: transform 0.2s ease;
            }
            .nav-item.dropdown.show .user-chevron { transform: rotate(180deg); }

            /* Dropdown panel */
            .user-dropdown-menu {
                border: none !important;
                border-radius: 18px !important;
                min-width: 260px !important;
                padding: 0 !important;
                box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.1) !important;
                overflow: hidden;
                animation: dropIn 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
                background: #fff !important;
            }
            @keyframes dropIn {
                from { opacity: 0; transform: translateY(-10px) scale(0.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }

            .user-dropdown-header {
                background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
                padding: 24px 20px 20px;
                position: relative;
                overflow: hidden;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .user-dropdown-header::before {
                content: '';
                position: absolute; top: -30px; right: -30px;
                width: 100px; height: 100px;
                border-radius: 50%;
                background: rgba(255,255,255,0.06);
            }
            .user-dropdown-header::after {
                content: '';
                position: absolute; bottom: -20px; left: 20px;
                width: 70px; height: 70px;
                border-radius: 50%;
                background: rgba(255,255,255,0.04);
            }
            .user-header-avatar {
                width: 60px; height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #10b981, #34d399);
                color: white;
                display: flex; align-items: center; justify-content: center;
                font-weight: 800; font-size: 1.4rem;
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                border: 3px solid rgba(255,255,255,0.25);
                margin: 0 auto 12px auto;
                overflow: hidden;
                position: relative; z-index: 1;
            }
            .user-header-name {
                color: #fff;
                font-size: 0.97rem;
                font-weight: 700;
                margin: 0 0 3px;
                position: relative; z-index: 1;
                text-align: center;
            }
            .user-header-email {
                color: rgba(255,255,255,0.65);
                font-size: 0.75rem;
                margin: 0 0 10px;
                position: relative; z-index: 1;
                text-align: center;
            }
            .user-role-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.2);
                color: rgba(255,255,255,0.9);
                font-size: 0.7rem;
                font-weight: 600;
                padding: 3px 10px;
                border-radius: 20px;
                position: relative; z-index: 1;
            }
            .user-status-dot {
                width: 7px; height: 7px;
                border-radius: 50%;
                background: #4ade80;
                box-shadow: 0 0 6px #4ade80;
                display: inline-block;
            }

            /* Menu items */
            .user-dropdown-body { padding: 8px; }
            .user-menu-item {
                display: flex !important;
                align-items: center;
                gap: 12px;
                padding: 10px 14px !important;
                border-radius: 12px !important;
                font-size: 0.85rem !important;
                font-weight: 500 !important;
                color: #374151 !important;
                transition: all 0.2s ease !important;
                border: none !important;
                width: 100%;
                background: transparent;
                text-align: left;
                cursor: pointer;
                text-decoration: none;
            }
            .user-menu-item:hover {
                background: #f0fdf4 !important;
                color: #059669 !important;
                transform: translateX(3px);
            }
            .user-menu-icon {
                width: 32px; height: 32px;
                border-radius: 9px;
                display: flex; align-items: center; justify-content: center;
                font-size: 0.8rem;
                flex-shrink: 0;
                transition: all 0.2s ease;
            }
            .user-menu-item:hover .user-menu-icon { transform: scale(1.1); }
            .icon-profile { background: #ecfdf5; color: #059669; }
            .icon-logout  { background: #fef2f2; color: #ef4444; }

            .user-menu-item.logout-btn { color: #dc2626 !important; }
            .user-menu-item.logout-btn:hover { background: #fef2f2 !important; color: #dc2626 !important; }

            .user-dropdown-divider {
                height: 1px;
                background: #f1f5f9;
                margin: 4px 8px;
            }
        </style>

        <li class="nav-item dropdown ml-3" style="display:flex; align-items:center;">
            <a class="nav-link user-dropdown-toggle p-0" data-toggle="dropdown" href="#" id="userDropdownBtn">
                <div class="user-avatar-btn">
                    @if(auth()->user()?->foto_perfil)
                        <img src="{{ asset('storage/' . auth()->user()->foto_perfil) }}"
                             style="width:100%; height:100%; object-fit:cover;"
                             alt="{{ auth()->user()->nombre ?? '' }}">
                    @else
                        {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
                    @endif
                </div>
            </a>

            <div class="dropdown-menu dropdown-menu-right user-dropdown-menu">
                {{-- Header --}}
                <div class="user-dropdown-header">
                    <div class="user-header-avatar">
                        @if(auth()->user()?->foto_perfil)
                            <img src="{{ asset('storage/' . auth()->user()->foto_perfil) }}"
                                 style="width:100%; height:100%; object-fit:cover;"
                                 alt="{{ auth()->user()->nombre ?? '' }}">
                        @else
                            {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <p class="user-header-name">{{ auth()->user()?->nombre ?? 'Usuario' }}</p>
                    <p class="user-header-email">{{ auth()->user()?->email ?? '' }}</p>
                    <span class="user-role-badge">
                        <span class="user-status-dot"></span>
                        {{ auth()->user()?->rol?->nombre ?? 'Usuario' }}
                    </span>
                </div>

                {{-- Items --}}
                <div class="user-dropdown-body">
                    <a href="{{ route('profile.edit') }}" class="user-menu-item">
                        <div class="user-menu-icon icon-profile">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <div style="font-weight:600; font-size:0.83rem;">Mi Perfil</div>
                            <div style="font-size:0.72rem; color:#94a3b8; margin-top:1px;">Ver y editar tu cuenta</div>
                        </div>
                    </a>

                    <div class="user-dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="user-menu-item logout-btn">
                            <div class="user-menu-icon icon-logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div>
                                <div style="font-weight:600; font-size:0.83rem;">Cerrar sesión</div>
                                <div style="font-size:0.72rem; color:#94a3b8; margin-top:1px;">Salir del sistema</div>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </li>

    </ul>
  </nav>
  {{-- /.navbar --}}

  {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════ --}}
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    {{-- Logo --}}
    <div style="padding: 24px 16px 32px 16px;">
      <a href="{{ route('dashboard') }}" style="display: block; text-decoration: none;">
        <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 14px 16px; display: flex; align-items: center; gap: 14px; transition: background 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255, 255, 255, 0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.05);">
            <img src="{{ asset('img/icono.png') }}" style="width: 32px; height: 32px; object-fit: contain;" alt="SIGVOS">
          </div>
          <div style="display: flex; flex-direction: column; justify-content: center; overflow: hidden;">
            <h1 style="margin: 0 0 2px 0; font-size: 1.4rem; font-weight: 800; color: #ffffff; line-height: 1.1; font-family: 'Outfit', sans-serif; letter-spacing: 0.5px;">SIGVOS</h1>
            <p style="margin: 0; font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); font-weight: 500; white-space: nowrap;">Panel {{ auth()->user()?->rol?->nombre ?? 'Gestión' }}</p>
          </div>
        </div>
      </a>
    </div>

    <div class="sidebar">


      {{-- Menú lateral --}}
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column"
            data-widget="treeview" role="menu" data-accordion="false">

          @if(auth()->user()?->isAdmin())
              {{-- Menú para Administradores --}}
              <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-header">AGRÍCOLA</li>
              <li class="nav-item">
                <a href="{{ route('lotes.index') }}" class="nav-link {{ request()->routeIs('lotes.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-map-marked-alt"></i><p>Lotes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('cultivos.index') }}" class="nav-link {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-seedling"></i><p>Cultivos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('asignaciones.index') }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-user-check"></i><p>Asignaciones</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('actividades.index') }}" class="nav-link {{ request()->routeIs('actividades.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tasks"></i><p>Actividades</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('cosecha.index') }}" class="nav-link {{ request()->routeIs('cosecha.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-basket"></i><p>Cosecha</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('fotografias.index') }}" class="nav-link {{ request()->routeIs('fotografias.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-camera"></i><p>Fotografías</p>
                </a>
              </li>

              <li class="nav-header">PLANIFICACIÓN</li>
              <li class="nav-item">
                <a href="{{ route('calendario.index') }}" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-calendar-alt"></i><p>Calendario</p>
                </a>
              </li>

              <li class="nav-header">ADMINISTRACIÓN</li>
              <li class="nav-item">
                <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-users"></i><p>Usuarios</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-bar"></i><p>Reportes</p>
                </a>
              </li>
          @else
              {{-- Menú para Trabajadores --}}
              <li class="nav-header">PRINCIPAL</li>
              <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-home"></i>
                  <p>Mi Panel</p>
                </a>
              </li>

              <li class="nav-header">OPERACIONES</li>
              <li class="nav-item">
                <a href="{{ route('lotes.index') }}" class="nav-link {{ request()->routeIs('lotes.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-map"></i><p>Mis Lotes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('cultivos.index') }}" class="nav-link {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-seedling"></i><p>Cultivos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('actividades.index') }}" class="nav-link {{ request()->routeIs('actividades.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-calendar-check"></i><p>Actividades</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('fotografias.index') }}" class="nav-link {{ request()->routeIs('fotografias.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-camera"></i><p>Fotografías</p>
                </a>
              </li>

          @endif



        </ul>
      </nav>
    </div>
  </aside>
  {{-- /.sidebar --}}

  {{-- ══ CONTENT WRAPPER ══════════════════════════════════════════════════ --}}
  <div class="content-wrapper">

    @hasSection('page-title')
    {{-- Cabecera de página --}}
    <div class="content-header">
      <div class="container-fluid px-4">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">@yield('page-title')</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a>
              </li>
              @yield('breadcrumb')
            </ol>
          </div>
        </div>
      </div>
    </div>
    @endif

    {{-- Las alertas flash ahora son manejadas por SweetAlert2 (ver final del archivo) --}}

    {{-- Contenido principal --}}
    <div class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>

  </div>
  {{-- /.content-wrapper --}}

  {{-- ══ FOOTER ════════════════════════════════════════════════════════════ --}}


</div>
{{-- ./wrapper --}}

{{-- ══ SCRIPTS ══════════════════════════════════════════════════════════════ --}}
<script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.min.js') }}"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if(session('success'))
        Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#ef4444'
        });
    @endif
    
    @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: '{{ session('warning') }}',
            confirmButtonColor: '#f59e0b'
        });
    @endif

    @if(session('status') === 'profile-updated' || session('status') === 'avatar-updated' || session('status') === 'password-updated')
        Toast.fire({ icon: 'success', title: 'Actualización exitosa' });
    @elseif(session('status'))
        Toast.fire({ icon: 'info', title: '{{ session('status') }}' });
    @endif

    @if($errors->any())
        Toast.fire({ icon: 'error', title: 'Por favor corrige los errores en el formulario.' });
    @endif

    // Función global para confirmaciones
    function confirmarEliminacion(event, formElement, title = '¿Estás seguro?', text = 'Esta acción no se puede deshacer.') {
        event.preventDefault();
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                formElement.submit();
            }
        });
    }
</script>

@stack('scripts')
<script>
  function toggleFiltros(event) {
      event.preventDefault();
      const dropdown = document.getElementById('unified-filtros-dropdown');
      if (dropdown) {
          dropdown.classList.toggle('show');
      }
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('unified-filtros-dropdown');
      const toggleBtn = document.querySelector('.btn-filtros-toggle');
      if (dropdown && dropdown.classList.contains('show')) {
          if (!dropdown.contains(event.target) && !toggleBtn.contains(event.target)) {
              dropdown.classList.remove('show');
          }
      }
  });
</script>

</body>
</html>
