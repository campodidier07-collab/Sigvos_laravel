@extends('layouts.adminlte')

@section('title', 'Programar Tarea')
@section('page-title', 'Programar Nueva Tarea')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}">Actividades</a></li>
  <li class="breadcrumb-item active">Programar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">Datos de la Actividad</h3>
      </div>
      
      <form action="{{ route('actividades.store') }}" method="POST">
        @csrf
        <div class="card-body">
          
          @if($cultivos->isEmpty())
            <div class="alert alert-info">
              <h5><i class="icon fas fa-info"></i> No hay cultivos activos</h5>
              No tienes ningún cultivo en proceso al que se le puedan asignar tareas. Primero debes <a href="{{ route('cultivos.create') }}" class="text-white font-weight-bold" style="text-decoration: underline;">registrar una siembra</a>.
            </div>
          @else
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="id_cultivo">Cultivo Objetivo <span class="text-danger">*</span></label>
                  <select class="form-control @error('id_cultivo') is-invalid @enderror" id="id_cultivo" name="id_cultivo" required>
                    <option value="">-- Seleccionar Cultivo --</option>
                    @foreach($cultivos as $cultivo)
                      <option value="{{ $cultivo->id }}" {{ (old('id_cultivo') == $cultivo->id || $cultivoPreseleccionado == $cultivo->id) ? 'selected' : '' }}>
                        {{ $cultivo->codigo }} (Lote {{ $cultivo->lote->identificador }})
                      </option>
                    @endforeach
                  </select>
                  @error('id_cultivo')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="id_tipo_actividad">Tipo de Tarea <span class="text-danger">*</span></label>
                  <select class="form-control @error('id_tipo_actividad') is-invalid @enderror" id="id_tipo_actividad" name="id_tipo_actividad" required>
                    <option value="">-- Seleccionar Tipo --</option>
                    @foreach($tiposActividad as $tipo)
                      <option value="{{ $tipo->id }}" {{ old('id_tipo_actividad') == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nombre }}
                      </option>
                    @endforeach
                  </select>
                  @error('id_tipo_actividad')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="asignado_a">Asignar a Trabajador <span class="text-danger">*</span></label>
                  <select class="form-control @error('asignado_a') is-invalid @enderror" id="asignado_a" name="asignado_a" required>
                    <option value="">-- Seleccionar Trabajador --</option>
                    @foreach($trabajadores as $trabajador)
                      <option value="{{ $trabajador->id }}" {{ old('asignado_a') == $trabajador->id ? 'selected' : '' }}>
                        {{ $trabajador->nombre }}
                      </option>
                    @endforeach
                  </select>
                  @error('asignado_a')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fecha_programada">Fecha a Realizar <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('fecha_programada') is-invalid @enderror" 
                         id="fecha_programada" name="fecha_programada" value="{{ old('fecha_programada', date('Y-m-d', strtotime('+1 day'))) }}" required>
                  @error('fecha_programada')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="descripcion">Descripción de la Tarea <span class="text-danger">*</span></label>
              <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                        id="descripcion" name="descripcion" rows="3" 
                        placeholder="Ej. Aplicar 2kg de fertilizante NPK por planta..." required>{{ old('descripcion') }}</textarea>
              @error('descripcion')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="observaciones">Notas Internas (Opcional)</label>
              <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                        id="observaciones" name="observaciones" rows="2" 
                        placeholder="Notas para el administrador...">{{ old('observaciones') }}</textarea>
              @error('observaciones')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

          @endif
        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('actividades.index') }}" class="btn btn-default mr-2">Cancelar</a>
          @if($cultivos->isNotEmpty())
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-calendar-check mr-1"></i> Programar Tarea
            </button>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
