@extends('layouts.adminlte')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
  <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- ══ FILA 1: Tarjetas KPI ═══════════════════════════════════════════════════ --}}
<div class="row">

  {{-- Lotes activos --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $estadisticas['total_lotes'] }}</h3>
        <p>Lotes Activos</p>
      </div>
      <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
      <a href="{{ route('lotes.index') }}" class="small-box-footer">
        Ver lotes <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- Cultivos activos --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $estadisticas['cultivos_activos'] }}</h3>
        <p>Cultivos en Proceso</p>
      </div>
      <div class="icon"><i class="fas fa-seedling"></i></div>
      <a href="{{ route('cultivos.index') }}" class="small-box-footer">
        Ver cultivos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- Actividades pendientes --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $estadisticas['actividades_pendientes'] }}</h3>
        <p>Actividades Pendientes</p>
      </div>
      <div class="icon"><i class="fas fa-tasks"></i></div>
      <a href="{{ route('actividades.index') }}" class="small-box-footer">
        Ver actividades <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- Usuarios (admin) / Actividades completadas (trabajador) --}}
  @if(auth()->user()->isAdmin())
  <div class="col-lg-3 col-6">
    <div class="small-box bg-secondary">
      <div class="inner">
        <h3>{{ $estadisticas['total_usuarios'] }}</h3>
        <p>Usuarios del Sistema</p>
      </div>
      <div class="icon"><i class="fas fa-users"></i></div>
      <a href="{{ route('usuarios.index') }}" class="small-box-footer">
        Gestionar usuarios <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  @else
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $estadisticas['actividades_completadas'] }}</h3>
        <p>Actividades Completadas</p>
      </div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
      <a href="{{ route('actividades.index') }}" class="small-box-footer">
        Ver historial <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  @endif

</div>

{{-- ══ FILA 2: Tablas informativas ═══════════════════════════════════════════ --}}
<div class="row">

  {{-- Actividades próximas (7 días) --}}
  <div class="col-md-7">
    <div class="card card-outline card-warning">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-calendar-alt mr-1 text-warning"></i>
          Actividades próximas <small class="text-muted">(próximos 7 días)</small>
        </h3>
        <div class="card-tools">
          <a href="{{ route('actividades.index') }}" class="btn btn-sm btn-outline-warning">
            Ver todas
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        @if($actividadesProximas->isEmpty())
          <div class="text-center py-4 text-muted">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <p class="mb-0">¡Sin actividades pendientes esta semana!</p>
          </div>
        @else
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>Actividad</th>
                <th>Cultivo / Lote</th>
                <th>Asignado a</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              @foreach($actividadesProximas as $actividad)
              <tr>
                <td>
                  <span class="badge badge-secondary">
                    {{ $actividad->tipoActividad->nombre ?? '—' }}
                  </span>
                  <small class="d-block text-muted">
                    {{ Str::limit($actividad->descripcion, 40) }}
                  </small>
                </td>
                <td>
                  <span class="text-sm">{{ $actividad->cultivo->codigo ?? '—' }}</span>
                  <small class="d-block text-muted">
                    <i class="fas fa-map-pin"></i>
                    Lote {{ $actividad->cultivo->lote->identificador ?? '?' }}
                  </small>
                </td>
                <td>{{ $actividad->asignadoA->nombre ?? '—' }}</td>
                <td>
                  <span class="badge badge-{{ $actividad->fecha_programada->isToday() ? 'danger' : 'warning' }}">
                    {{ $actividad->fecha_programada->format('d/m') }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>

  {{-- Cosechas próximas + Notificaciones --}}
  <div class="col-md-5">

    {{-- Cosechas en los próximos 30 días --}}
    <div class="card card-outline card-success">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-leaf mr-1 text-success"></i>
          Cosechas próximas <small class="text-muted">(30 días)</small>
        </h3>
      </div>
      <div class="card-body p-0">
        @if($cosechasProximas->isEmpty())
          <div class="text-center py-3 text-muted">
            <small>Sin cosechas en los próximos 30 días</small>
          </div>
        @else
          <ul class="list-group list-group-flush">
            @foreach($cosechasProximas as $cultivo)
            <li class="list-group-item px-3 py-2">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $cultivo->codigo }}</strong>
                  <small class="d-block text-muted">
                    {{ $cultivo->variedad->tipoCultivo->nombre ?? '—' }}
                    · {{ $cultivo->variedad->nombre ?? '—' }}
                    · Lote <strong>{{ $cultivo->lote->identificador ?? '?' }}</strong>
                  </small>
                </div>
                <span class="badge badge-success">
                  {{ $cultivo->fecha_cosecha_estimada->format('d/m') }}
                </span>
              </div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>

    {{-- Notificaciones sin leer --}}
    <div class="card card-outline card-info">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bell mr-1 text-info"></i>
          Notificaciones
        </h3>
      </div>
      <div class="card-body p-0">
        @if($notificaciones->isEmpty())
          <div class="text-center py-3 text-muted">
            <i class="fas fa-check text-success"></i>
            <small class="ml-1">Sin notificaciones nuevas</small>
          </div>
        @else
          <ul class="list-group list-group-flush">
            @foreach($notificaciones as $notif)
            <li class="list-group-item px-3 py-2">
              <span class="badge badge-{{ $notif->prioridad === 'alta' ? 'danger' : ($notif->prioridad === 'media' ? 'warning' : 'secondary') }} float-right">
                {{ ucfirst($notif->prioridad) }}
              </span>
              <strong class="d-block">{{ $notif->titulo }}</strong>
              <small class="text-muted">{{ Str::limit($notif->mensaje, 60) }}</small>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>

  </div>

</div>

@endsection
