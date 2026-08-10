@extends('layouts.adminlte')

@section('title', 'Editar Lote')
@section('page-title', 'Editar Lote: ' . $lote->identificador)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
  <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Datos del Lote</h3>
      </div>
      
      <form action="{{ route('lotes.update', $lote) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card-body">
          <div class="row">
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="identificador">Identificador <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('identificador') is-invalid @enderror" 
                       id="identificador" name="identificador" value="{{ old('identificador', $lote->identificador) }}" 
                       placeholder="Ej. A" maxlength="1" required>
                @error('identificador')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group">
                <label for="nombre">Nombre / Alias <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" value="{{ old('nombre', $lote->nombre) }}" 
                       required>
                @error('nombre')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>

          </div>

          <div class="form-group">
            <label for="ubicacion">Ubicación / Coordenadas <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('ubicacion') is-invalid @enderror" 
                   id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $lote->ubicacion) }}" 
                   required>
            @error('ubicacion')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="area_ha">Área Total (Hectáreas) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0.01" class="form-control @error('area_ha') is-invalid @enderror" 
                         id="area_ha" name="area_ha" value="{{ old('area_ha', $lote->area_ha) }}" required>
                  <div class="input-group-append">
                    <span class="input-group-text">ha</span>
                  </div>
                  @error('area_ha')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="id_tipo_preferido">Cultivo Preferido (Opcional)</label>
                <select class="form-control @error('id_tipo_preferido') is-invalid @enderror" id="id_tipo_preferido" name="id_tipo_preferido">
                  <option value="">-- Seleccionar --</option>
                  @foreach($tiposCultivo as $tipo)
                    <option value="{{ $tipo->id }}" {{ old('id_tipo_preferido', $lote->id_tipo_preferido) == $tipo->id ? 'selected' : '' }}>
                      {{ $tipo->nombre }}
                    </option>
                  @endforeach
                </select>
                @error('id_tipo_preferido')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="es_alternativo" name="es_alternativo" value="1" {{ old('es_alternativo', $lote->es_alternativo) ? 'checked' : '' }}>
              <label class="custom-control-label" for="es_alternativo">Lote Alternativo</label>
            </div>
          </div>

          <hr>
          
          <div class="form-group mb-0">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ old('activo', $lote->activo) ? 'checked' : '' }}>
              <label class="custom-control-label" for="activo">Lote Activo</label>
              <small class="form-text text-muted d-block">Si desactivas el lote, ya no estará disponible para nuevos cultivos.</small>
            </div>
          </div>

        </div>
        <!-- /.card-body -->
        
        <div class="card-footer text-right">
          <a href="{{ route('lotes.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Actualizar Lote
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
