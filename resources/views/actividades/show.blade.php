@extends('layouts.adminlte')

@section('title', 'Detalle de Actividad')
@section('page-title', 'Actividad #' . $actividad->id)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}">Actividades</a></li>
  <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4">
    <!-- Resumen de Actividad -->
    <div class="card card-{{ $actividad->estado == 'completada' ? 'success' : ($actividad->estado == 'cancelada' ? 'danger' : 'warning') }} card-outline">
      <div class="card-body box-profile">
        <div class="text-center mb-3">
          @php
            $icono = match($actividad->tipoActividad->codigo) {
              'RIEGO' => 'fa-tint',
              'FERTILIZACION' => 'fa-flask',
              'PODA' => 'fa-cut',
              'FUMIGACION' => 'fa-bug',
              'COSECHA' => 'fa-leaf',
              default => 'fa-tasks'
            };
          @endphp
          <span class="img-circle elevation-2 bg-{{ $actividad->estado == 'completada' ? 'success' : ($actividad->estado == 'cancelada' ? 'danger' : 'warning') }} d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:80px;height:80px;font-size:2.5rem;">
            <i class="fas {{ $icono }}"></i>
          </span>
        </div>

        <h3 class="profile-username text-center">{{ $actividad->tipoActividad->nombre }}</h3>
        <p class="text-muted text-center">{{ $actividad->tipoActividad->descripcion }}</p>

        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item">
            <b>Estado</b> 
            @php
              $badge = match($actividad->estado) {
                'pendiente' => 'warning',
                'completada' => 'success',
                'cancelada' => 'danger',
                default => 'secondary'
              };
            @endphp
            <a class="float-right badge badge-{{ $badge }} font-weight-bold" style="font-size: 0.9rem;">
              {{ ucfirst($actividad->estado) }}
            </a>
          </li>
          <li class="list-group-item">
            <b>Fecha Prog.</b> 
            <a class="float-right text-{{ $actividad->fecha_programada->isPast() && $actividad->estado == 'pendiente' ? 'danger font-weight-bold' : 'dark' }}">
              {{ $actividad->fecha_programada->format('d/m/Y') }}
            </a>
          </li>
          <li class="list-group-item">
            <b>Asignado a</b> 
            <a class="float-right text-dark">
              <i class="fas fa-user-circle text-info"></i> {{ $actividad->asignadoA->nombre ?? 'N/A' }}
            </a>
          </li>
        </ul>

        @if(auth()->user()->isAdmin() || auth()->id() == $actividad->asignado_a)
        <a href="{{ route('actividades.edit', $actividad) }}" class="btn btn-primary btn-block"><b>Actualizar / Reportar</b></a>
        @endif
      </div>
    </div>
  </div>
  
  <div class="col-md-8">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#info" data-toggle="tab">Información Detallada</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          
          <div class="active tab-pane" id="info">
            
            <div class="callout callout-info mb-4">
              <h5><i class="fas fa-seedling text-success mr-2"></i> Cultivo Objetivo</h5>
              <p class="mb-0">
                Esta actividad está programada para el cultivo 
                <strong><a href="{{ route('cultivos.show', $actividad->cultivo) }}" class="text-success">{{ $actividad->cultivo->codigo }}</a></strong>, 
                ubicado en el <strong>Lote {{ $actividad->cultivo->lote->identificador }}</strong>.
              </p>
            </div>

            <h5 class="text-primary"><i class="fas fa-align-left mr-1"></i> Descripción de la Tarea</h5>
            <p class="text-muted bg-light p-3 rounded border">
              {{ $actividad->descripcion }}
            </p>

            <hr>

            <h5 class="text-success"><i class="fas fa-clipboard-check mr-1"></i> Reporte de Ejecución</h5>
            @if($actividad->estado == 'completada')
              <div class="row">
                <div class="col-sm-6">
                  <dl>
                    <dt>Fecha Real de Ejecución</dt>
                    <dd class="text-success font-weight-bold">{{ $actividad->fecha_ejecucion->format('d/m/Y') }}</dd>
                    <dt>Ejecutado por</dt>
                    <dd>{{ $actividad->ejecutadoPor->nombre ?? 'Desconocido' }}</dd>
                  </dl>
                </div>
                <div class="col-sm-6">
                  <dt>Observaciones durante la ejecución</dt>
                  <dd class="text-muted font-italic">
                    {{ $actividad->observaciones ?: 'Sin observaciones registradas.' }}
                  </dd>
                </div>
              </div>
            @elseif($actividad->estado == 'cancelada')
              <div class="alert alert-danger">
                Esta actividad fue cancelada. <br>
                <strong>Motivo/Observación:</strong> {{ $actividad->observaciones ?: 'No especificado.' }}
              </div>
            @else
              <div class="text-center py-4 text-muted">
                <i class="far fa-clock fa-2x mb-2 text-warning"></i>
                <p>La actividad aún está pendiente de ejecución.</p>
              </div>
            @endif

            <hr>
            <div class="text-muted text-right small">
              Programada por {{ $actividad->creadoPor->nombre ?? 'Sistema' }} el {{ $actividad->created_at->format('d/m/Y H:i') }}
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
