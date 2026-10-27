@extends('layouts.adminlte')

@section('title', 'Calendario de Planificación Agrícola')

@push('styles')
<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8fafc;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        padding: 24px;
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
    }
    
    /* Botón Superior */
    .btn-top-primary {
        background-color: #10b981;
        color: white !important;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        text-decoration: none;
    }
    .btn-top-primary:hover {
        background-color: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.38);
        color: white !important;
        text-decoration: none;
    }

    /* Barra de filtros */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .custom-select-filter {
        padding: 9px 16px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 600;
        min-width: 240px;
        outline: none;
        transition: all 0.2s ease;
    }
    .custom-select-filter:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .btn-reset-filter {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #64748b;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-reset-filter:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }

    /* Leyenda */
    .legend-container {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
    }
    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    /* Personalización FullCalendar */
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e2e8f0;
    }
    .fc-theme-standard .fc-scrollgrid {
        border-color: #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .fc .fc-toolbar-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #1e293b;
        font-size: 1.45rem;
        text-transform: capitalize;
    }
    .fc .fc-button-primary {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #334155;
        text-transform: capitalize;
        font-weight: 700;
        font-size: 0.85rem;
        border-radius: 10px;
        padding: 8px 16px;
        transition: all 0.15s ease;
        box-shadow: none;
    }
    .fc .fc-button-primary:not(:disabled):active, 
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }
    .fc .fc-button-primary:disabled {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        color: #94a3b8;
    }
    .fc .fc-col-header-cell-cushion {
        color: #065f46;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        padding: 12px 0;
    }
    .fc .fc-col-header {
        background-color: #f0fdf4;
    }
    .fc .fc-daygrid-day-number {
        color: #334155;
        font-weight: 700;
        padding: 8px 10px;
        font-size: 0.85rem;
    }
    
    /* Celda del día clickeable con hover suave */
    .fc .fc-daygrid-day-frame {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .fc .fc-daygrid-day-frame:hover {
        background-color: #f0fdf4 !important;
    }
    
    /* Resaltado del día de hoy */
    .fc .fc-day-today {
        background-color: rgba(254, 243, 199, 0.35) !important;
    }
    .fc-day-today .fc-daygrid-day-number {
        background: #f59e0b;
        color: #ffffff;
        border-radius: 50%;
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 4px;
    }

    /* Eventos */
    .fc-event {
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        transition: transform 0.15s, box-shadow 0.15s;
        margin-bottom: 3px;
        border: none;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .fc-event-today-highlight {
        box-shadow: 0 0 0 2px #f59e0b, 0 4px 12px rgba(245, 158, 11, 0.4) !important;
    }

    /* Modales Estilo Sigvos Premium */
    .custom-modal-content {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .custom-modal-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 22px 26px;
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .custom-modal-body {
        padding: 26px 28px;
        background: #ffffff;
    }
    .custom-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 18px 28px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
    }
    .modal-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .modal-input, .modal-select {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 11px 14px;
        font-size: 0.92rem;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
        outline: none;
    }
    .modal-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px 16px;
        padding-right: 40px;
    }
    .modal-input:focus, .modal-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
    }
    .btn-modal-cancel {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-modal-cancel:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .btn-modal-submit {
        background: #10b981;
        color: white !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 10px 22px;
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-modal-submit:hover {
        background: #059669;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        transform: translateY(-1px);
    }
    .close-modal-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .close-modal-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        color: white;
    }
    .selected-date-badge {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-weight: 700;
        border-radius: 10px;
        padding: 8px 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 pt-3 pb-5">
    
    <x-module-header 
        title="Calendario de Planificación Agrícola" 
        subtitle="Visualiza actividades programadas, proyecciones de cosecha en vista mensual o semanal y agenda tareas directamente." 
        icon="fa-solid fa-calendar-days"
    >
        <button type="button" class="btn-top-primary shadow-sm" onclick="abrirModalCrearActividad('{{ date('Y-m-d') }}')">
            <i class="fa-solid fa-circle-plus"></i> Programar Actividad
        </button>
    </x-module-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #065f46; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
            @foreach($errors->all() as $error)
                <span>{{ $error }}</span><br>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Barra de Herramientas: Filtros y Acceso Rápido -->
    <div class="glass-card mb-3" style="padding: 16px 20px;">
        <div class="filter-bar m-0">
            <div class="filter-group">
                <label for="filtro_tipo_actividad" class="m-0 font-weight-bold text-muted small text-uppercase">
                    <i class="fa-solid fa-filter text-emerald-600 mr-1"></i> Filtrar por Tipo:
                </label>
                <select id="filtro_tipo_actividad" class="custom-select-filter">
                    <option value="">Todas las actividades y cosechas</option>
                    @foreach($tiposActividad as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
                <button type="button" id="btn-limpiar-filtro" class="btn-reset-filter">
                    <i class="fa-solid fa-rotate-left"></i> Restablecer
                </button>
            </div>

            <div class="text-muted small">
                <i class="fa-solid fa-hand-pointer text-emerald-600 mr-1"></i> Haz clic en cualquier día del calendario para programar una actividad directamente.
            </div>
        </div>
    </div>

    <!-- Leyenda de colores -->
    <div class="glass-card mb-4" style="padding: 14px 20px;">
        <div class="legend-container">
            <div class="legend-item"><div class="legend-color" style="background: #3b82f6;"></div> Actividad Pendiente / En Progreso</div>
            <div class="legend-item"><div class="legend-color" style="background: #10b981;"></div> Actividad Completada</div>
            <div class="legend-item"><div class="legend-color" style="background: #ef4444;"></div> Actividad Cancelada</div>
            <div class="legend-item"><div class="legend-color" style="background: #f59e0b;"></div> Cosecha Estimada</div>
            <div class="legend-item"><div class="legend-color" style="background: #15803d;"></div> Ya Cosechado</div>
            <div class="legend-item"><span class="badge badge-warning px-2 py-1"><i class="fa-solid fa-star text-white"></i></span> Actividades del Día Actual (Resaltadas)</div>
        </div>
    </div>

    <!-- Contenedor del Calendario -->
    <div class="glass-card">
        <div id="calendar"></div>
    </div>

</div>

<!-- Modal Directo para Programar Actividad en Fecha Seleccionada -->
<div class="modal fade" id="modalCrearActividadCalendario" tabindex="-1" role="dialog" aria-labelledby="modalCrearActividadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('actividades.store') }}" method="POST" id="formCrearActividadCalendario" class="modal-content custom-modal-content">
            @csrf
            <input type="hidden" name="redirect_to" value="calendario">

            <div class="custom-modal-header">
                <div class="d-flex align-items-center" style="gap: 14px;">
                    <div class="modal-icon-badge">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold m-0" id="modalCrearActividadLabel" style="font-family: 'Outfit', sans-serif;">
                            Programar Actividad Agrícola
                        </h5>
                        <small style="color: rgba(255,255,255,0.9); font-size: 0.8rem;" id="modalCrearActividadSubtitulo">
                            Agendar labor en campo para la fecha seleccionada
                        </small>
                    </div>
                </div>
                <button type="button" class="close-modal-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <!-- Indicador de Fecha Elegida -->
                <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <div class="selected-date-badge">
                        <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                        <span>Fecha seleccionada: <strong id="fecha_seleccionada_texto"></strong></span>
                    </div>
                    <span class="text-muted" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-circle-info mr-1 text-emerald-600"></i> Labor con fecha hoy o futura
                    </span>
                </div>

                <div class="row">
                    <!-- Fecha Programada -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="cal_fecha_programada">
                            <i class="fa-regular fa-calendar text-emerald-600"></i> Fecha Programada *
                        </label>
                        <input type="date" name="fecha_programada" id="cal_fecha_programada" 
                               class="modal-input" 
                               min="{{ date('Y-m-d') }}" 
                               required 
                               onchange="alCambiarFechaInput(this.value)">
                    </div>

                    <!-- Tipo de Actividad -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="cal_id_tipo_actividad">
                            <i class="fa-solid fa-clipboard-check text-emerald-600"></i> Tipo de Actividad *
                        </label>
                        <select name="id_tipo_actividad" id="cal_id_tipo_actividad" class="modal-select" required>
                            <option value="">Seleccionar tipo de labor...</option>
                            @foreach($tiposActividad as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Cultivo / Lote -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="cal_id_cultivo">
                            <i class="fa-solid fa-seedling text-emerald-600"></i> Cultivo / Lote Destino *
                        </label>
                        <select name="id_cultivo" id="cal_id_cultivo" class="modal-select" required onchange="alCambiarCultivo(this)">
                            <option value="">Seleccionar cultivo activo...</option>
                            @foreach($cultivos as $c)
                                @php
                                    $trabajadorAsignado = $c->lote && $c->lote->trabajadores->isNotEmpty() ? $c->lote->trabajadores->first()->id : '';
                                    $nombreLote = $c->lote->nombre ?? 'Sin Lote';
                                    $variedad = $c->variedad->nombre ?? 'N/A';
                                @endphp
                                <option value="{{ $c->id }}" data-trabajador-id="{{ $trabajadorAsignado }}">
                                    {{ $c->codigo }} — {{ $nombreLote }} ({{ $variedad }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Trabajador Asignado -->
                    <div class="col-md-6 mb-3">
                        <label class="modal-label" for="cal_asignado_a">
                            <i class="fa-solid fa-user-check text-emerald-600"></i> Trabajador Asignado *
                        </label>
                        <select name="asignado_a" id="cal_asignado_a" class="modal-select" required>
                            <option value="">Seleccionar trabajador responsable...</option>
                            @foreach($trabajadores as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Descripción de la actividad -->
                <div class="form-group mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="modal-label m-0" for="cal_descripcion">
                            <i class="fa-solid fa-align-left text-emerald-600"></i> Descripción de la Labor *
                        </label>
                        <small id="char_counter_desc" class="text-muted" style="font-size: 0.75rem;">Mínimo 10 caracteres</small>
                    </div>
                    <textarea name="descripcion" id="cal_descripcion" rows="2" 
                              class="modal-input" 
                              style="resize: none;" 
                              placeholder="Ej: Aplicar fertilización foliar y monitorear plagas en el surco central..." 
                              minlength="10" maxlength="500" required
                              oninput="actualizarContadorDesc(this.value)"></textarea>
                </div>

                <!-- Observaciones (Opcional) -->
                <div class="form-group mb-0">
                    <label class="modal-label" for="cal_observaciones">
                        <i class="fa-solid fa-comment-dots text-emerald-600"></i> Observaciones Adicionales (Opcional)
                    </label>
                    <textarea name="observaciones" id="cal_observaciones" rows="2" 
                              class="modal-input" 
                              style="resize: none;" 
                              placeholder="Insumos requeridos, precauciones de seguridad, herramientas..."></textarea>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn-modal-submit" id="btnSubmitCrearActividad">
                    <i class="fa-solid fa-calendar-check"></i> Programar Actividad
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script>
    var calendarInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var filtroTipo = document.getElementById('filtro_tipo_actividad');
        var btnLimpiar = document.getElementById('btn-limpiar-filtro');

        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            // Criterio 1: Vista mensual por defecto
            // Criterio 2: Vista semanal detallada disponible mediante botón timeGridWeek
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                list: 'Agenda'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                var url = new URL('{{ route("calendario.eventos") }}', window.location.origin);
                url.searchParams.set('start', fetchInfo.startStr.substring(0, 10));
                url.searchParams.set('end', fetchInfo.endStr.substring(0, 10));
                if (filtroTipo && filtroTipo.value) {
                    url.searchParams.set('id_tipo_actividad', filtroTipo.value);
                }

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    successCallback(data);
                })
                .catch(function(err) {
                    console.error('Error cargando eventos:', err);
                    failureCallback(err);
                });
            },
            eventDisplay: 'block',
            height: 'auto',
            dayMaxEvents: 3,
            
            // Criterio 3: Navegación desde el calendario al detalle de la actividad
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },

            // Selección de fecha directa en el calendario para crear actividad ahí mismo
            dateClick: function(info) {
                var fechaSeleccionada = info.dateStr.substring(0, 10);
                abrirModalCrearActividad(fechaSeleccionada);
            }
        });

        calendarInstance.render();

        // Filtro reactivo por tipo
        if (filtroTipo) {
            filtroTipo.addEventListener('change', function() {
                calendarInstance.refetchEvents();
            });
        }

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function() {
                if (filtroTipo) {
                    filtroTipo.value = '';
                    calendarInstance.refetchEvents();
                }
            });
        }
    });

    // Función principal para abrir el modal de creación rápida
    function abrirModalCrearActividad(fechaIso) {
        document.getElementById('formCrearActividadCalendario').reset();

        const inputFecha = document.getElementById('cal_fecha_programada');
        const hoyIso = '{{ date('Y-m-d') }}';
        
        // Si la fecha seleccionada es pasada, ajustarla al menos a hoy para validación after_or_equal:today
        const fechaAUsar = (fechaIso && fechaIso >= hoyIso) ? fechaIso : hoyIso;
        inputFecha.value = fechaAUsar;

        alCambiarFechaInput(fechaAUsar);
        actualizarContadorDesc('');

        $('#modalCrearActividadCalendario').modal('show');

        setTimeout(() => {
            document.getElementById('cal_id_tipo_actividad').focus();
        }, 400);
    }

    function alCambiarFechaInput(fechaIso) {
        if (!fechaIso) return;
        const partes = fechaIso.split('-');
        if (partes.length === 3) {
            const fechaObj = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const fechaHumana = fechaObj.toLocaleDateString('es-ES', opciones);
            document.getElementById('fecha_seleccionada_texto').textContent = fechaHumana.charAt(0).toUpperCase() + fechaHumana.slice(1);
        } else {
            document.getElementById('fecha_seleccionada_texto').textContent = fechaIso;
        }
    }

    function alCambiarCultivo(select) {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.dataset.trabajadorId) {
            const trabajadorId = selectedOption.dataset.trabajadorId;
            const selectTrabajador = document.getElementById('cal_asignado_a');
            if (trabajadorId && selectTrabajador) {
                selectTrabajador.value = trabajadorId;
            }
        }
    }

    function actualizarContadorDesc(texto) {
        const counter = document.getElementById('char_counter_desc');
        const len = (texto || '').trim().length;
        if (len < 10) {
            counter.textContent = `${len}/10 caracteres (mínimo 10)`;
            counter.className = 'text-danger font-weight-bold';
        } else {
            counter.textContent = `${len} caracteres ingresados`;
            counter.className = 'text-success font-weight-bold';
        }
    }
</script>
@endpush
