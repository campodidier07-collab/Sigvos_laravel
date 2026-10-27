@extends('layouts.adminlte')

@section('title', 'Perfil de ' . $usuario->nombre)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }
    .font-heading {
        font-family: 'Outfit', sans-serif;
    }

    /* Tarjetas Modernas Sigvos */
    .card-sigvos {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .card-sigvos-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-sigvos-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-sigvos-body {
        padding: 24px;
    }

    /* Avatar del Perfil */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 16px;
    }
    .avatar-img-profile {
        width: 104px;
        height: 104px;
        border-radius: 26px;
        object-fit: cover;
        border: 3px solid #10b981;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.22);
    }
    .avatar-initial-profile {
        width: 104px;
        height: 104px;
        border-radius: 26px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 3.2rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.25);
    }

    /* Badges de Rol */
    .badge-role-admin {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-role-worker {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Badges de Estado Estandarizados */
    .badge-status-activo {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-status-inactivo {
        background-color: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Lista de información de resumen */
    .info-list-sigvos {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }
    .info-list-sigvos li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .info-list-sigvos li:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }

    /* Botones Estandarizados SIGVOS */
    .btn-sigvos-primary {
        background-color: #10b981;
        color: #ffffff !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 12px 20px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-sigvos-primary:hover {
        background-color: #059669;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.38);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .btn-sigvos-outline {
        background-color: #ffffff;
        color: #475569 !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 9px 18px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-sigvos-outline:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b !important;
        transform: translateY(-1px);
        text-decoration: none;
    }

    /* Pestañas Modernas estilo SIGVOS */
    .nav-tabs-sigvos {
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: 8px;
        padding: 12px 20px 0;
        background: #f8fafc;
    }
    .nav-tabs-sigvos .nav-link {
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.9rem;
        color: #64748b;
        background: transparent;
        border: none;
        border-radius: 12px 12px 0 0;
        padding: 12px 18px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
    }
    .nav-tabs-sigvos .nav-link:hover {
        color: #059669;
        background: #f1f5f9;
    }
    .nav-tabs-sigvos .nav-link.active {
        color: #065f46;
        background: #ffffff;
        border-bottom: 3px solid #10b981;
        font-weight: 700;
    }

    /* Tarjetas de Tarea en la lista */
    .task-card-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .task-card-item:hover {
        background: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        border-color: #a7f3d0;
    }
    .task-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
        flex-shrink: 0;
    }

    /* Tarjetas de Lotes Asignados */
    .lote-assigned-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .lote-assigned-card:hover {
        border-color: #10b981;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.08);
        transform: translateY(-2px);
    }

    /* Estado vacío */
    .empty-state-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border: 1px dashed #bbf7d0;
        border-radius: 18px;
        padding: 48px 24px;
        text-align: center;
    }
    .empty-state-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: #ecfdf5;
        color: #10b981;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Barra de Navegación y Acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 12px;">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('usuarios.index') }}" class="btn-sigvos-outline">
                <i class="fa-solid fa-arrow-left"></i> Volver a Usuarios
            </a>
            <h1 class="font-heading font-extrabold m-0 text-slate-800" style="font-size: 1.5rem; color: #0f172a;">
                Perfil: <span style="color: #10b981;">{{ $usuario->nombre }}</span>
            </h1>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-sigvos-outline" style="background: #10b981; color: white !important; border-color: #10b981;">
                <i class="fa-solid fa-user-pen"></i> Editar Perfil
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Tarjeta de Perfil -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card-sigvos">
                <div class="card-sigvos-body text-center pt-4">
                    
                    <!-- Avatar con soporte de iniciales y fallback -->
                    <div class="avatar-wrapper">
                        @if($usuario->foto_perfil)
                            <img src="{{ asset('storage/' . $usuario->foto_perfil) }}"
                                 alt="{{ $usuario->nombre }}"
                                 class="avatar-img-profile"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span class="avatar-initial-profile" style="display: none;">
                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                            </span>
                        @else
                            <span class="avatar-initial-profile">
                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    <h3 class="font-heading font-bold mb-1" style="color: #0f172a; font-size: 1.35rem;">
                        {{ $usuario->nombre }}
                    </h3>
                    
                    <div class="mb-3">
                        @if($usuario->isAdmin())
                            <span class="badge-role-admin">
                                <i class="fa-solid fa-shield-halved"></i> {{ $usuario->rol->nombre ?? 'Administrador' }}
                            </span>
                        @else
                            <span class="badge-role-worker">
                                <i class="fa-solid fa-tractor"></i> {{ $usuario->rol->nombre ?? 'Trabajador de Campo' }}
                            </span>
                        @endif
                    </div>

                    <!-- Lista de Atributos Clave -->
                    <ul class="info-list-sigvos text-left">
                        <li>
                            <span class="info-label">Estado</span>
                            @if($usuario->activo)
                                <span class="badge-status-activo">
                                    <i class="fa-solid fa-circle-check"></i> Activo
                                </span>
                            @else
                                <span class="badge-status-inactivo">
                                    <i class="fa-solid fa-circle-xmark"></i> Inactivo
                                </span>
                            @endif
                        </li>
                        <li>
                            <span class="info-label">Correo Electrónico</span>
                            <a href="mailto:{{ $usuario->email }}" class="info-value text-decoration-none" style="color: #047857;" title="Enviar correo">
                                <i class="fa-regular fa-envelope mr-1 text-muted"></i> {{ $usuario->email }}
                            </a>
                        </li>
                        <li>
                            <span class="info-label">Teléfono</span>
                            <span class="info-value">
                                <i class="fa-solid fa-phone mr-1 text-muted"></i> {{ $usuario->telefono ?: 'No registrado' }}
                            </span>
                        </li>
                        <li>
                            <span class="info-label">Fecha de Registro</span>
                            <span class="info-value">
                                <i class="fa-regular fa-calendar mr-1 text-muted"></i> {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : 'N/A' }}
                            </span>
                        </li>
                    </ul>

                    <div class="mt-4">
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-sigvos-primary">
                            <i class="fa-solid fa-user-pen"></i> Editar Perfil
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Columna Derecha: Tareas Pendientes y Lotes Asignados -->
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card-sigvos">
                
                <!-- Pestañas Modernas -->
                <ul class="nav nav-tabs nav-tabs-sigvos" id="userTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tareas-tab" data-toggle="tab" href="#tareas" role="tab">
                            <i class="fa-solid fa-clipboard-check"></i> Tareas Pendientes
                            @if($actividadesPendientes->isNotEmpty())
                                <span class="badge ml-1" style="background: #10b981; color: white; font-size: 0.7rem;">{{ $actividadesPendientes->count() }}</span>
                            @endif
                        </a>
                    </li>
                    @if($usuario->esTrabajador())
                    <li class="nav-item">
                        <a class="nav-link" id="lotes-tab" data-toggle="tab" href="#lotes" role="tab">
                            <i class="fa-solid fa-layer-group"></i> Lotes Asignados
                            @if($usuario->lotes->isNotEmpty())
                                <span class="badge ml-1" style="background: #0284c7; color: white; font-size: 0.7rem;">{{ $usuario->lotes->count() }}</span>
                            @endif
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="card-sigvos-body">
                    <div class="tab-content" id="userTabsContent">
                        
                        <!-- Pestaña 1: Tareas Pendientes -->
                        <div class="tab-pane fade show active" id="tareas" role="tabpanel">
                            @if($actividadesPendientes->isEmpty())
                                <div class="empty-state-box">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <h4 class="font-heading font-bold mb-1" style="color: #065f46;">¡Todo al día!</h4>
                                    <p class="text-muted text-sm mb-0">Este usuario no tiene tareas pendientes en este momento.</p>
                                </div>
                            @else
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="font-heading font-bold text-sm m-0 text-uppercase" style="color: #64748b; letter-spacing: 0.05em;">
                                        Últimas labores asignadas
                                    </h5>
                                    <span class="text-muted text-xs">Mostrando hasta 5 tareas</span>
                                </div>

                                @foreach($actividadesPendientes as $act)
                                    @php
                                        $codigoTipo = strtoupper($act->tipoActividad->codigo ?? '');
                                        $taskBg = match($codigoTipo) {
                                            'RIEGO' => 'background: linear-gradient(135deg, #0284c7, #38bdf8);',
                                            'FERTILIZACION' => 'background: linear-gradient(135deg, #059669, #34d399);',
                                            'PODA' => 'background: linear-gradient(135deg, #d97706, #fbbf24);',
                                            'FUMIGACION' => 'background: linear-gradient(135deg, #e11d48, #fb7185);',
                                            'COSECHA' => 'background: linear-gradient(135deg, #16a34a, #4ade80);',
                                            default => 'background: linear-gradient(135deg, #10b981, #34d399);'
                                        };
                                        $icono = match($codigoTipo) {
                                            'RIEGO' => 'fa-droplet',
                                            'FERTILIZACION' => 'fa-flask',
                                            'PODA' => 'fa-scissors',
                                            'FUMIGACION' => 'fa-spray-can-sparkles',
                                            'COSECHA' => 'fa-wheat-awn',
                                            default => 'fa-list-check'
                                        };
                                    @endphp
                                    <div class="task-card-item">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="task-icon-box" style="{{ $taskBg }}">
                                                <i class="fa-solid {{ $icono }}"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-heading font-bold m-0" style="font-size: 0.95rem; color: #0f172a;">
                                                    <a href="{{ route('actividades.show', $act) }}" class="text-dark hover:text-success text-decoration-none">
                                                        {{ $act->tipoActividad->nombre }}
                                                    </a>
                                                </h5>
                                                <p class="text-muted text-xs m-0 mb-1">
                                                    {{ Str::limit($act->descripcion, 90) }}
                                                </p>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.725rem;">
                                                        <i class="fa-regular fa-calendar mr-1"></i> {{ $act->fecha_programada->format('d/m/Y') }}
                                                    </span>
                                                    @if($act->cultivo)
                                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.725rem;">
                                                        <i class="fa-solid fa-leaf mr-1"></i> {{ $act->cultivo->codigo }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('actividades.show', $act) }}" class="btn-sigvos-outline py-1 px-3" style="font-size: 0.8rem; white-space: nowrap;">
                                            Ver <i class="fa-solid fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Pestaña 2: Lotes Asignados -->
                        @if($usuario->esTrabajador())
                        <div class="tab-pane fade" id="lotes" role="tabpanel">
                            @if($usuario->lotes->isEmpty())
                                <div class="empty-state-box">
                                    <div class="empty-state-icon" style="background: #f0f9ff; color: #0284c7;">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <h4 class="font-heading font-bold mb-1" style="color: #0369a1;">Sin lotes asignados</h4>
                                    <p class="text-muted text-sm mb-0">Este trabajador no tiene lotes asignados actualmente.</p>
                                </div>
                            @else
                                <div class="row">
                                    @foreach($usuario->lotes as $lote)
                                        @php
                                            $badgeColor = match($lote->estado) {
                                                'disponible' => 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
                                                'ocupado' => 'background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;',
                                                'en_descanso' => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                                default => 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;'
                                            };
                                        @endphp
                                        <div class="col-md-6 mb-3">
                                            <div class="lote-assigned-card">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h5 class="font-heading font-bold m-0" style="font-size: 1rem; color: #0f172a;">
                                                            Lote {{ $lote->identificador }} - {{ $lote->nombre }}
                                                        </h5>
                                                        <span class="badge text-uppercase font-bold" style="{{ $badgeColor }}; font-size: 0.7rem; border-radius: 9999px; padding: 4px 8px;">
                                                            {{ str_replace('_', ' ', $lote->estado) }}
                                                        </span>
                                                    </div>

                                                    <p class="text-muted text-xs mb-2">
                                                        <i class="fa-solid fa-vector-square mr-1 text-emerald-600"></i> Área: <strong>{{ number_format($lote->area_ha, 2) }} ha</strong>
                                                        @if($lote->ubicacion)
                                                            <br><i class="fa-solid fa-compass mr-1 text-emerald-600"></i> {{ $lote->ubicacion }}
                                                        @endif
                                                    </p>

                                                    @if($lote->pivot->creado_en)
                                                    <div class="text-muted text-xs mb-3">
                                                        <i class="fa-regular fa-calendar-check mr-1"></i> Asignado el: {{ \Carbon\Carbon::parse($lote->pivot->creado_en)->format('d/m/Y') }}
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                    <a href="{{ route('lotes.show', $lote) }}" class="btn-sigvos-outline py-1 px-3" style="font-size: 0.775rem;">
                                                        Ver Lote <i class="fa-solid fa-arrow-right ml-1"></i>
                                                    </a>

                                                    @if(auth()->user()->isAdmin())
                                                    <form action="{{ route('asignaciones.lotes.desactivar') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar la asignación de este lote para este trabajador? Se conservará el registro histórico.');">
                                                        @csrf
                                                        <input type="hidden" name="id_lote" value="{{ $lote->id }}">
                                                        <input type="hidden" name="id_usuario" value="{{ $usuario->id }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.75rem;" title="Desactivar asignación conservando historial">
                                                            <i class="fa-solid fa-user-minus mr-1"></i> Desactivar
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
