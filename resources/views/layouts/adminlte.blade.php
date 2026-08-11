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
      --sigvos-green:  #2d6a4f;
      --sigvos-lime:   #52b788;
      --sigvos-dark:   #1b4332;
    }
    .brand-link { background: var(--sigvos-dark) !important; }
    .main-sidebar { background: var(--sigvos-dark) !important; }
    .sidebar .nav-sidebar > .nav-item > .nav-link.active,
    .sidebar .nav-sidebar > .nav-item > .nav-link:hover {
      background: var(--sigvos-green) !important;
    }
    .nav-sidebar .nav-item .nav-treeview .nav-link.active,
    .nav-sidebar .nav-item .nav-treeview .nav-link:hover {
      background: rgba(255,255,255,.08) !important;
    }
    .main-header.navbar { border-bottom: 2px solid var(--sigvos-lime); }
    .brand-text { color: #fff !important; font-weight: 700; letter-spacing: 1px; }
    .user-panel .info a { color: #c3e6cb !important; }
    .sidebar-search-results .list-group-item a { color: #333; }
    /* Badge activo */
    .nav-sidebar .badge { font-size: .7rem; }
    /* Fondo general */
    body, .content-wrapper { background-color: #f2fbf5 !important; }
  </style>
  @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  {{-- ══ NAVBAR SUPERIOR ══════════════════════════════════════════════════ --}}
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    {{-- Izquierda: toggle menú + breadcrumb --}}
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link">
          <i class="fas fa-home text-success"></i>&nbsp;Inicio
        </a>
      </li>
    </ul>

    {{-- Derecha: notificaciones + perfil --}}
    <ul class="navbar-nav ml-auto">
      {{-- Campana de notificaciones --}}
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" id="notif-toggle">
          <i class="far fa-bell"></i>
          @php
            $unreadCount = auth()->user()
              ->notificaciones()
              ->where('leida', false)
              ->count();
          @endphp
          @if($unreadCount > 0)
            <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header">
            {{ $unreadCount }} Notificaci{{ $unreadCount === 1 ? 'ón' : 'ones' }}
          </span>
          <div class="dropdown-divider"></div>
          @if($unreadCount === 0)
            <a href="{{ route('notificaciones.index') }}" class="dropdown-item text-muted text-center">
              <i class="fas fa-check-circle text-success mr-1"></i> Todo al día
            </a>
          @else
            @foreach(auth()->user()->notificaciones()->where('leida', false)->latest()->take(3)->get() as $noti)
              <div class="dropdown-divider"></div>
              <a href="{{ route('notificaciones.index') }}" class="dropdown-item">
                <i class="fas fa-info-circle mr-2 text-primary"></i> 
                <span class="text-wrap" style="font-size: 0.85rem; display:inline-block; width: 220px;">
                  {{ Str::limit($noti->titulo, 40) }}
                </span>
                <span class="float-right text-muted text-sm">{{ $noti->creado_en->diffForHumans() }}</span>
              </a>
            @endforeach
          @endif
          <div class="dropdown-divider"></div>
          <a href="{{ route('notificaciones.index') }}" class="dropdown-item dropdown-footer">Ver todas las notificaciones</a>
        </div>
      </li>

      {{-- Pantalla completa --}}
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>

      {{-- Menú usuario --}}
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" id="user-menu-toggle">
          @if(auth()->user()?->profile_photo)
            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                 class="user-image img-circle elevation-2"
                 alt="{{ auth()->user()->name }}">
          @else
            <span class="user-image img-circle elevation-2 bg-success d-flex align-items-center justify-content-center text-white font-weight-bold"
                  style="width:30px;height:30px;font-size:.9rem;">
              {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
            </span>
          @endif
          <span class="d-none d-md-inline">{{ auth()->user()?->nombre }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <li class="user-header bg-success">
            <span class="user-image img-circle elevation-2 bg-white d-flex align-items-center justify-content-center"
                  style="width:90px;height:90px;font-size:2rem;color:var(--sigvos-dark);margin:0 auto;">
              {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
            </span>
            <p>
              {{ auth()->user()?->nombre }}
              <small>{{ auth()->user()?->rol?->nombre ?? 'Sin rol' }}</small>
            </p>
          </li>
          <li class="user-footer">
            <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">
              <i class="fas fa-user-cog mr-1"></i> Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-default btn-flat float-right">
                <i class="fas fa-sign-out-alt mr-1"></i> Salir
              </button>
            </form>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
  {{-- /.navbar --}}

  {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════ --}}
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    {{-- Logo --}}
    <a href="{{ route('dashboard') }}" class="brand-link">
      <i class="fas fa-leaf brand-image elevation-3 text-success"
         style="font-size:1.6rem;width:34px;height:34px;display:flex;align-items:center;justify-content:center;background:#fff;border-radius:50%;padding:6px;opacity:.9;"></i>
      <span class="brand-text">SIGVOS</span>
    </a>

    <div class="sidebar">
      {{-- Panel usuario en sidebar --}}
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <span class="img-circle elevation-2 bg-success d-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:35px;height:35px;font-size:1rem;">
            {{ strtoupper(substr(auth()->user()?->nombre ?? 'U', 0, 1)) }}
          </span>
        </div>
        <div class="info">
          <a href="{{ route('profile.edit') }}" class="d-block">
            {{ auth()->user()?->nombre }}
          </a>
        </div>
      </div>

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
            <a href="{{ route('actividades.index') }}"
               class="nav-link {{ request()->routeIs('actividades.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Actividades</p>
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

          {{-- ─── CUENTA ──────────────────────────────────────── --}}
          <li class="nav-header">MI CUENTA</li>

          <li class="nav-item">
            <a href="{{ route('notificaciones.index') }}"
               class="nav-link {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}">
              <i class="nav-icon far fa-bell"></i>
              <p>
                Notificaciones
                @if($unreadCount > 0)
                  <span class="badge badge-warning right">{{ $unreadCount }}</span>
                @endif
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('profile.edit') }}"
               class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-circle"></i>
              <p>Perfil</p>
            </a>
          </li>

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
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline text-muted">
      <small>v1.0.0</small>
    </div>
    <strong>
      <i class="fas fa-leaf text-success mr-1"></i> SIGVOS
    </strong>
    &mdash; Sistema de Gestión de Vigilancia de Operaciones &amp; Siembras.
    Todos los derechos reservados.
  </footer>

</div>
{{-- ./wrapper --}}

{{-- ══ SCRIPTS ══════════════════════════════════════════════════════════════ --}}
<script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
