@extends('layouts.adminlte')

@section('title', 'Detalle de Cultivo')
@section('page-title', 'Cultivo ' . $cultivo->codigo)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
  <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4">
    <!-- Tarjeta de Estado Principal -->
    <div class="card card-{{ $cultivo->estaActivo() ? 'success' : 'secondary' }} card-outline">
      <div class="card-body box-profile">
        <div class="text-center mb-3">
          <span class="img-circle elevation-2 bg-{{ $cultivo->estaActivo() ? 'success' : 'secondary' }} d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:80px;height:80px;font-size:2.5rem;">
            <i class="fas fa-seedling"></i>
          </span>
        </div>

        <h3 class="profile-username text-center">{{ $cultivo->codigo }}</h3>
        <p class="text-muted text-center">
          Lote: <a href="{{ route('lotes.show', $cultivo->lote) }}" class="text-dark font-weight-bold">{{ $cultivo->lote->identificador }}</a>
        </p>

        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item">
            <b>Estado</b> 
            @php
              $badge = match($cultivo->estado) {
                'sembrado' => 'info',
                'creciendo' => 'primary',
                'cosechado' => 'success',
                'perdido' => 'danger',
                default => 'secondary'
              };
            @endphp
            <a class="float-right text-{{ $badge }} font-weight-bold">
              {{ ucfirst($cultivo->estado) }}
            </a>
          </li>
          <li class="list-group-item">
            <b>Tipo</b> <a class="float-right text-dark">{{ $cultivo->variedad->tipoCultivo->nombre }}</a>
          </li>
          <li class="list-group-item">
            <b>Variedad</b> <a class="float-right text-dark">{{ $cultivo->variedad->nombre }}</a>
          </li>
        </ul>

        @if($cultivo->estaActivo() || auth()->user()->isAdmin())
        <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-primary btn-block"><b>Actualizar Cultivo</b></a>
        @endif
      </div>
    </div>
    
    <!-- Fechas -->
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><i class="far fa-calendar-alt mr-1"></i> Línea de Tiempo</h3>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <span class="text-muted d-block text-sm">Siembra</span>
            <strong>{{ $cultivo->fecha_siembra->format('d/m/Y') }}</strong>
            <span class="text-xs text-muted float-right">Registrado por {{ $cultivo->registradoPor->nombre }}</span>
          </li>
          <li class="list-group-item">
            <span class="text-muted d-block text-sm">Cosecha Estimada</span>
            <strong class="text-warning">{{ $cultivo->fecha_cosecha_estimada->format('d/m/Y') }}</strong>
            @if($cultivo->estaActivo())
              @php
                 $faltan = now()->diffInDays($cultivo->fecha_cosecha_estimada, false);
                 $textoFalta = $faltan > 0 ? "en {$faltan} días" : ($faltan === 0 ? "¡Hoy!" : "hace " . abs($faltan) . " días");
              @endphp
              <span class="badge badge-{{ $faltan >= 0 ? 'warning' : 'danger' }} float-right">{{ $textoFalta }}</span>
            @endif
          </li>
          @if($cultivo->estado == 'cosechado')
          <li class="list-group-item bg-light">
            <span class="text-muted d-block text-sm">Cosecha Real</span>
            <strong class="text-success">{{ $cultivo->fecha_cosecha_real?->format('d/m/Y') ?? '—' }}</strong>
            <span class="float-right badge badge-success">{{ $cultivo->cantidad_cosechada_kg }} Kg</span>
          </li>
          @endif
        </ul>
      </div>
    </div>

  </div>
  
  <div class="col-md-8">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#actividades" data-toggle="tab">Actividades</a></li>
          <li class="nav-item"><a class="nav-link" href="#galeria" data-toggle="tab">Galería Fotográfica</a></li>
          <li class="nav-item"><a class="nav-link" href="#observaciones" data-toggle="tab">Observaciones</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          
          <!-- Tab Actividades -->
          <div class="active tab-pane" id="actividades">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0 text-muted">Historial de Tareas</h5>
              @if($cultivo->estaActivo())
              <a href="{{ route('actividades.create', ['cultivo' => $cultivo->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus mr-1"></i> Programar Tarea
              </a>
              @endif
            </div>

            @if($cultivo->actividades->isEmpty())
              <div class="text-center py-5 text-muted">
                <i class="fas fa-tasks fa-3x mb-3 text-light"></i>
                <p>No se han registrado actividades para este cultivo.</p>
              </div>
            @else
              <div class="timeline timeline-inverse">
                @foreach($cultivo->actividades()->orderByDesc('fecha_programada')->get() as $act)
                  @php
                    $icono = match($act->tipoActividad->codigo) {
                      'RIEGO' => 'fa-tint bg-primary',
                      'FERTILIZACION' => 'fa-flask bg-warning',
                      'PODA' => 'fa-cut bg-secondary',
                      'FUMIGACION' => 'fa-bug bg-danger',
                      'COSECHA' => 'fa-leaf bg-success',
                      default => 'fa-check bg-info'
                    };
                  @endphp
                  <div>
                    <i class="fas {{ $icono }}"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> {{ $act->fecha_programada->format('d/m/Y') }}</span>
                      <h3 class="timeline-header">
                        <a href="{{ route('actividades.show', $act) }}">{{ $act->tipoActividad->nombre }}</a>
                        @if($act->estado == 'completada')
                          <span class="badge badge-success ml-1">Completada</span>
                        @elseif($act->estado == 'cancelada')
                          <span class="badge badge-danger ml-1">Cancelada</span>
                        @else
                          <span class="badge badge-warning ml-1">Pendiente</span>
                        @endif
                      </h3>
                      <div class="timeline-body">
                        {{ $act->descripcion }}
                        <br>
                        <small class="text-muted">Asignado a: {{ $act->asignadoA->nombre ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </div>
                @endforeach
                <div>
                  <i class="far fa-clock bg-gray"></i>
                </div>
              </div>
            @endif
          </div>
          
          <!-- Tab Galería Fotográfica -->
          <div class="tab-pane" id="galeria">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0 text-muted">Evidencias Visuales del Cultivo</h5>
              @if($cultivo->estaActivo() || auth()->user()->isAdmin())
              <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#modalSubirFoto">
                <i class="fas fa-camera mr-1"></i> Subir Fotografía
              </button>
              @endif
            </div>

            @if($cultivo->fotos->isEmpty())
              <div class="text-center py-5 text-muted bg-light rounded border">
                <i class="far fa-image fa-3x mb-3 text-secondary"></i>
                <p>No hay fotografías registradas para este cultivo.</p>
              </div>
            @else
              <div class="row">
                @foreach($cultivo->fotos as $foto)
                <div class="col-sm-4 mb-4">
                  <div class="card h-100 shadow-sm">
                    <a href="{{ Storage::url($foto->ruta) }}" data-toggle="lightbox" data-title="Subida por {{ $foto->usuario->nombre }}" data-gallery="gallery">
                      <img src="{{ Storage::url($foto->ruta) }}" class="card-img-top" alt="Foto Cultivo" style="height: 150px; object-fit: cover;">
                    </a>
                    <div class="card-body p-2">
                      <p class="card-text text-sm mb-1">{{ $foto->descripcion ?: 'Sin descripción' }}</p>
                      <small class="text-muted"><i class="far fa-clock"></i> {{ $foto->fecha_captura->format('d/m/Y H:i') }}</small>
                    </div>
                    @if(auth()->user()->isAdmin() || auth()->id() == $foto->id_usuario)
                    <div class="card-footer p-1 text-right bg-white">
                      <form action="{{ route('fotos.destroy', $foto) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Borrar fotografía?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i> Borrar</button>
                      </form>
                    </div>
                    @endif
                  </div>
                </div>
                @endforeach
              </div>
            @endif
          </div>
          
          <!-- Tab Observaciones -->
          <div class="tab-pane" id="observaciones">
            <h5 class="text-muted mb-3">Notas del Cultivo</h5>
            @if($cultivo->observaciones)
              <div class="callout callout-info">
                <p>{!! nl2br(e($cultivo->observaciones)) !!}</p>
              </div>
            @else
              <p class="text-muted font-italic">No hay observaciones registradas para este cultivo.</p>
            @endif
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
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="fas fa-camera mr-2"></i> Subir Evidencia</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="foto">Seleccionar Imagen (Max 5MB) <span class="text-danger">*</span></label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*" required>
              <label class="custom-file-label" for="foto" data-browse="Buscar">Elegir archivo...</label>
            </div>
          </div>
          <div class="form-group mt-3">
            <label for="descripcion">Descripción Breve (Opcional)</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Ej: Avance de crecimiento mes 2..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Subir Foto</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
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
@endsection
