@extends('layouts.adminlte')

@section('title', 'Editar Cultivo')
@section('page-title', 'Editar Cultivo: ' . $cultivo->codigo)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
  <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Actualizar Datos y Estado</h3>
      </div>
      
      <form action="{{ route('cultivos.update', $cultivo) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card-body">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Lote Actual (Solo lectura)</label>
                <input type="text" class="form-control" value="Lote {{ $cultivo->lote->identificador }} - {{ $cultivo->lote->nombre }}" disabled>
                <small class="text-muted">El cultivo está vinculado a este lote. Para cambiarlo, debes registrar un nuevo cultivo.</small>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="id_variedad">Cultivo a Sembrar (Variedad) <span class="text-danger">*</span></label>
                <select class="form-control @error('id_variedad') is-invalid @enderror" id="id_variedad" name="id_variedad" required>
                  <option value="">-- Seleccionar Variedad --</option>
                  @foreach($variedades as $variedad)
                    <option value="{{ $variedad->id }}" {{ old('id_variedad', $cultivo->id_variedad) == $variedad->id ? 'selected' : '' }}>
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

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="codigo">Código Identificador <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                       id="codigo" name="codigo" value="{{ old('codigo', $cultivo->codigo) }}" required>
                @error('codigo')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">Estado del Cultivo <span class="text-danger">*</span></label>
                <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required onchange="toggleCosechaFields()">
                  <option value="sembrado" {{ old('estado', $cultivo->estado) == 'sembrado' ? 'selected' : '' }}>Sembrado</option>
                  <option value="creciendo" {{ old('estado', $cultivo->estado) == 'creciendo' ? 'selected' : '' }}>Creciendo</option>
                  <option value="cosechado" {{ old('estado', $cultivo->estado) == 'cosechado' ? 'selected' : '' }}>Cosechado (Finalizar ciclo)</option>
                  <option value="perdido" {{ old('estado', $cultivo->estado) == 'perdido' ? 'selected' : '' }}>Perdido (Finalizar ciclo)</option>
                </select>
                @error('estado')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_siembra">Fecha de Siembra <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_siembra') is-invalid @enderror" 
                       id="fecha_siembra" name="fecha_siembra" value="{{ old('fecha_siembra', $cultivo->fecha_siembra->format('Y-m-d')) }}" required>
                @error('fecha_siembra')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_cosecha_estimada">Fecha de Cosecha Estimada <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_cosecha_estimada') is-invalid @enderror" 
                       id="fecha_cosecha_estimada" name="fecha_cosecha_estimada" value="{{ old('fecha_cosecha_estimada', $cultivo->fecha_cosecha_estimada->format('Y-m-d')) }}" required>
                @error('fecha_cosecha_estimada')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <!-- Campos adicionales si el estado es 'cosechado' -->
          <div id="div_cosecha" style="display: {{ in_array(old('estado', $cultivo->estado), ['cosechado']) ? 'block' : 'none' }};">
            <hr>
            <h5 class="text-success"><i class="fas fa-check-circle"></i> Datos de la Cosecha</h5>
            <p class="text-muted small mb-3">Registrar esta información liberará el lote para futuras siembras.</p>
            
            <div class="row bg-light pt-3 pb-2 rounded border">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fecha_cosecha_real">Fecha Real de Cosecha</label>
                  <input type="date" class="form-control @error('fecha_cosecha_real') is-invalid @enderror" 
                         id="fecha_cosecha_real" name="fecha_cosecha_real" 
                         value="{{ old('fecha_cosecha_real', $cultivo->fecha_cosecha_real?->format('Y-m-d')) }}">
                  @error('fecha_cosecha_real')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="cantidad_cosechada_kg">Cantidad Cosechada (Kg)</label>
                  <div class="input-group">
                    <input type="number" step="0.01" class="form-control @error('cantidad_cosechada_kg') is-invalid @enderror" 
                           id="cantidad_cosechada_kg" name="cantidad_cosechada_kg" 
                           value="{{ old('cantidad_cosechada_kg', $cultivo->cantidad_cosechada_kg) }}">
                    <div class="input-group-append">
                      <span class="input-group-text">Kg</span>
                    </div>
                    @error('cantidad_cosechada_kg')
                      <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Alerta para perdido -->
          <div id="div_perdido" class="mt-3 alert alert-danger" style="display: {{ in_array(old('estado', $cultivo->estado), ['perdido']) ? 'block' : 'none' }};">
            <h5><i class="icon fas fa-ban"></i> Atención</h5>
            Marcar este cultivo como perdido cerrará su ciclo y liberará el lote para nuevas siembras. Por favor, documenta las razones en las observaciones.
          </div>

          <div class="form-group mt-3">
            <label for="observaciones">Observaciones</label>
            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                      id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $cultivo->observaciones) }}</textarea>
            @error('observaciones')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('cultivos.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Actualizar Cultivo
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function toggleCosechaFields() {
    var estado = document.getElementById('estado').value;
    var divCosecha = document.getElementById('div_cosecha');
    var divPerdido = document.getElementById('div_perdido');
    
    if (estado === 'cosechado') {
      divCosecha.style.display = 'block';
      divPerdido.style.display = 'none';
    } else if (estado === 'perdido') {
      divCosecha.style.display = 'none';
      divPerdido.style.display = 'block';
    } else {
      divCosecha.style.display = 'none';
      divPerdido.style.display = 'none';
    }
  }
</script>
@endsection
