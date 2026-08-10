@extends('layouts.adminlte')

@section('title', 'Editar Actividad')
@section('page-title', 'Actualizar Tarea #' . $actividad->id)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}">Actividades</a></li>
  <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          @if(auth()->user()->isAdmin())
            Editar o Reportar Tarea
          @else
            Reportar Ejecución de Tarea
          @endif
        </h3>
      </div>
      
      <form action="{{ route('actividades.update', $actividad) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card-body">
          
          <!-- Bloque de lectura para trabajadores o admin -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Cultivo Asignado</label>
                <input type="text" class="form-control" value="{{ $actividad->cultivo->codigo }} (Lote {{ $actividad->cultivo->lote->identificador }})" disabled>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="id_tipo_actividad">Tipo de Tarea @if(auth()->user()->isAdmin())<span class="text-danger">*</span>@endif</label>
                @if(auth()->user()->isAdmin())
                  <select class="form-control @error('id_tipo_actividad') is-invalid @enderror" id="id_tipo_actividad" name="id_tipo_actividad" required>
                    @foreach($tiposActividad as $tipo)
                      <option value="{{ $tipo->id }}" {{ old('id_tipo_actividad', $actividad->id_tipo_actividad) == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nombre }}
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="text" class="form-control" value="{{ $actividad->tipoActividad->nombre }}" disabled>
                @endif
                @error('id_tipo_actividad')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="asignado_a">Trabajador Asignado @if(auth()->user()->isAdmin())<span class="text-danger">*</span>@endif</label>
                @if(auth()->user()->isAdmin())
                  <select class="form-control @error('asignado_a') is-invalid @enderror" id="asignado_a" name="asignado_a" required>
                    @foreach($trabajadores as $trabajador)
                      <option value="{{ $trabajador->id }}" {{ old('asignado_a', $actividad->asignado_a) == $trabajador->id ? 'selected' : '' }}>
                        {{ $trabajador->nombre }}
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="text" class="form-control" value="{{ $actividad->asignadoA->nombre ?? 'N/A' }}" disabled>
                @endif
                @error('asignado_a')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_programada">Fecha Programada @if(auth()->user()->isAdmin())<span class="text-danger">*</span>@endif</label>
                @if(auth()->user()->isAdmin())
                  <input type="date" class="form-control @error('fecha_programada') is-invalid @enderror" 
                         id="fecha_programada" name="fecha_programada" value="{{ old('fecha_programada', $actividad->fecha_programada->format('Y-m-d')) }}" required>
                @else
                  <input type="date" class="form-control" value="{{ $actividad->fecha_programada->format('Y-m-d') }}" disabled>
                @endif
                @error('fecha_programada')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="descripcion">Descripción de la Tarea <span class="text-danger">*</span></label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                      id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion', $actividad->descripcion) }}</textarea>
            @error('descripcion')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <hr>

          <h5 class="text-primary"><i class="fas fa-clipboard-check"></i> Reporte de Ejecución</h5>
          
          <div class="row bg-light pt-3 pb-2 rounded border">
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">Estado Actual <span class="text-danger">*</span></label>
                <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required onchange="toggleEjecucion()">
                  <option value="pendiente" {{ old('estado', $actividad->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                  <option value="completada" {{ old('estado', $actividad->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                  <option value="cancelada" {{ old('estado', $actividad->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('estado')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group" id="div_fecha_ejecucion" style="display: {{ old('estado', $actividad->estado) == 'completada' ? 'block' : 'none' }};">
                <label for="fecha_ejecucion">Fecha de Ejecución Real</label>
                <input type="date" class="form-control @error('fecha_ejecucion') is-invalid @enderror" 
                       id="fecha_ejecucion" name="fecha_ejecucion" 
                       value="{{ old('fecha_ejecucion', $actividad->fecha_ejecucion?->format('Y-m-d') ?? date('Y-m-d')) }}">
                <small class="text-muted">¿Cuándo se realizó realmente?</small>
                @error('fecha_ejecucion')
                  <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="form-group mt-3">
            <label for="observaciones">Observaciones de Ejecución</label>
            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                      id="observaciones" name="observaciones" rows="2" 
                      placeholder="Novedades durante la ejecución...">{{ old('observaciones', $actividad->observaciones) }}</textarea>
            @error('observaciones')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('actividades.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Actualizar Reporte
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function toggleEjecucion() {
    var estado = document.getElementById('estado').value;
    var divFecha = document.getElementById('div_fecha_ejecucion');
    
    if (estado === 'completada') {
      divFecha.style.display = 'block';
    } else {
      divFecha.style.display = 'none';
    }
  }
</script>
@endsection
