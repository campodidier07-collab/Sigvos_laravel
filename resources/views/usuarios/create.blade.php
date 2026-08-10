@extends('layouts.adminlte')

@section('title', 'Nuevo Usuario')
@section('page-title', 'Registrar Nuevo Usuario')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
  <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Datos del Usuario</h3>
      </div>
      
      <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
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
                         id="nombre" name="nombre" value="{{ old('nombre') }}" required>
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
                    <option value="">-- Seleccionar Rol --</option>
                    @foreach($roles as $rol)
                      <option value="{{ $rol->id }}" {{ old('id_rol') == $rol->id ? 'selected' : '' }}>
                        {{ $rol->nombre }} ({{ $rol->codigo }})
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
                         id="email" name="email" value="{{ old('email') }}" required>
                  @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="telefono">Teléfono (Opcional)</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  </div>
                  <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                         id="telefono" name="telefono" value="{{ old('telefono') }}">
                  @error('telefono')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <hr>
          <h5 class="mb-3 text-muted"><i class="fas fa-lock"></i> Credenciales de Acceso</h5>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="password">Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" 
                         id="password" name="password" required>
                  @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" class="form-control" 
                         id="password_confirmation" name="password_confirmation" required>
                </div>
              </div>
            </div>
          </div>

        </div>
        
        <div class="card-footer text-right">
          <a href="{{ route('usuarios.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar Usuario
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
