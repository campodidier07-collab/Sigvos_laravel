@extends('layouts.adminlte')

@section('title', 'Gestión de Usuarios')

@push('styles')
<style>
    /* Estilos Gsigvos para el módulo Usuarios */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 24px;
        border: 1px solid rgba(255,255,255,0.4);
        margin-bottom: 24px;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .header-title h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #16332b;
        margin: 0;
        font-family: 'Outfit', sans-serif;
    }
    .header-title p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 4px 0 0 0;
    }
    
    .btn-primary-custom {
        background-color: #2563eb;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 12px;
        border: none;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary-custom:hover {
        background-color: #1d4ed8;
        color: white;
        text-decoration: none;
    }
    
    .filters-container {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    .custom-select-filter, .custom-input-filter {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.875rem;
        color: #475569;
        transition: all 0.2s;
    }
    .custom-select-filter:focus, .custom-input-filter:focus {
        outline: none;
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15);
    }
    .btn-search {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        border-radius: 10px;
        padding: 8px 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-search:hover {
        background-color: #e2e8f0;
    }
    
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table th {
        text-align: left;
        padding: 12px 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b9e8a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f0fdf4;
        border-bottom: 1px solid #d8eee4;
    }
    .custom-table th:first-child {
        border-top-left-radius: 12px;
    }
    .custom-table th:last-child {
        border-top-right-radius: 12px;
    }
    .custom-table td {
        padding: 16px 20px;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0fdf4;
        vertical-align: middle;
        background-color: transparent;
        transition: background-color 0.2s;
    }
    .custom-table tbody tr:hover td {
        background-color: #f9fefb;
    }
    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .status-pill {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-active { background: #dcfce7; color: #15803d; }
    .status-inactive { background: #fee2e2; color: #b91c1c; }
    
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }
    .role-badge.worker {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        color: white;
    }
    .avatar-admin { background: #3b82f6; }
    .avatar-worker { background: #10b981; }
    
    .actions-flex {
        display: flex;
        gap: 8px;
    }
    .btn-action-info {
        background-color: #f0f9ff;
        color: #0284c7;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-info:hover { background-color: #e0f2fe; color: #0369a1; text-decoration: none; }
    
    .btn-action-primary {
        background-color: #eff6ff;
        color: #2563eb;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-primary:hover { background-color: #dbeafe; color: #1d4ed8; text-decoration: none; }
    
    .btn-action-danger {
        background-color: #fef2f2;
        color: #dc2626;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-action-danger:hover { background-color: #fee2e2; color: #b91c1c; }
    
    .empty-state {
        text-align: center;
        padding: 48px 20px;
    }
    .empty-state i {
        font-size: 3rem;
        color: #e2e8f0;
        margin-bottom: 16px;
    }
    .empty-state p {
        color: #94a3b8;
        font-size: 0.875rem;
        margin: 0;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="header-actions">
        <div class="header-title">
            <h2>Usuarios</h2>
            <p>Gestión de Administradores y Trabajadores del Sistema.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn-primary-custom">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Filtros -->
    <div class="filters-container">
        <form action="{{ route('usuarios.index') }}" method="GET" id="form-filtro" style="display: flex; gap: 12px;">
            <select name="rol" class="custom-select-filter" onchange="document.getElementById('form-filtro').submit();">
                <option value="">Todos los roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ request('rol') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                @endforeach
            </select>
            
            <div style="display: flex;">
                <input type="text" name="buscar" class="custom-input-filter" placeholder="Buscar nombre o correo..." value="{{ request('buscar') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                <button type="submit" class="btn-search" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        @if($usuarios->isEmpty())
            <div class="empty-state">
                <i class="fas fa-users block"></i>
                <p>No se encontraron usuarios registrados.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Contacto</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $user)
                        <tr style="{{ !$user->activo ? 'opacity: 0.6;' : '' }}">
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar {{ $user->isAdmin() ? 'avatar-admin' : 'avatar-worker' }}">
                                        {{ strtoupper(substr($user->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #16332b;">
                                            {{ $user->nombre }}
                                            @if($user->id === auth()->id())
                                                <span style="font-size: 0.65rem; background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: 800;">TÚ</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}" style="color: #065f46; font-weight: 600; text-decoration: none; display: block;">
                                    {{ $user->email }}
                                </a>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    <i class="fas fa-phone" style="font-size: 0.7rem; margin-right: 2px;"></i> {{ $user->telefono ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <span class="role-badge {{ !$user->isAdmin() ? 'worker' : '' }}">
                                    <i class="fas {{ $user->isAdmin() ? 'fa-user-shield' : 'fa-user-hard-hat' }}"></i>
                                    {{ $user->rol->nombre }}
                                </span>
                            </td>
                            <td>
                                @if($user->activo)
                                    <span class="status-pill status-active">Activo</span>
                                @else
                                    <span class="status-pill status-inactive">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <span style="color: #64748b; font-size: 0.875rem;">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="actions-flex">
                                    <a href="{{ route('usuarios.show', $user) }}" class="btn-action-info" title="Ver Perfil">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('usuarios.edit', $user) }}" class="btn-action-primary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $user) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Eliminar usuario?', '¿Seguro que deseas eliminar o desactivar este usuario?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-danger" title="Eliminar / Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($usuarios->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f0fdf4;">
                {{ $usuarios->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
            @endif
            
        @endif
    </div>
</div>
@endsection
