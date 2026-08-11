@extends('layouts.adminlte')

@section('title', 'Editar Actividad')

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
        border-color: #2563eb;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
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
        background-color: #2563eb;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-submit-custom:hover {
        background-color: #1d4ed8;
    }
    
    .section-divider {
        margin: 32px 0 24px 0;
        border-top: 1px solid #e2e8f0;
        padding-top: 24px;
    }
    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #2563eb;
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
                <h3 class="form-header-title m-0">
                    @if(auth()->user()->isAdmin())
                        Editar Tarea #{{ $actividad->id }}
                    @else
                        Reportar Tarea #{{ $actividad->id }}
                    @endif
                </h3>
                <a href="{{ route('actividades.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('actividades.update', $actividad) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label">Cultivo Asignado (Lectura)</label>
                        <input type="text" class="custom-input" value="{{ $actividad->cultivo->codigo }} (Lote {{ $actividad->cultivo->lote->identificador }})" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label" for="id_tipo_actividad">Tipo de Tarea @if(auth()->user()->isAdmin())*@endif</label>
                        @if(auth()->user()->isAdmin())
                            <select class="custom-input @error('id_tipo_actividad') is-invalid-custom @enderror" id="id_tipo_actividad" name="id_tipo_actividad" required>
                                @foreach($tiposActividad as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('id_tipo_actividad', $actividad->id_tipo_actividad) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo_actividad')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        @else
                            <input type="text" class="custom-input" value="{{ $actividad->tipoActividad->nombre }}" disabled>
                        @endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="asignado_a">Trabajador Asignado @if(auth()->user()->isAdmin())*@endif</label>
                        @if(auth()->user()->isAdmin())
                            <select class="custom-input @error('asignado_a') is-invalid-custom @enderror" id="asignado_a" name="asignado_a" required>
                                @foreach($trabajadores as $trabajador)
                                    <option value="{{ $trabajador->id }}" {{ old('asignado_a', $actividad->asignado_a) == $trabajador->id ? 'selected' : '' }}>
                                        {{ $trabajador->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('asignado_a')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        @else
                            <input type="text" class="custom-input" value="{{ $actividad->asignadoA->nombre ?? 'N/A' }}" disabled>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <label class="custom-label" for="fecha_programada">Fecha Programada @if(auth()->user()->isAdmin())*@endif</label>
                        @if(auth()->user()->isAdmin())
                            <input type="date" class="custom-input @error('fecha_programada') is-invalid-custom @enderror" 
                                   id="fecha_programada" name="fecha_programada" value="{{ old('fecha_programada', $actividad->fecha_programada->format('Y-m-d')) }}" required>
                            @error('fecha_programada')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        @else
                            <input type="date" class="custom-input" value="{{ $actividad->fecha_programada->format('Y-m-d') }}" disabled>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="custom-label" for="descripcion">Descripción de la Tarea *</label>
                    @if(auth()->user()->isAdmin())
                        <textarea class="custom-input @error('descripcion') is-invalid-custom @enderror" 
                                  id="descripcion" name="descripcion" required>{{ old('descripcion', $actividad->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    @else
                        <textarea class="custom-input" disabled>{{ $actividad->descripcion }}</textarea>
                    @endif
                </div>

                <!-- Reporte de Ejecución -->
                <div class="section-divider"></div>
                <h5 class="section-title"><i class="fas fa-clipboard-check mr-2"></i> Reporte de Ejecución</h5>
                <p class="text-sm text-muted mb-4">Actualiza el estado de la tarea y reporta detalles.</p>
                
                <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="estado">Estado Actual *</label>
                        <select class="custom-input @error('estado') is-invalid-custom @enderror" id="estado" name="estado" required onchange="toggleEjecucion()">
                            <option value="pendiente" {{ old('estado', $actividad->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="completada" {{ old('estado', $actividad->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                            <option value="cancelada" {{ old('estado', $actividad->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('estado')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6" id="div_fecha_ejecucion" style="display: {{ old('estado', $actividad->estado) == 'completada' ? 'block' : 'none' }};">
                        <label class="custom-label" for="fecha_ejecucion">Fecha Real de Ejecución</label>
                        <input type="date" class="custom-input @error('fecha_ejecucion') is-invalid-custom @enderror" 
                               id="fecha_ejecucion" name="fecha_ejecucion" 
                               value="{{ old('fecha_ejecucion', $actividad->fecha_ejecucion?->format('Y-m-d') ?? date('Y-m-d')) }}">
                        @error('fecha_ejecucion')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="custom-label" for="observaciones">Observaciones de Ejecución</label>
                    <textarea class="custom-input @error('observaciones') is-invalid-custom @enderror" 
                              id="observaciones" name="observaciones" 
                              placeholder="Novedades durante la ejecución...">{{ old('observaciones', $actividad->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('actividades.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom">
                            Actualizar Reporte
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
