@extends('layouts.adminlte')

@section('title', 'Programar Tarea')

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
        border-color: #f59e0b;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
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
        background-color: #f59e0b;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-submit-custom:hover {
        background-color: #d97706;
    }
    
    .alert-custom-info {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }
    .alert-custom-info h5 {
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
                <h3 class="form-header-title m-0">Programar Nueva Tarea</h3>
                <a href="{{ route('actividades.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('actividades.store') }}" method="POST">
                @csrf
                
                @if($cultivos->isEmpty())
                    <div class="alert-custom-info">
                        <h5><i class="fas fa-info-circle mr-2"></i> No hay cultivos activos</h5>
                        <p class="mb-0 text-sm">No tienes ningún cultivo en proceso al que se le puedan asignar tareas. Primero debes <a href="{{ route('cultivos.create') }}" style="color: #1e40af; font-weight: 700; text-decoration: underline;">registrar una siembra</a>.</p>
                    </div>
                @else
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="custom-label" for="id_cultivo">Cultivo Objetivo *</label>
                            <select class="custom-input @error('id_cultivo') is-invalid-custom @enderror" id="id_cultivo" name="id_cultivo" required>
                                <option value="">Seleccionar Cultivo</option>
                                @foreach($cultivos as $cultivo)
                                    <option value="{{ $cultivo->id }}" {{ (old('id_cultivo') == $cultivo->id || $cultivoPreseleccionado == $cultivo->id) ? 'selected' : '' }}>
                                        {{ $cultivo->codigo }} (Lote {{ $cultivo->lote->identificador }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_cultivo')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="custom-label" for="id_tipo_actividad">Tipo de Tarea *</label>
                            <select class="custom-input @error('id_tipo_actividad') is-invalid-custom @enderror" id="id_tipo_actividad" name="id_tipo_actividad" required>
                                <option value="">Seleccionar Tipo</option>
                                @foreach($tiposActividad as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('id_tipo_actividad') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo_actividad')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="custom-label" for="asignado_a">Trabajador Asignado *</label>
                            <select class="custom-input @error('asignado_a') is-invalid-custom @enderror" id="asignado_a" name="asignado_a" required>
                                <option value="">Seleccionar Trabajador</option>
                                @foreach($trabajadores as $trabajador)
                                    <option value="{{ $trabajador->id }}" {{ old('asignado_a') == $trabajador->id ? 'selected' : '' }}>
                                        {{ $trabajador->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('asignado_a')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="custom-label" for="fecha_programada">Fecha a Realizar *</label>
                            <input type="date" class="custom-input @error('fecha_programada') is-invalid-custom @enderror" 
                                   id="fecha_programada" name="fecha_programada" value="{{ old('fecha_programada', date('Y-m-d', strtotime('+1 day'))) }}" required>
                            @error('fecha_programada')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="custom-label" for="descripcion">Descripción de la Tarea *</label>
                        <textarea class="custom-input @error('descripcion') is-invalid-custom @enderror" 
                                  id="descripcion" name="descripcion" 
                                  placeholder="Ej: Aplicar 2kg de fertilizante NPK por planta..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="custom-label" for="observaciones">Notas Internas (Opcional)</label>
                        <textarea class="custom-input @error('observaciones') is-invalid-custom @enderror" 
                                  id="observaciones" name="observaciones" 
                                  placeholder="Notas para el administrador...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                @endif
                
                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('actividades.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom" {{ $cultivos->isEmpty() ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                            Programar Tarea
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
