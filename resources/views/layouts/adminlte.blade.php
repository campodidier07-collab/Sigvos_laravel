<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SIGVOS | @yield('title', 'Panel')</title>

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
    .main-header.navbar { 
        border-bottom: none !important;
        background: #ffffff !important;
        padding: 16px 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
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
      <li class="nav-item d-none d-sm-inline-block">
        <div class="navbar-title-section">
            <div class="navbar-icon-box">
                <i class="fas fa-home"></i>
            </div>
            <div class="navbar-title">
                <h1>Panel</h1>
                @php
                    \Carbon\Carbon::setLocale('es');
                    $fecha = ucfirst(\Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y'));
                @endphp
                <p>{{ $fecha }}</p>
            </div>
        </div>
      </li>
    </ul>

    {{-- Right: Search, Filters, Notifications --}}
    <ul class="navbar-nav ml-auto top-actions">
        
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

        {{-- Campana de notificaciones --}}
        <li class="nav-item dropdown">
            <a class="btn-top-icon" data-toggle="dropdown" href="#" id="notif-toggle">
                <i class="fas fa-bell"></i>
                @php
                $unreadCount = auth()->user()
                    ->notificaciones()
                    ->where('leida', false)
                    ->count();
                @endphp
                @if($unreadCount > 0)
                <span class="badge badge-danger navbar-badge" style="top: -2px; right: -2px;">{{ $unreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right mt-2" style="border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <span class="dropdown-header font-weight-bold">
                    {{ $unreadCount }} Notificaci{{ $unreadCount === 1 ? 'ón' : 'ones' }}
                </span>
                <div class="dropdown-divider"></div>
                @if($unreadCount === 0)
                <a href="{{ route('notificaciones.index') }}" class="dropdown-item text-muted text-center py-3">
                    <i class="fas fa-check-circle text-success mr-1"></i> Todo al día
                </a>
                @else
                @foreach(auth()->user()->notificaciones()->where('leida', false)->latest()->take(3)->get() as $noti)
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('notificaciones.index') }}" class="dropdown-item py-2">
                    <i class="fas fa-info-circle mr-2 text-primary"></i> 
                    <span class="text-wrap" style="font-size: 0.85rem; display:inline-block; width: 220px;">
                        {{ Str::limit($noti->titulo, 40) }}
                    </span>
                    <span class="float-right text-muted text-sm" style="font-size: 0.7rem;">{{ $noti->creado_en->diffForHumans() }}</span>
                    </a>
                @endforeach
                @endif
                <div class="dropdown-divider"></div>
                <a href="{{ route('notificaciones.index') }}" class="dropdown-item dropdown-footer text-primary font-weight-bold">Ver todas</a>
            </div>
        </li>

        {{-- Dropdown de Salir --}}
        <li class="nav-item dropdown ml-2">
            <a class="nav-link p-0" data-toggle="dropdown" href="#">
                @if(auth()->user()?->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                         class="img-circle" style="width: 40px; height: 40px; object-fit: cover;"
                         alt="{{ auth()->user()->name }}">
                @else
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right mt-2" style="border-radius: 12px; border: 1px solid #e2e8f0; min-width: 200px;">
                <div class="px-4 py-3 border-bottom">
                    <p class="mb-0 font-weight-bold" style="color: #1e293b;">{{ auth()->user()->nombre }}</p>
                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item py-2">
                    <i class="fas fa-user-cog mr-2 text-muted"></i> Mi Perfil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item py-2 text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                    </button>
                </form>
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
            <p style="margin: 0; font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); font-weight: 500; white-space: nowrap;">Panel Administrador</p>
          </div>
        </div>
      </a>
    </div>

    <div class="sidebar">


      {{-- Menú lateral --}}
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column"
            data-widget="treeview" role="menu" data-accordion="false">

          {{-- Dashboard --}}
          <li class="nav-item">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          {{-- ─── MÓDULOS AGRÍCOLAS ───────────────────────────── --}}
          <li class="nav-header">AGRÍCOLA</li>

          <li class="nav-item">
            <a href="{{ route('lotes.index') }}"
               class="nav-link {{ request()->routeIs('lotes.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-map-marked-alt"></i>
              <p>Lotes</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('cultivos.index') }}"
               class="nav-link {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-seedling"></i>
              <p>Cultivos</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="{{ route('asignaciones.index') }}"
               class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-check"></i>
              <p>Asignaciones</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('actividades.index') }}"
               class="nav-link {{ request()->routeIs('actividades.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Actividades</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="{{ route('cosecha.index') }}"
               class="nav-link {{ request()->routeIs('cosecha.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-shopping-basket"></i>
              <p>Cosecha</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('fotografias.index') }}"
               class="nav-link {{ request()->routeIs('fotografias.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-camera"></i>
              <p>Fotografías</p>
            </a>
          </li>
          
          <li class="nav-header">PLANIFICACIÓN</li>
          
          <li class="nav-item">
            <a href="{{ route('calendario.index') }}"
               class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>Calendario</p>
            </a>
          </li>

          {{-- ─── ADMINISTRACIÓN ──────────────────────────────── --}}
          @if(auth()->user()?->isAdmin())
          <li class="nav-header">ADMINISTRACIÓN</li>

          <li class="nav-item">
            <a href="{{ route('usuarios.index') }}"
               class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i>
              <p>Usuarios</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('reportes.index') }}"
               class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Reportes</p>
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

    {{-- Alertas flash --}}
    <div class="container-fluid px-3">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          <strong>Por favor corrige los siguientes errores:</strong>
          <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      @endif
    </div>

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
@stack('scripts')
</body>
</html>
