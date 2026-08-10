@extends('layouts.adminlte')

@section('title', 'Registrar Siembra')
@section('page-title', 'Registrar Nuevo Cultivo')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
  <li class="breadcrumb-item active">Registrar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-success card-outline">
      <div class="card-header">
        <h3 class="card-title">Datos de la Siembra</h3>
      </div>
      
      <form action="{{ route('cultivos.store') }}" method="POST">
        @csrf
        <div class="card-body">
          
          @if($lotes->isEmpty())
            <div class="alert alert-warning">
              <h5><i class="icon fas fa-exclamation-triangle"></i> No hay lotes disponibles</h5>
              Todos los lotes están ocupados actualmente o no tienes ninguno asignado.
              Debes liberar un lote (marcando su cultivo actual como cosechado/perdido) o registrar uno nuevo.
            </div>
          @else
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="id_lote">Lote <span class="text-danger">*</span></label>
                  <select class="form-control @error('id_lote') is-invalid @enderror" id="id_lote" name="id_lote" required>
                    <option value="">-- Seleccionar Lote --</option>
                    @foreach($lotes as $lote)
                      <option value="{{ $lote->id }}" {{ (old('id_lote') == $lote->id || $lotePreseleccionado == $lote->id) ? 'selected' : '' }}>
                        Lote {{ $lote->identificador }} - {{ $lote->nombre }}
                      </option>
                    @endforeach
                  </select>
                  @error('id_lote')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="id_variedad">Cultivo a Sembrar (Variedad) <span class="text-danger">*</span></label>
                  <select class="form-control @error('id_variedad') is-invalid @enderror" id="id_variedad" name="id_variedad" required>
                    <option value="">-- Seleccionar Variedad --</option>
                    @foreach($variedades as $variedad)
                      <option value="{{ $variedad->id }}" {{ old('id_variedad') == $variedad->id ? 'selected' : '' }}>
                        {{ $variedad->tipoCultivo->nombre }} - {{ $variedad->nombre }}
                      </option>
                    @endforeach
                  </select>
                  @error('id_variedad')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="codigo">Código Identificador (Referencia Interna) <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                     id="codigo" name="codigo" value="{{ old('codigo', 'CULT-' . date('ymd-Hi')) }}" required>
              <small class="text-muted">Puedes dejar el sugerido o ingresar uno personalizado (ej. MAIZ-A-2025).</small>
              @error('codigo')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fecha_siembra">Fecha de Siembra <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('fecha_siembra') is-invalid @enderror" 
                         id="fecha_siembra" name="fecha_siembra" value="{{ old('fecha_siembra', date('Y-m-d')) }}" required>
                  @error('fecha_siembra')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fecha_cosecha_estimada">Fecha de Cosecha Estimada <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('fecha_cosecha_estimada') is-invalid @enderror" 
                         id="fecha_cosecha_estimada" name="fecha_cosecha_estimada" value="{{ old('fecha_cosecha_estimada') }}" required>
                  <small class="text-muted">Calcula según los días promedio de la variedad elegida.</small>
                  @error('fecha_cosecha_estimada')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="observaciones">Observaciones (Opcional)</label>
              <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                        id="observaciones" name="observaciones" rows="3" 
                        placeholder="Ej. Semilla tratada con fungicida...">{{ old('observaciones') }}</textarea>
              @error('observaciones')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

          @endif
        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('cultivos.index') }}" class="btn btn-default mr-2">Cancelar</a>
          @if($lotes->isNotEmpty())
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save mr-1"></i> Registrar Cultivo
            </button>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
