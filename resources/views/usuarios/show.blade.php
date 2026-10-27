@extends('layouts.adminlte')

@section('title', 'Perfil de Usuario')
@section('page-title', 'Perfil: ' . $usuario->nombre)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
  <li class="breadcrumb-item active">Perfil</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4">
    <!-- Tarjeta de Perfil -->
    <div class="card card-{{ $usuario->isAdmin() ? 'primary' : 'info' }} card-outline">
      <div class="card-body box-profile">
        <div class="text-center mb-3">
          @if($usuario->foto_perfil)
            <img src="{{ asset('storage/' . $usuario->foto_perfil) }}"
                 alt="{{ $usuario->nombre }}"
                 class="img-circle elevation-2"
                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #10b981;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <span class="img-circle elevation-2 bg-{{ $usuario->isAdmin() ? 'primary' : 'info' }} align-items-center justify-content-center text-white font-weight-bold"
                  style="display:none; width:100px; height:100px; font-size:3.5rem;">
              {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
            </span>
          @else
            <span class="img-circle elevation-2 bg-{{ $usuario->isAdmin() ? 'primary' : 'info' }} d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                  style="width:100px; height:100px; font-size:3.5rem;">
              {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
            </span>
          @endif
        </div>

        <h3 class="profile-username text-center">{{ $usuario->nombre }}</h3>
        <p class="text-muted text-center">
          <i class="fa-solid {{ $usuario->isAdmin() ? 'fa-user-shield' : 'fa-helmet-safety' }} mr-1"></i>
          {{ $usuario->rol->nombre }}
        </p>

        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item">
            <b>Estado</b> 
            @if($usuario->activo)
              <a class="float-right text-success font-weight-bold">Activo</a>
            @else
              <a class="float-right text-danger font-weight-bold">Inactivo</a>
            @endif
          </li>
          <li class="list-group-item">
            <b>Correo</b> <a class="float-right text-dark" href="mailto:{{ $usuario->email }}">{{ $usuario->email }}</a>
          </li>
          <li class="list-group-item">
            <b>Teléfono</b> <a class="float-right text-dark">{{ $usuario->telefono ?? 'No registrado' }}</a>
          </li>
          <li class="list-group-item">
            <b>Registro</b> <a class="float-right text-dark">{{ $usuario->created_at->format('d/m/Y') }}</a>
          </li>
        </ul>

        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-primary btn-block"><b>Editar Perfil</b></a>
      </div>
    </div>
  </div>
  
  <div class="col-md-8">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#tareas" data-toggle="tab">Tareas Pendientes</a></li>
          @if($usuario->esTrabajador())
          <li class="nav-item"><a class="nav-link" href="#lotes" data-toggle="tab">Lotes Asignados</a></li>
          @endif
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          
          <!-- Tareas Pendientes -->
          <div class="active tab-pane" id="tareas">
            @if($actividadesPendientes->isEmpty())
              <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-circle-check fa-3x mb-3 text-success"></i>
                <p>Este usuario no tiene tareas pendientes en este momento.</p>
              </div>
            @else
              <div class="timeline timeline-inverse">
                @foreach($actividadesPendientes as $act)
                  @php
                    $icono = match($act->tipoActividad->codigo) {
                      'RIEGO' => 'fa-droplet bg-primary',
                      'FERTILIZACION' => 'fa-flask bg-warning',
                      'PODA' => 'fa-scissors bg-secondary',
                      'FUMIGACION' => 'fa-bug bg-danger',
                      'COSECHA' => 'fa-leaf bg-success',
                      default => 'fa-list-check bg-info'
                    };
                  @endphp
                  <div>
                    <i class="fa-solid {{ $icono }}"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="fa-regular fa-clock"></i> {{ $act->fecha_programada->format('d/m/Y') }}</span>
                      <h3 class="timeline-header">
                        <a href="{{ route('actividades.show', $act) }}">{{ $act->tipoActividad->nombre }}</a>
                      </h3>
                      <div class="timeline-body">
                        {{ Str::limit($act->descripcion, 100) }}
                        <br>
                        <small class="text-muted">
                          En cultivo: <a href="{{ route('cultivos.show', $act->cultivo) }}">{{ $act->cultivo->codigo }}</a>
                        </small>
                      </div>
                    </div>
                  </div>
                @endforeach
                <div>
                  <i class="fa-regular fa-clock bg-gray"></i>
                </div>
              </div>
            @endif
          </div>
          
          @if($usuario->esTrabajador())
          <!-- Lotes Asignados -->
          <div class="tab-pane" id="lotes">
            @if($usuario->lotes->isEmpty())
              <div class="text-center py-4 text-muted">
                <i class="fa-solid fa-layer-group fa-2x mb-2 text-muted"></i>
                <p class="m-0">No tiene lotes asignados actualmente.</p>
              </div>
            @else
              <div class="row">
                @foreach($usuario->lotes as $lote)
                @php
                  $badgeColor = match($lote->estado) {
                    'disponible' => 'success',
                    'ocupado' => 'info',
                    'en_descanso' => 'warning',
                    default => 'secondary'
                  };
                @endphp
                <div class="col-md-6 mb-3">
                  <div class="card card-outline card-{{ $badgeColor }} h-100 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="font-weight-bold text-dark m-0">
                          <i class="fa-solid fa-location-dot text-{{ $badgeColor }} mr-1"></i>
                          Lote {{ $lote->identificador }} - {{ $lote->nombre }}
                        </h5>
                        <span class="badge badge-{{ $badgeColor }} text-uppercase px-2 py-1">
                          {{ str_replace('_', ' ', $lote->estado) }}
                        </span>
                      </div>
                      <p class="text-muted text-sm mb-2">
                        <i class="fa-solid fa-vector-square mr-1"></i> Área: <strong>{{ number_format($lote->area_ha, 2) }} ha</strong>
                        @if($lote->ubicacion)
                          <br><i class="fa-solid fa-compass mr-1"></i> {{ $lote->ubicacion }}
                        @endif
                      </p>
                      @if($lote->pivot->creado_en)
                        <small class="text-muted d-block mb-3">
                          <i class="fa-regular fa-calendar-check mr-1"></i> Asignado el: {{ \Carbon\Carbon::parse($lote->pivot->creado_en)->format('d/m/Y') }}
                        </small>
                      @endif
                      <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <a href="{{ route('lotes.show', $lote) }}" class="btn btn-sm btn-outline-primary">
                          Ver Lote <i class="fa-solid fa-circle-arrow-right ml-1"></i>
                        </a>
                        @if(auth()->user()->isAdmin())
                        <form action="{{ route('asignaciones.lotes.desactivar') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar la asignación de este lote para este trabajador? Se conservará el registro histórico.');">
                          @csrf
                          <input type="hidden" name="id_lote" value="{{ $lote->id }}">
                          <input type="hidden" name="id_usuario" value="{{ $usuario->id }}">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar asignación conservando historial">
                            <i class="fa-solid fa-user-minus mr-1"></i> Desactivar
                          </button>
                        </form>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            @endif
          </div>
          @endif
          
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
