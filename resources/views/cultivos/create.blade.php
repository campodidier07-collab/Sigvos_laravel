@extends('layouts.adminlte')

@section('title', 'Registrar Siembra')

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
    
    .alert-custom {
        background-color: #fffbeb;
        border: 1px solid #fcd34d;
        color: #b45309;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }
    .alert-custom h5 {
        font-size: 1rem;
        font-weight: 700;
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
                <h3 class="form-header-title m-0">Registrar Nuevo Cultivo</h3>
                <a href="{{ route('cultivos.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('cultivos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                @if($lotes->isEmpty())
                    <div class="alert-custom">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i> No hay lotes disponibles</h5>
                        <p class="mb-0 text-sm">Todos los lotes están ocupados actualmente o no tienes ninguno asignado.
                        Debes liberar un lote (marcando su cultivo actual como cosechado/perdido) o registrar uno nuevo.</p>
                    </div>
                @else
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="custom-label" for="id_lote">Lote Disponible *</label>
                            <select class="custom-input @error('id_lote') is-invalid-custom @enderror" id="id_lote" name="id_lote" required>
                                <option value="">Seleccionar Lote</option>
                                @foreach($lotes as $lote)
                                    <option value="{{ $lote->id }}" {{ (old('id_lote') == $lote->id || $lotePreseleccionado == $lote->id) ? 'selected' : '' }}>
                                        Lote {{ $lote->identificador }} - {{ $lote->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_lote')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="custom-label" for="id_variedad">Variedad a sembrar *</label>
                            <select class="custom-input @error('id_variedad') is-invalid-custom @enderror" id="id_variedad" name="id_variedad" required>
                                <option value="">Seleccionar Variedad</option>
                                @foreach($variedades as $variedad)
                                    <option value="{{ $variedad->id }}" {{ old('id_variedad') == $variedad->id ? 'selected' : '' }}>
                                        {{ $variedad->tipoCultivo->nombre }} - {{ $variedad->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_variedad')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="custom-label" for="codigo">Código Identificador (Interno) *</label>
                        <input type="text" class="custom-input @error('codigo') is-invalid-custom @enderror" 
                               id="codigo" name="codigo" value="{{ old('codigo', 'CULT-' . date('ymd-Hi')) }}" required>
                        @error('codigo')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="custom-label" for="fecha_siembra">Fecha de Siembra *</label>
                            <input type="date" class="custom-input @error('fecha_siembra') is-invalid-custom @enderror" 
                                   id="fecha_siembra" name="fecha_siembra" value="{{ old('fecha_siembra', date('Y-m-d')) }}" required>
                            @error('fecha_siembra')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="custom-label" for="fecha_cosecha_estimada">Cosecha Estimada *</label>
                            <input type="date" class="custom-input @error('fecha_cosecha_estimada') is-invalid-custom @enderror" 
                                   id="fecha_cosecha_estimada" name="fecha_cosecha_estimada" value="{{ old('fecha_cosecha_estimada') }}" required>
                            @error('fecha_cosecha_estimada')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="custom-label" for="observaciones">Observaciones (Opcional)</label>
                        <textarea class="custom-input @error('observaciones') is-invalid-custom @enderror" 
                                  id="observaciones" name="observaciones" 
                                  placeholder="Ej: Semilla tratada con fertilizante...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="custom-label" for="fotografia">Imagen Principal (Opcional)</label>
                        <input type="file" class="custom-input @error('fotografia') is-invalid-custom @enderror" 
                               id="fotografia" name="fotografia" accept="image/*">
                        <small class="text-muted">Formatos: jpg, jpeg, png, gif. Máx 5MB.</small>
                        @error('fotografia')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                @endif
                
                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('cultivos.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom" {{ $lotes->isEmpty() ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                            Registrar Cultivo
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
