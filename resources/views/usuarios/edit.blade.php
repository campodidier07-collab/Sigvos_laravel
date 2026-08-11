@extends('layouts.adminlte')

@section('title', 'Editar Usuario')

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
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    /* Toggle switch personalizado */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 28px;
    }
    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #10b981;
    }
    input:focus + .slider {
        box-shadow: 0 0 1px #10b981;
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    input:disabled + .slider {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="mx-auto" style="max-width: 800px;">
        <div class="glass-form-card">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="form-header-title m-0">Editar Usuario: {{ $usuario->nombre }}</h3>
                <a href="{{ route('usuarios.index') }}" class="text-muted" style="font-size: 1.25rem;"><i class="fas fa-times"></i></a>
            </div>
            
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="nombre">Nombre Completo *</label>
                        <input type="text" class="custom-input @error('nombre') is-invalid-custom @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
                        @error('nombre')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label" for="id_rol">Rol en el Sistema *</label>
                        <select class="custom-input @error('id_rol') is-invalid-custom @enderror" id="id_rol" name="id_rol" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('id_rol', $usuario->id_rol) == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_rol')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="email">Correo Electrónico *</label>
                        <input type="email" class="custom-input @error('email') is-invalid-custom @enderror" 
                               id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                        @error('email')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="custom-label" for="telefono">Teléfono</label>
                        <input type="text" class="custom-input @error('telefono') is-invalid-custom @enderror" 
                               id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}">
                        @error('telefono')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-center p-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="flex-grow: 1;">
                        <div style="font-weight: 700; color: #1e293b;">Estado de la Cuenta</div>
                        <div style="font-size: 0.875rem; color: #64748b;">
                            Permite o deniega el acceso al sistema. 
                            @if($usuario->id === auth()->id())
                                <br><span style="color: #ef4444; font-weight: 600;">(No puedes desactivar tu propia cuenta).</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="switch mb-0">
                            <input type="checkbox" name="activo" value="1" 
                                   {{ old('activo', $usuario->activo) ? 'checked' : '' }} 
                                   {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                            <span class="slider"></span>
                        </label>
                        @if($usuario->id === auth()->id())
                            <input type="hidden" name="activo" value="1">
                        @endif
                    </div>
                </div>

                <div class="section-divider"></div>
                <h5 class="section-title"><i class="fas fa-lock mr-2 text-muted"></i> Actualizar Contraseña</h5>
                <p class="text-sm text-muted mb-4">Deja estos campos en blanco si no deseas cambiar la contraseña actual.</p>

                <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="custom-label" for="password">Nueva Contraseña</label>
                        <input type="password" class="custom-input @error('password') is-invalid-custom @enderror" 
                               id="password" name="password" placeholder="••••••••">
                        @error('password')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="custom-label" for="password_confirmation">Confirmar Nueva Contraseña</label>
                        <input type="password" class="custom-input" 
                               id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-6">
                        <a href="{{ route('usuarios.index') }}" class="btn-cancel-custom">
                            Cancelar
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn-submit-custom">
                            Actualizar Usuario
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
