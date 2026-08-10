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
          <span class="img-circle elevation-2 bg-{{ $usuario->isAdmin() ? 'primary' : 'info' }} d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:100px;height:100px;font-size:3.5rem;">
            {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
          </span>
        </div>

        <h3 class="profile-username text-center">{{ $usuario->nombre }}</h3>
        <p class="text-muted text-center">
          <i class="fas {{ $usuario->isAdmin() ? 'fa-user-shield' : 'fa-user-hard-hat' }} mr-1"></i>
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
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                <p>Este usuario no tiene tareas pendientes en este momento.</p>
              </div>
            @else
              <div class="timeline timeline-inverse">
                @foreach($actividadesPendientes as $act)
                  @php
                    $icono = match($act->tipoActividad->codigo) {
                      'RIEGO' => 'fa-tint bg-primary',
                      'FERTILIZACION' => 'fa-flask bg-warning',
                      'PODA' => 'fa-cut bg-secondary',
                      'FUMIGACION' => 'fa-bug bg-danger',
                      'COSECHA' => 'fa-leaf bg-success',
                      default => 'fa-tasks bg-info'
                    };
                  @endphp
                  <div>
                    <i class="fas {{ $icono }}"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> {{ $act->fecha_programada->format('d/m/Y') }}</span>
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
                  <i class="far fa-clock bg-gray"></i>
                </div>
              </div>
            @endif
          </div>
          
          @if($usuario->esTrabajador())
          <!-- Lotes Asignados -->
          <div class="tab-pane" id="lotes">
            @if($usuario->lotes->isEmpty())
              <div class="text-center py-4 text-muted">
                <small>No tiene lotes asignados actualmente.</small>
              </div>
            @else
              <div class="row">
                @foreach($usuario->lotes as $lote)
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <span class="info-box-icon text-success"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text text-dark">Lote {{ $lote->identificador }}</span>
                      <span class="info-box-number text-muted">{{ $lote->nombre }}</span>
                      <a href="{{ route('lotes.show', $lote) }}" class="text-sm">Ver Lote <i class="fas fa-arrow-circle-right"></i></a>
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
