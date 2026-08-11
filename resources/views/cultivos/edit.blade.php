@extends('layouts.adminlte')

@section('title', 'Editar Cultivo')

@push('styles')
<style>
    .glass-form-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(29, 69, 51, 0.08);
        padding: 40px;
        margin-top: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(58, 165, 116, 0.1);
    }
    .form-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #065f46;
        margin-bottom: 1.5rem;
        font-family: 'Outfit', sans-serif;
    }
    
    .custom-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
        letter-spacing: 0.05em;
    }
    .custom-input {
        width: 100%;
        padding: 12px 16px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        color: #334155;
        transition: all 0.2s;
    }
    .custom-input:focus {
        outline: none;
        border-color: #34d399;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15);
    }
    .custom-input::placeholder {
        color: #94a3b8;
    }
    .custom-input[disabled] {
        background-color: #e2e8f0;
        color: #64748b;
        cursor: not-allowed;
    }
    textarea.custom-input {
        min-height: 100px;
        resize: vertical;
    }
    
    .is-invalid-custom {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
    }
    .error-text {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 4px;
        font-weight: 500;
    }
    
    .btn-cancel-custom {
        display: inline-block;
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background-color: transparent;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-cancel-custom:hover {
        background-color: #f1f5f9;
        color: #475569;
        text-decoration: none;
    }
    
    .btn-submit-custom {
        display: inline-block;
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: none;
        background-color: #10b981;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-submit-custom:hover {
        background-color: #059669;
    }
    
    .alert-custom-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
        margin-bottom: 16px;
    }
    .alert-custom-danger h5 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .section-divider {
        margin: 32px 0 24px 0;
        border-top: 1px solid #e2e8f0;
        padding-top: 24px;
    }
    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #059669;
        margin-bottom: 8px;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="mx-auto" style="max-width: 800px;">
        <div class="glass-form-card">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="form-header-title m-0">Editar Cultivo</h3>
                <a href="{{ route('cultivos.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('cultivos.update', $cultivo) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label">Lote Actual (Lectura)</label>
                        <input type="text" class="custom-input" value="Lote {{ $cultivo->lote->identificador }} - {{ $cultivo->lote->nombre }}" disabled>
                        <div class="text-xs text-muted mt-1">El cultivo está vinculado a este lote de forma permanente.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label" for="id_variedad">Variedad a sembrar *</label>
                        <select class="custom-input @error('id_variedad') is-invalid-custom @enderror" id="id_variedad" name="id_variedad" required>
                            <option value="">Seleccionar Variedad</option>
                            @foreach($variedades as $variedad)
                                <option value="{{ $variedad->id }}" {{ old('id_variedad', $cultivo->id_variedad) == $variedad->id ? 'selected' : '' }}>
                                    {{ $variedad->tipoCultivo->nombre }} - {{ $variedad->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_variedad')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="codigo">Código Identificador *</label>
                        <input type="text" class="custom-input @error('codigo') is-invalid-custom @enderror" 
                               id="codigo" name="codigo" value="{{ old('codigo', $cultivo->codigo) }}" required>
                        @error('codigo')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="custom-label" for="estado">Estado del Cultivo *</label>
                        <select class="custom-input @error('estado') is-invalid-custom @enderror" id="estado" name="estado" required onchange="toggleCosechaFields()">
                            <option value="sembrado" {{ old('estado', $cultivo->estado) == 'sembrado' ? 'selected' : '' }}>Sembrado</option>
                            <option value="creciendo" {{ old('estado', $cultivo->estado) == 'creciendo' ? 'selected' : '' }}>Creciendo</option>
                            <option value="cosechado" {{ old('estado', $cultivo->estado) == 'cosechado' ? 'selected' : '' }}>Cosechado (Finalizar ciclo)</option>
                            <option value="perdido" {{ old('estado', $cultivo->estado) == 'perdido' ? 'selected' : '' }}>Perdido (Finalizar ciclo)</option>
                        </select>
                        @error('estado')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="fecha_siembra">Fecha de Siembra *</label>
                        <input type="date" class="custom-input @error('fecha_siembra') is-invalid-custom @enderror" 
                               id="fecha_siembra" name="fecha_siembra" value="{{ old('fecha_siembra', $cultivo->fecha_siembra->format('Y-m-d')) }}" required>
                        @error('fecha_siembra')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="custom-label" for="fecha_cosecha_estimada">Cosecha Estimada *</label>
                        <input type="date" class="custom-input @error('fecha_cosecha_estimada') is-invalid-custom @enderror" 
                               id="fecha_cosecha_estimada" name="fecha_cosecha_estimada" value="{{ old('fecha_cosecha_estimada', $cultivo->fecha_cosecha_estimada->format('Y-m-d')) }}" required>
                        @error('fecha_cosecha_estimada')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Campos adicionales si el estado es 'cosechado' -->
                <div id="div_cosecha" style="display: {{ in_array(old('estado', $cultivo->estado), ['cosechado']) ? 'block' : 'none' }};">
                    <div class="section-divider"></div>
                    <h5 class="section-title"><i class="fas fa-check-circle mr-2"></i> Datos de la Cosecha</h5>
                    <p class="text-sm text-muted mb-4">Registrar esta información cerrará el ciclo del cultivo y liberará el lote.</p>
                    
                    <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="custom-label" for="fecha_cosecha_real">Fecha Real de Cosecha</label>
                            <input type="date" class="custom-input @error('fecha_cosecha_real') is-invalid-custom @enderror" 
                                   id="fecha_cosecha_real" name="fecha_cosecha_real" 
                                   value="{{ old('fecha_cosecha_real', $cultivo->fecha_cosecha_real?->format('Y-m-d')) }}">
                            @error('fecha_cosecha_real')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="custom-label" for="cantidad_cosechada_kg">Cantidad Cosechada (Kg)</label>
                            <div class="d-flex">
                                <input type="number" step="0.01" class="custom-input @error('cantidad_cosechada_kg') is-invalid-custom @enderror" 
                                       id="cantidad_cosechada_kg" name="cantidad_cosechada_kg" 
                                       value="{{ old('cantidad_cosechada_kg', $cultivo->cantidad_cosechada_kg) }}"
                                       style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                                <div style="background: #e2e8f0; border: 1px solid #e2e8f0; padding: 12px 16px; border-top-right-radius: 12px; border-bottom-right-radius: 12px; color: #475569; font-weight: 600;">
                                    Kg
                                </div>
                            </div>
                            @error('cantidad_cosechada_kg')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Alerta para perdido -->
                <div id="div_perdido" class="alert-custom-danger" style="display: {{ in_array(old('estado', $cultivo->estado), ['perdido']) ? 'block' : 'none' }};">
                    <h5><i class="fas fa-ban mr-2"></i> Atención</h5>
                    <p class="mb-0 text-sm">Marcar este cultivo como perdido cerrará su ciclo y liberará el lote para nuevas siembras. Por favor, documenta las razones en las observaciones.</p>
                </div>

                <div class="mb-4 mt-4">
                    <label class="custom-label" for="observaciones">Observaciones</label>
                    <textarea class="custom-input @error('observaciones') is-invalid-custom @enderror" 
                              id="observaciones" name="observaciones">{{ old('observaciones', $cultivo->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('cultivos.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom">
                            Actualizar Cultivo
                        </button>
                    </div>
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
