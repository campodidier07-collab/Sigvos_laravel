@extends('layouts.adminlte')

@section('title', 'Gestión de Usuarios')
@section('page-title', 'Usuarios del Sistema')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Listado de Usuarios</h3>
        
        <div class="card-tools d-flex">
          <form action="{{ route('usuarios.index') }}" method="GET" class="mr-2" id="form-filtro">
            <select name="rol" class="form-control form-control-sm" onchange="document.getElementById('form-filtro').submit();">
              <option value="">Todos los roles</option>
              @foreach($roles as $r)
                <option value="{{ $r->id }}" {{ request('rol') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
              @endforeach
            </select>
          </form>

          <form action="{{ route('usuarios.index') }}" method="GET" class="input-group input-group-sm mr-2" style="width: 250px;">
            <input type="hidden" name="rol" value="{{ request('rol') }}">
            <input type="text" name="buscar" class="form-control float-right" placeholder="Buscar nombre o email..." value="{{ request('buscar') }}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
            </div>
          </form>

          <a href="{{ route('usuarios.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
          </a>
        </div>
      </div>
      
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Contacto</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Creado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($usuarios as $user)
              <tr class="{{ !$user->activo ? 'text-muted bg-light' : '' }}">
                <td>
                  <div class="d-flex align-items-center">
                    <span class="img-circle elevation-1 bg-{{ $user->isAdmin() ? 'primary' : 'info' }} d-inline-flex align-items-center justify-content-center text-white mr-2" style="width: 40px; height: 40px;">
                      {{ strtoupper(substr($user->nombre, 0, 1)) }}
                    </span>
                    <div>
                      <strong>{{ $user->nombre }}</strong>
                      @if($user->id === auth()->id())
                        <span class="badge badge-success ml-1">Tú</span>
                      @endif
                    </div>
                  </div>
                </td>
                <td>
                  <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                  <br>
                  <small class="text-muted"><i class="fas fa-phone mr-1"></i> {{ $user->telefono ?? 'N/A' }}</small>
                </td>
                <td>
                  <span class="badge badge-{{ $user->isAdmin() ? 'primary' : 'info' }}">
                    <i class="fas {{ $user->isAdmin() ? 'fa-user-shield' : 'fa-user-hard-hat' }} mr-1"></i>
                    {{ $user->rol->nombre }}
                  </span>
                </td>
                <td>
                  @if($user->activo)
                    <span class="badge badge-success">Activo</span>
                  @else
                    <span class="badge badge-danger">Inactivo</span>
                  @endif
                </td>
                <td>
                  {{ $user->created_at->format('d/m/Y') }}
                </td>
                <td class="text-center">
                  <a href="{{ route('usuarios.show', $user) }}" class="btn btn-sm btn-info" title="Ver Perfil">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-sm btn-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                  </a>
                  
                  @if($user->id !== auth()->id())
                  <form action="{{ route('usuarios.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar o desactivar este usuario?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar/Desactivar">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  <i class="fas fa-users fa-3x mb-3 text-light"></i>
                  <h5>No se encontraron usuarios.</h5>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if($usuarios->hasPages())
      <div class="card-footer clearfix">
        {{ $usuarios->withQueryString()->links('pagination::bootstrap-4') }}
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
