@extends('layouts.adminlte')

@section('title', 'Actividades Agrícolas')
@section('page-title', 'Gestión de Tareas y Actividades')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Actividades</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">

    <div class="card card-outline card-warning">
      <div class="card-header">
        <h3 class="card-title">Listado de Tareas</h3>
        
        <div class="card-tools d-flex">
          <!-- Filtro de estado -->
          <form action="{{ route('actividades.index') }}" method="GET" class="mr-2" id="form-filtro">
            <select name="estado" class="form-control form-control-sm" onchange="document.getElementById('form-filtro').submit();">
              <option value="">Todos los estados</option>
              <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
              <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
              <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
            </select>
          </form>

          <!-- Buscador -->
          <form action="{{ route('actividades.index') }}" method="GET" class="input-group input-group-sm mr-2" style="width: 200px;">
            <input type="hidden" name="estado" value="{{ request('estado') }}">
            <input type="text" name="buscar" class="form-control float-right" placeholder="Buscar (ej. Riego...)" value="{{ request('buscar') }}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
            </div>
          </form>

          <!-- Botón Nueva Tarea (Solo Admin) -->
          @if(auth()->user()->isAdmin())
          <a href="{{ route('actividades.create') }}" class="btn btn-sm btn-warning">
            <i class="fas fa-plus"></i> Programar Tarea
          </a>
          @endif
        </div>
      </div>
      
      <!-- Cuerpo de la Tabla -->
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Actividad</th>
              <th>Cultivo / Lote</th>
              <th>Asignado A</th>
              <th>Fecha Prog.</th>
              <th>Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($actividades as $act)
              <tr class="{{ $act->estado == 'cancelada' ? 'text-muted bg-light' : '' }}">
                <td>#{{ $act->id }}</td>
                <td>
                  <span class="font-weight-bold d-block">{{ $act->tipoActividad->nombre }}</span>
                  <small class="text-muted text-wrap" style="max-width: 200px;">{{ Str::limit($act->descripcion, 40) }}</small>
                </td>
                <td>
                  <a href="{{ route('cultivos.show', $act->cultivo) }}" class="text-dark font-weight-bold">
                    {{ $act->cultivo->codigo }}
                  </a>
                  <br>
                  <small class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> Lote {{ $act->cultivo->lote->identificador }}</small>
                </td>
                <td>
                  <i class="fas fa-user text-info"></i> {{ $act->asignadoA->nombre ?? 'N/A' }}
                </td>
                <td>
                  <span class="badge badge-{{ $act->fecha_programada->isPast() && $act->estado == 'pendiente' ? 'danger' : 'secondary' }}">
                    {{ $act->fecha_programada->format('d/m/Y') }}
                  </span>
                </td>
                <td>
                  @php
                    $badge = match($act->estado) {
                      'pendiente' => 'warning',
                      'completada' => 'success',
                      'cancelada' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge badge-{{ $badge }}">{{ ucfirst($act->estado) }}</span>
                </td>
                <td class="text-center">
                  <a href="{{ route('actividades.show', $act) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                  </a>
                  
                  <a href="{{ route('actividades.edit', $act) }}" class="btn btn-sm btn-primary" title="Editar / Reportar">
                    <i class="fas fa-edit"></i>
                  </a>

                  @if(auth()->user()->isAdmin())
                  <form action="{{ route('actividades.destroy', $act) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fas fa-tasks fa-3x mb-3 text-light"></i>
                  <h5>No hay tareas registradas.</h5>
                  <p>Inicia programando una nueva actividad agrícola.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      <!-- Paginación -->
      @if($actividades->hasPages())
      <div class="card-footer clearfix">
        {{ $actividades->withQueryString()->links('pagination::bootstrap-4') }}
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
