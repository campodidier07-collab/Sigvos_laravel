@extends('layouts.adminlte')

@section('title', 'Lotes Agrícolas')
@section('page-title', 'Gestión de Lotes')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Lotes</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">

    <div class="card card-outline card-success">
      <div class="card-header">
        <h3 class="card-title">Listado de Lotes</h3>
        <div class="card-tools d-flex">
          <form action="{{ route('lotes.index') }}" method="GET" class="input-group input-group-sm mr-2" style="width: 200px;">
            <input type="text" name="buscar" class="form-control float-right" placeholder="Buscar..." value="{{ request('buscar') }}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-default">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </form>
          @if(auth()->user()->isAdmin())
          <a href="{{ route('lotes.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Nuevo Lote
          </a>
          @endif
        </div>
      </div>
      <!-- /.card-header -->
      
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">ID</th>
              <th>Nombre</th>
              <th>Ubicación</th>
              <th>Área (ha)</th>
              <th>Cultivo Actual</th>
              <th>Estado</th>
              <th style="width: 150px;" class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lotes as $lote)
              <tr class="{{ !$lote->activo ? 'text-muted' : '' }}">
                <td class="text-center">
                  <span class="badge badge-success" style="font-size: 1.1rem;">
                    {{ $lote->identificador }}
                  </span>
                </td>
                <td>
                  <strong>{{ $lote->nombre }}</strong>
                  @if($lote->es_alternativo)
                    <span class="badge badge-info ml-1" title="Lote Alternativo">Alt</span>
                  @endif
                  @if(!$lote->activo)
                    <span class="badge badge-secondary ml-1">Inactivo</span>
                  @endif
                </td>
                <td>
                  <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                  {{ Str::limit($lote->ubicacion, 30) }}
                </td>
                <td>{{ number_format($lote->area_ha, 2) }} ha</td>
                <td>
                  @if($lote->cultivoActivo)
                    <span class="text-success font-weight-bold">
                      <i class="fas fa-seedling mr-1"></i>
                      {{ $lote->cultivoActivo->codigo }}
                    </span>
                    <small class="d-block text-muted">
                      {{ $lote->cultivoActivo->variedad->nombre ?? '' }}
                    </small>
                  @else
                    <span class="text-muted">Ninguno</span>
                  @endif
                </td>
                <td>
                  @php
                    $badgeClase = match($lote->estado) {
                      'disponible' => 'success',
                      'ocupado' => 'warning',
                      'mantenimiento' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge badge-{{ $badgeClase }}">
                    {{ ucfirst($lote->estado) }}
                  </span>
                </td>
                <td class="text-center">
                  <a href="{{ route('lotes.show', $lote) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                  </a>
                  @if(auth()->user()->isAdmin())
                  <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar/desactivar este lote?');">
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
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="fas fa-map-marked-alt fa-3x mb-3 text-light"></i>
                  <h5>No se encontraron lotes registrados.</h5>
                  <p>Agrega un nuevo lote para comenzar a gestionar tus terrenos.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
      
      @if($lotes->hasPages())
      <div class="card-footer clearfix">
        {{ $lotes->withQueryString()->links('pagination::bootstrap-4') }}
      </div>
      @endif

    </div>
    <!-- /.card -->

  </div>
</div>
@endsection
