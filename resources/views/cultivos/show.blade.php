@extends('layouts.adminlte')

@section('title', 'Detalle de Cultivo')
@section('page-title', 'Cultivo ' . $cultivo->codigo)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
  <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4">
    <!-- Tarjeta de Estado Principal -->
    <div class="card card-{{ $cultivo->estaActivo() ? 'success' : 'secondary' }} card-outline">
      <div class="card-body box-profile">
        <div class="text-center mb-3">
          <span class="img-circle elevation-2 bg-{{ $cultivo->estaActivo() ? 'success' : 'secondary' }} d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:80px;height:80px;font-size:2.5rem;">
            <i class="fas fa-seedling"></i>
          </span>
        </div>

        <h3 class="profile-username text-center">{{ $cultivo->codigo }}</h3>
        <p class="text-muted text-center">
          Lote: <a href="{{ route('lotes.show', $cultivo->lote) }}" class="text-dark font-weight-bold">{{ $cultivo->lote->identificador }}</a>
        </p>

        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item">
            <b>Estado</b> 
            @php
              $badge = match($cultivo->estado) {
                'sembrado' => 'info',
                'creciendo' => 'primary',
                'cosechado' => 'success',
                'perdido' => 'danger',
                default => 'secondary'
              };
            @endphp
            <a class="float-right text-{{ $badge }} font-weight-bold">
              {{ ucfirst($cultivo->estado) }}
            </a>
          </li>
          <li class="list-group-item">
            <b>Tipo</b> <a class="float-right text-dark">{{ $cultivo->variedad->tipoCultivo->nombre }}</a>
          </li>
          <li class="list-group-item">
            <b>Variedad</b> <a class="float-right text-dark">{{ $cultivo->variedad->nombre }}</a>
          </li>
        </ul>

        @if($cultivo->estaActivo() || auth()->user()->isAdmin())
        <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-primary btn-block"><b>Actualizar Cultivo</b></a>
        @endif
      </div>
    </div>
    
    <!-- Fechas -->
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><i class="far fa-calendar-alt mr-1"></i> Línea de Tiempo</h3>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <span class="text-muted d-block text-sm">Siembra</span>
            <strong>{{ $cultivo->fecha_siembra->format('d/m/Y') }}</strong>
            <span class="text-xs text-muted float-right">Registrado por {{ $cultivo->registradoPor->nombre }}</span>
          </li>
          <li class="list-group-item">
            <span class="text-muted d-block text-sm">Cosecha Estimada</span>
            <strong class="text-warning">{{ $cultivo->fecha_cosecha_estimada->format('d/m/Y') }}</strong>
            @if($cultivo->estaActivo())
              @php
                 $faltan = now()->diffInDays($cultivo->fecha_cosecha_estimada, false);
                 $textoFalta = $faltan > 0 ? "en {$faltan} días" : ($faltan === 0 ? "¡Hoy!" : "hace " . abs($faltan) . " días");
              @endphp
              <span class="badge badge-{{ $faltan >= 0 ? 'warning' : 'danger' }} float-right">{{ $textoFalta }}</span>
            @endif
          </li>
          @if($cultivo->estado == 'cosechado')
          <li class="list-group-item bg-light">
            <span class="text-muted d-block text-sm">Cosecha Real</span>
            <strong class="text-success">{{ $cultivo->fecha_cosecha_real?->format('d/m/Y') ?? '—' }}</strong>
            <span class="float-right badge badge-success">{{ $cultivo->cantidad_cosechada_kg }} Kg</span>
          </li>
          @endif
        </ul>
      </div>
    </div>

  </div>
  
  <div class="col-md-8">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#actividades" data-toggle="tab">Actividades</a></li>
          <li class="nav-item"><a class="nav-link" href="#observaciones" data-toggle="tab">Observaciones</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          
          <!-- Tab Actividades -->
          <div class="active tab-pane" id="actividades">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0 text-muted">Historial de Tareas</h5>
              @if($cultivo->estaActivo())
              <a href="{{ route('actividades.create', ['cultivo' => $cultivo->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus mr-1"></i> Programar Tarea
              </a>
              @endif
            </div>

            @if($cultivo->actividades->isEmpty())
              <div class="text-center py-5 text-muted">
                <i class="fas fa-tasks fa-3x mb-3 text-light"></i>
                <p>No se han registrado actividades para este cultivo.</p>
              </div>
            @else
              <div class="timeline timeline-inverse">
                @foreach($cultivo->actividades()->orderByDesc('fecha_programada')->get() as $act)
                  @php
                    $icono = match($act->tipoActividad->codigo) {
                      'RIEGO' => 'fa-tint bg-primary',
                      'FERTILIZACION' => 'fa-flask bg-warning',
                      'PODA' => 'fa-cut bg-secondary',
                      'FUMIGACION' => 'fa-bug bg-danger',
                      'COSECHA' => 'fa-leaf bg-success',
                      default => 'fa-check bg-info'
                    };
                  @endphp
                  <div>
                    <i class="fas {{ $icono }}"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> {{ $act->fecha_programada->format('d/m/Y') }}</span>
                      <h3 class="timeline-header">
                        <a href="{{ route('actividades.show', $act) }}">{{ $act->tipoActividad->nombre }}</a>
                        @if($act->estado == 'completada')
                          <span class="badge badge-success ml-1">Completada</span>
                        @elseif($act->estado == 'cancelada')
                          <span class="badge badge-danger ml-1">Cancelada</span>
                        @else
                          <span class="badge badge-warning ml-1">Pendiente</span>
                        @endif
                      </h3>
                      <div class="timeline-body">
                        {{ $act->descripcion }}
                        <br>
                        <small class="text-muted">Asignado a: {{ $act->asignadoA->nombre ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </div>
                @endforeach
                <div>
                  <i class="far fa-clock bg-gray"></i>
                </div>
              </div>
            @endif
          </div>
          
          <!-- Tab Observaciones -->
          <div class="tab-pane" id="observaciones">
            <h5 class="text-muted mb-3">Notas del Cultivo</h5>
            @if($cultivo->observaciones)
              <div class="callout callout-info">
                <p>{!! nl2br(e($cultivo->observaciones)) !!}</p>
              </div>
            @else
              <p class="text-muted font-italic">No hay observaciones registradas para este cultivo.</p>
            @endif
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
