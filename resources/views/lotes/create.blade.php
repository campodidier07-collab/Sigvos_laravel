@extends('layouts.adminlte')

@section('title', 'Nuevo Lote')

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
    
    /* Checkbox custom */
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .checkbox-custom {
        width: 18px;
        height: 18px;
        accent-color: #059669;
        cursor: pointer;
    }
    .checkbox-label {
        font-size: 0.875rem;
        color: #475569;
        font-weight: 500;
        cursor: pointer;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="mx-auto" style="max-width: 700px;">
        <div class="glass-form-card">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="form-header-title m-0">Registrar Nuevo Lote</h3>
                <a href="{{ route('lotes.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('lotes.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="identificador">Identificador *</label>
                        <input type="text" class="custom-input @error('identificador') is-invalid-custom @enderror" 
                               id="identificador" name="identificador" value="{{ old('identificador') }}" 
                               placeholder="Ej: A" maxlength="1" required style="text-transform: uppercase;">
                        @error('identificador')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="custom-label" for="area_ha">Área (ha) *</label>
                        <input type="number" step="0.01" min="0.01" class="custom-input @error('area_ha') is-invalid-custom @enderror" 
                               id="area_ha" name="area_ha" value="{{ old('area_ha') }}" 
                               placeholder="Ej: 2.5" required>
                        @error('area_ha')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="custom-label" for="nombre">Nombre *</label>
                    <input type="text" class="custom-input @error('nombre') is-invalid-custom @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" 
                           placeholder="Nombre del lote" required maxlength="100">
                    @error('nombre')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="custom-label" for="ubicacion">Ubicación *</label>
                    <input type="text" class="custom-input @error('ubicacion') is-invalid-custom @enderror" 
                           id="ubicacion" name="ubicacion" value="{{ old('ubicacion') }}" 
                           placeholder="Descripción de la ubicación" required maxlength="200">
                    @error('ubicacion')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-4 align-items-end">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="id_tipo_preferido">Tipo de cultivo preferido</label>
                        <select class="custom-input @error('id_tipo_preferido') is-invalid-custom @enderror" id="id_tipo_preferido" name="id_tipo_preferido">
                            <option value="">Sin preferencia</option>
                            @foreach($tiposCultivo as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('id_tipo_preferido') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tipo_preferido')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 pb-2">
                        <label class="checkbox-wrapper m-0 pt-2">
                            <input type="checkbox" class="checkbox-custom" id="es_alternativo" name="es_alternativo" value="1" {{ old('es_alternativo') ? 'checked' : '' }}>
                            <span class="checkbox-label">Lote alternativo</span>
                        </label>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('lotes.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom">
                            Registrar Lote
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
