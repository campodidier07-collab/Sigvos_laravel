@extends('layouts.adminlte')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario: ' . $usuario->nombre)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
  <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Datos del Usuario</h3>
      </div>
      
      <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card-body">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                         id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
                  @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="id_rol">Rol en el Sistema <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                  </div>
                  <select class="form-control @error('id_rol') is-invalid @enderror" id="id_rol" name="id_rol" required>
                    @foreach($roles as $rol)
                      <option value="{{ $rol->id }}" {{ old('id_rol', $usuario->id_rol) == $rol->id ? 'selected' : '' }}>
                        {{ $rol->nombre }}
                      </option>
                    @endforeach
                  </select>
                  @error('id_rol')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                  </div>
                  <input type="email" class="form-control @error('email') is-invalid @enderror" 
                         id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                  @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="telefono">Teléfono</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  </div>
                  <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                         id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}">
                  @error('telefono')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
              <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" 
                     {{ old('activo', $usuario->activo) ? 'checked' : '' }} 
                     {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
              <label class="custom-control-label" for="activo">Usuario Activo (Permite el acceso al sistema)</label>
            </div>
            @if($usuario->id === auth()->id())
              <small class="text-muted d-block mt-1">No puedes desactivar tu propia cuenta desde aquí.</small>
              <input type="hidden" name="activo" value="1">
            @endif
          </div>

          <hr>
          <h5 class="mb-3 text-muted">
            <i class="fas fa-lock"></i> Actualizar Contraseña
            <small class="d-block mt-1" style="font-size: 0.7rem;">Deja estos campos en blanco si no deseas cambiar la contraseña actual.</small>
          </h5>

          <div class="row bg-light pt-3 pb-2 rounded border">
            <div class="col-md-6">
              <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" 
                         id="password" name="password">
                  @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" class="form-control" 
                         id="password_confirmation" name="password_confirmation">
                </div>
              </div>
            </div>
          </div>

        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('usuarios.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Actualizar Usuario
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
