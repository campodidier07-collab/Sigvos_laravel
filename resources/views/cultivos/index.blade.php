@extends('layouts.adminlte')

@section('title', 'Cultivos')
@section('page-title', 'Gestión de Cultivos')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Cultivos</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">

    <!-- Tarjeta Principal -->
    <div class="card card-outline card-success">
      <div class="card-header">
        <h3 class="card-title">Listado de Cultivos Registrados</h3>
        
        <div class="card-tools d-flex">
          <!-- Filtro de estado -->
          <form action="{{ route('cultivos.index') }}" method="GET" class="mr-2" id="form-filtro">
            <select name="estado" class="form-control form-control-sm" onchange="document.getElementById('form-filtro').submit();">
              <option value="">Todos los estados</option>
              <option value="sembrado" {{ request('estado') == 'sembrado' ? 'selected' : '' }}>Sembrado</option>
              <option value="creciendo" {{ request('estado') == 'creciendo' ? 'selected' : '' }}>Creciendo</option>
              <option value="cosechado" {{ request('estado') == 'cosechado' ? 'selected' : '' }}>Cosechado</option>
              <option value="perdido" {{ request('estado') == 'perdido' ? 'selected' : '' }}>Perdido</option>
            </select>
          </form>

          <!-- Buscador -->
          <form action="{{ route('cultivos.index') }}" method="GET" class="input-group input-group-sm mr-2" style="width: 200px;">
            <input type="hidden" name="estado" value="{{ request('estado') }}">
            <input type="text" name="buscar" class="form-control float-right" placeholder="Buscar código..." value="{{ request('buscar') }}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
            </div>
          </form>

          <!-- Botón Nuevo (Solo Admin o quienes puedan) -->
          <a href="{{ route('cultivos.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Registrar Siembra
          </a>
        </div>
      </div>
      
      <!-- Cuerpo de la Tabla -->
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
          <thead>
            <tr>
              <th>Código</th>
              <th>Lote</th>
              <th>Cultivo / Variedad</th>
              <th>Fecha Siembra</th>
              <th>Estado</th>
              <th>Progreso</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cultivos as $cultivo)
              <tr class="{{ !$cultivo->estaActivo() ? 'bg-light text-muted' : '' }}">
                <td>
                  <strong>{{ $cultivo->codigo }}</strong>
                </td>
                <td>
                  <a href="{{ route('lotes.show', $cultivo->lote) }}" class="text-dark">
                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> Lote {{ $cultivo->lote->identificador }}
                  </a>
                </td>
                <td>
                  <span class="d-block">{{ $cultivo->variedad->tipoCultivo->nombre ?? '—' }}</span>
                  <small class="text-muted">{{ $cultivo->variedad->nombre ?? '—' }}</small>
                </td>
                <td>{{ $cultivo->fecha_siembra->format('d/m/Y') }}</td>
                <td>
                  @php
                    $badge = match($cultivo->estado) {
                      'sembrado' => 'info',
                      'creciendo' => 'primary',
                      'cosechado' => 'success',
                      'perdido' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge badge-{{ $badge }}">{{ ucfirst($cultivo->estado) }}</span>
                </td>
                <td>
                  @if($cultivo->estaActivo())
                    @php
                       $inicio = $cultivo->fecha_siembra;
                       $fin = $cultivo->fecha_cosecha_estimada;
                       $hoy = now();
                       $total = max(1, $inicio->diffInDays($fin));
                       $pasados = $inicio->diffInDays($hoy);
                       $prog = min(100, max(0, round(($pasados / $total) * 100)));
                    @endphp
                    <div class="progress progress-sm mt-1 mb-1">
                      <div class="progress-bar bg-success" style="width: {{ $prog }}%"></div>
                    </div>
                    <small>{{ $prog }}% ({{ $cultivo->fecha_cosecha_estimada->format('M Y') }})</small>
                  @else
                    <small>Ciclo finalizado</small>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('cultivos.show', $cultivo) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                  </a>
                  
                  <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-sm btn-primary" title="Editar / Actualizar Estado">
                    <i class="fas fa-edit"></i>
                  </a>

                  @if(auth()->user()->isAdmin())
                  <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este cultivo de la base de datos? Esto es irreversible.');">
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
                  <i class="fas fa-seedling fa-3x mb-3 text-light"></i>
                  <h5>No hay cultivos registrados.</h5>
                  <p>Inicia registrando una nueva siembra en uno de tus lotes disponibles.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      <!-- Paginación -->
      @if($cultivos->hasPages())
      <div class="card-footer clearfix">
        {{ $cultivos->withQueryString()->links('pagination::bootstrap-4') }}
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
