@extends('layouts.adminlte')

@section('title', 'Detalle de Cultivo')

@push('styles')
<!-- Ekko Lightbox CSS -->
<link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/ekko-lightbox/ekko-lightbox.css') }}">
<style>
    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8fafc;
    }
    .card-modern {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .card-modern-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-modern-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .card-modern-body {
        padding: 20px;
    }
    
    /* Icon Box */
    .cultivo-icon-box {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        margin: 0 auto 16px;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .cultivo-icon-box.secondary {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
    }
    
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
        align-items: center;
    }
    .info-list li:last-child {
        border-bottom: none;
    }
    .info-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .info-value {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.95rem;
    }
    
    /* Modern Tabs */
    .nav-modern {
        border-bottom: 2px solid #e2e8f0;
        padding: 0 20px;
        background: #ffffff;
    }
    .nav-modern .nav-item {
        margin-bottom: -2px;
    }
    .nav-modern .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 16px 20px;
        transition: all 0.2s;
    }
    .nav-modern .nav-link:hover {
        color: #10b981;
    }
    .nav-modern .nav-link.active {
        color: #10b981;
        border-bottom: 2px solid #10b981;
        background: transparent;
    }
    
    /* Modern Timeline */
    .modern-timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }
    .modern-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
        border-radius: 2px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .timeline-icon {
        position: absolute;
        left: -40px;
        top: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #10b981;
        border: 4px solid #ffffff;
        box-shadow: 0 0 0 1px #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.5rem;
    }
    .timeline-icon.completed { background: #10b981; }
    .timeline-icon.pending { background: #f59e0b; }
    .timeline-icon.cancelled { background: #ef4444; }
    .timeline-icon.gray { background: #94a3b8; }
    
    .timeline-content {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        border: 1px solid #e2e8f0;
    }
    .timeline-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .timeline-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1rem;
        margin: 0;
    }
    .timeline-date {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    /* Badges */
    .badge-soft-success { background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-info { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    
    /* Buttons */
    .btn-custom-outline {
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s;
    }
    .btn-custom-outline:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .btn-custom-primary {
        background: #10b981;
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-custom-primary:hover {
        background: #059669;
        color: white;
        text-decoration: none;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Cultivo {{ $cultivo->codigo }}" 
        subtitle="Detalles, historial y galería del cultivo." 
        icon="fa-seedling"
    >
        @if($cultivo->estaActivo() || auth()->user()->isAdmin())
        <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn-custom-primary" style="background: #2563eb;">
            <i class="fas fa-edit mr-1"></i> Actualizar Cultivo
        </a>
        @endif
    </x-module-header>

    <div class="row">
        <!-- Columna Izquierda: Info y Fechas -->
        <div class="col-md-4">
            <!-- Tarjeta Principal -->
            <div class="card-modern">
                <div class="card-modern-body text-center">
                    <div class="cultivo-icon-box {{ $cultivo->estaActivo() ? '' : 'secondary' }}">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 style="font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 4px;">{{ $cultivo->codigo }}</h3>
                    <p style="color: #64748b; font-weight: 600; margin-bottom: 20px;">
                        Lote: <a href="{{ route('lotes.show', $cultivo->lote) }}" style="color: #10b981; text-decoration: underline;">{{ $cultivo->lote->identificador }}</a>
                    </p>

                    <ul class="info-list text-left">
                        <li>
                            <span class="info-label">Estado</span>
                            @php
                                $badge = match($cultivo->estado) {
                                    'sembrado' => 'info',
                                    'creciendo' => 'primary',
                                    'cosechado' => 'success',
                                    'perdido' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge-soft-{{ $badge }}">{{ ucfirst($cultivo->estado) }}</span>
                        </li>
                        <li>
                            <span class="info-label">Tipo</span>
                            <span class="info-value">{{ $cultivo->variedad->tipoCultivo->nombre }}</span>
                        </li>
                        <li>
                            <span class="info-label">Variedad</span>
                            <span class="info-value">{{ $cultivo->variedad->nombre }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Fechas / Línea de tiempo simple -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <h4 class="card-modern-title"><i class="far fa-calendar-alt text-muted mr-2"></i> Fechas Clave</h4>
                </div>
                <div class="card-modern-body p-0">
                    <ul class="info-list" style="padding: 0 20px;">
                        <li>
                            <div>
                                <span class="info-label d-block">Siembra</span>
                                <span class="info-value">{{ $cultivo->fecha_siembra->format('d/m/Y') }}</span>
                            </div>
                            <span class="badge-soft-secondary" style="font-size:0.7rem;">Por {{ $cultivo->registradoPor->nombre }}</span>
                        </li>
                        <li>
                            <div>
                                <span class="info-label d-block">Cosecha Estimada</span>
                                <span class="info-value text-warning">{{ $cultivo->fecha_cosecha_estimada->format('d/m/Y') }}</span>
                            </div>
                            @if($cultivo->estaActivo())
                                @php
                                    $faltan = now()->diffInDays($cultivo->fecha_cosecha_estimada, false);
                                    $textoFalta = $faltan > 0 ? "Faltan {$faltan} días" : ($faltan === 0 ? "¡Es Hoy!" : "Pasó hace " . abs($faltan) . " días");
                                @endphp
                                <span class="badge-soft-{{ $faltan >= 0 ? 'warning' : 'danger' }}" style="font-size:0.7rem;">{{ $textoFalta }}</span>
                            @endif
                        </li>
                        @if($cultivo->estado == 'cosechado')
                        <li style="background: #f8fafc; padding: 12px; border-radius: 8px; margin: 12px 0;">
                            <div>
                                <span class="info-label d-block">Cosecha Real</span>
                                <span class="info-value text-success">{{ $cultivo->fecha_cosecha_real?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                            <span class="badge-soft-success">{{ $cultivo->cantidad_cosechada_kg }} Kg</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Tabs -->
        <div class="col-md-8">
            <div class="card-modern" style="padding:0;">
                <ul class="nav nav-modern">
                    <li class="nav-item">
                        <a class="nav-link active" href="#actividades" data-toggle="tab"><i class="fas fa-tasks mr-1"></i> Actividades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#galeria" data-toggle="tab"><i class="far fa-images mr-1"></i> Galería Fotográfica</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#observaciones" data-toggle="tab"><i class="far fa-comment-alt mr-1"></i> Observaciones</a>
                    </li>
                </ul>
                
                <div class="card-modern-body">
                    <div class="tab-content">
                        <!-- Tab Actividades -->
                        <div class="active tab-pane" id="actividades">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-modern-title">Historial de Tareas</h5>
                                @if($cultivo->estaActivo())
                                <a href="{{ route('actividades.create', ['cultivo' => $cultivo->id]) }}" class="btn-custom-outline text-decoration-none">
                                    <i class="fas fa-plus mr-1"></i> Programar Tarea
                                </a>
                                @endif
                            </div>

                            @if($cultivo->actividades->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p style="color: #64748b; font-weight: 600;">No se han registrado actividades para este cultivo.</p>
                                </div>
                            @else
                                <div class="modern-timeline">
                                    @foreach($cultivo->actividades()->orderByDesc('fecha_programada')->get() as $act)
                                        @php
                                            $iconClass = match($act->estado) {
                                                'completada' => 'completed',
                                                'cancelada' => 'cancelled',
                                                default => 'pending'
                                            };
                                            $badgeClass = match($act->estado) {
                                                'completada' => 'success',
                                                'cancelada' => 'danger',
                                                default => 'warning'
                                            };
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-icon {{ $iconClass }}">
                                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <a href="{{ route('actividades.show', $act) }}" class="timeline-title" style="text-decoration: none;">
                                                        {{ $act->tipoActividad->nombre }}
                                                        <span class="badge-soft-{{ $badgeClass }} ml-2" style="font-size: 0.7rem; vertical-align: middle;">{{ ucfirst($act->estado) }}</span>
                                                    </a>
                                                    <span class="timeline-date"><i class="far fa-clock mr-1"></i>{{ $act->fecha_programada->format('d/m/Y') }}</span>
                                                </div>
                                                <p style="color: #475569; font-size: 0.9rem; margin-bottom: 8px;">{{ $act->descripcion }}</p>
                                                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">
                                                    <i class="far fa-user mr-1"></i> Asignado a: <span style="color: #64748b;">{{ $act->asignadoA->nombre ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="timeline-item">
                                        <div class="timeline-icon gray" style="background: #e2e8f0; border-color: #f8fafc;">
                                            <i class="fas fa-flag-checkered" style="color: #94a3b8;"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Galería Fotográfica -->
                        <div class="tab-pane" id="galeria">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-modern-title">Evidencias Visuales</h5>
                                @if($cultivo->estaActivo() || auth()->user()->isAdmin())
                                <button type="button" class="btn-custom-outline" data-toggle="modal" data-target="#modalSubirFoto">
                                    <i class="fas fa-camera mr-1"></i> Subir Fotografía
                                </button>
                                @endif
                            </div>

                            @if($cultivo->fotos->isEmpty())
                                <div class="text-center py-5">
                                    <i class="far fa-images fa-3x mb-3" style="color: #cbd5e1;"></i>
                                    <p style="color: #64748b; font-weight: 600;">No hay fotografías registradas.</p>
                                </div>
                            @else
                                <div class="row">
                                    @foreach($cultivo->fotos as $foto)
                                    <div class="col-sm-4 mb-4">
                                        <div class="card-modern" style="margin-bottom: 0;">
                                            <a href="{{ Storage::url($foto->ruta) }}" data-toggle="lightbox" data-title="Subida por {{ $foto->usuario->nombre }}" data-gallery="gallery">
                                                <img src="{{ Storage::url($foto->ruta) }}" alt="Foto Cultivo" style="width: 100%; height: 160px; object-fit: cover;">
                                            </a>
                                            <div class="p-3">
                                                <p style="color: #475569; font-size: 0.85rem; margin-bottom: 8px;">{{ $foto->descripcion ?: 'Sin descripción' }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small style="color: #94a3b8; font-size: 0.75rem;"><i class="far fa-clock"></i> {{ $foto->fecha_captura->format('d/m/Y') }}</small>
                                                    @if(auth()->user()->isAdmin() || auth()->id() == $foto->id_usuario)
                                                    <form action="{{ route('fotos.destroy', $foto) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Borrar fotografía?');" style="margin:0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; font-size:0.8rem; cursor:pointer;"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tab Observaciones -->
                        <div class="tab-pane" id="observaciones">
                            <h5 class="card-modern-title mb-3">Notas del Cultivo</h5>
                            @if($cultivo->observaciones)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #10b981; padding: 20px; border-radius: 8px;">
                                    <p style="color: #334155; font-size: 0.95rem; margin: 0; line-height: 1.6;">{!! nl2br(e($cultivo->observaciones)) !!}</p>
                                </div>
                            @else
                                <p style="color: #94a3b8; font-weight: 600; font-style: italic;">No hay observaciones registradas para este cultivo.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Foto -->
<div class="modal fade" id="modalSubirFoto" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('fotos.store', $cultivo) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
        <div class="modal-header" style="background: #10b981; color: white; border-bottom: none; padding: 20px;">
          <h5 class="modal-title" style="font-weight: 700; font-family: 'Outfit', sans-serif;"><i class="fas fa-camera mr-2"></i> Subir Evidencia</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="padding: 24px;">
          <div class="form-group">
            <label for="foto" style="font-weight: 600; color: #475569;">Seleccionar Imagen (Max 5MB) <span class="text-danger">*</span></label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*" required>
              <label class="custom-file-label" for="foto" data-browse="Buscar" style="border-radius: 8px;">Elegir archivo...</label>
            </div>
          </div>
          <div class="form-group mt-4">
            <label for="descripcion" style="font-weight: 600; color: #475569;">Descripción Breve (Opcional)</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ej: Avance de crecimiento..." style="border-radius: 8px; border: 1px solid #cbd5e1;"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px;">
          <button type="button" class="btn-custom-outline" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-custom-primary"><i class="fas fa-upload mr-1"></i> Subir Foto</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<!-- Ekko Lightbox -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<!-- bs-custom-file-input -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
  $(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });
    bsCustomFileInput.init();
  })
</script>
@endpush
