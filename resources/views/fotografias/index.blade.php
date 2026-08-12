@extends('layouts.adminlte')

@section('title', 'Galería de Fotografías')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 24px;
        border: 1px solid rgba(255,255,255,0.4);
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
    
    /* Tabs Styles */
    .custom-tabs {
        display: flex;
        gap: 8px;
        background: #f8fafc;
        padding: 8px;
        border-radius: 16px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }
    .custom-tab-btn {
        flex: 1;
        padding: 12px 20px;
        border: none;
        background: transparent;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }
    .custom-tab-btn:hover {
        background: rgba(226, 232, 240, 0.5);
        color: #334155;
    }
    .custom-tab-btn.active {
        background: #10b981;
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
    }
    
    .tab-content-pane {
        display: none;
        animation: fadeIn 0.4s;
    }
    .tab-content-pane.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    .photo-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
    }
    .photo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    .photo-wrapper {
        position: relative;
        width: 100%;
        padding-top: 75%; /* 4:3 Aspect Ratio */
        background: #f1f5f9;
        overflow: hidden;
    }
    .photo-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .photo-card:hover .photo-wrapper img {
        transform: scale(1.05);
    }
    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        padding: 12px;
        background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .photo-badge {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(4px);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .btn-delete-photo {
        background: rgba(220, 38, 38, 0.8);
        color: white;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-delete-photo:hover {
        background: rgba(220, 38, 38, 1);
    }
    .photo-info {
        padding: 16px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .photo-desc {
        font-size: 0.875rem;
        color: #334155;
        margin-bottom: 12px;
        flex-grow: 1;
        font-weight: 500;
    }
    .photo-meta {
        font-size: 0.75rem;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
    }
    .empty-state {
        text-align: center;
        padding: 48px 20px;
    }
    .empty-state i {
        font-size: 3rem;
        color: #e2e8f0;
        margin-bottom: 16px;
    }
    .empty-state h5 {
        color: #64748b;
        font-weight: 600;
    }
    .empty-state p {
        color: #94a3b8;
        font-size: 0.875rem;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="glass-card mb-4" style="padding: 20px;">
        <div class="header-title">
            <h2>Galería de Fotografías</h2>
            <p>Visualiza y organiza las imágenes de lotes, cultivos y el progreso de siembras.</p>
        </div>
    </div>

    <!-- Pestañas -->
    <div class="custom-tabs">
        <button class="custom-tab-btn active" onclick="openTab('tab-cultivos', this)">
            <i class="fas fa-seedling mr-2"></i> Portadas de Cultivos
        </button>
        <button class="custom-tab-btn" onclick="openTab('tab-lotes', this)">
            <i class="fas fa-map mr-2"></i> Portadas de Lotes
        </button>
        <button class="custom-tab-btn" onclick="openTab('tab-actividades', this)">
            <i class="fas fa-clipboard-check mr-2"></i> Evidencia de Actividades
        </button>
    </div>

    <!-- Contenido: Evidencia de Actividades -->
    <div id="tab-actividades" class="tab-content-pane">
        @if($actividades->isEmpty())
            <div class="glass-card empty-state">
                <i class="fas fa-clipboard-check block"></i>
                <h5>Aún no se han registrado evidencias de actividades.</h5>
                <p>Las fotos se suben directamente al registrar o completar una actividad agrícola.</p>
            </div>
        @else
            <div class="photo-grid mb-4">
                @foreach($actividades as $actividad)
                <div class="photo-card">
                    <div class="photo-wrapper">
                        <a href="{{ asset('storage/' . $actividad->fotografia) }}" target="_blank">
                            <img src="{{ asset('storage/' . $actividad->fotografia) }}" alt="Evidencia de actividad">
                        </a>
                        <div class="photo-overlay">
                            <span class="photo-badge" style="background: rgba(245, 158, 11, 0.8);">
                                Actividad: {{ $actividad->tipoActividad->nombre ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="photo-info">
                        <p class="photo-desc">
                            @if($actividad->descripcion)
                                "{{ Str::limit($actividad->descripcion, 60) }}"
                            @else
                                <i class="text-muted text-sm" style="font-weight: 400;">Sin descripción detallada</i>
                            @endif
                        </p>
                        <div class="photo-meta">
                            <span><i class="far fa-user mr-1"></i> {{ explode(' ', $actividad->ejecutadoPor->nombre ?? 'Usuario')[0] }}</span>
                            <span><i class="far fa-calendar-alt mr-1"></i> {{ $actividad->fecha_programada->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Contenido: Portadas de Cultivos -->
    <div id="tab-cultivos" class="tab-content-pane active">
        @if($cultivos->isEmpty())
            <div class="glass-card empty-state">
                <i class="fas fa-seedling block"></i>
                <h5>No hay cultivos con imagen de portada.</h5>
                <p>Puedes subir una imagen principal al crear o editar un cultivo.</p>
            </div>
        @else
            <div class="photo-grid mb-4">
                @foreach($cultivos as $cultivo)
                <div class="photo-card">
                    <div class="photo-wrapper">
                        <a href="{{ asset('storage/' . $cultivo->fotografia) }}" target="_blank">
                            <img src="{{ asset('storage/' . $cultivo->fotografia) }}" alt="Portada de Cultivo">
                        </a>
                        <div class="photo-overlay">
                            <span class="photo-badge" style="background: rgba(16, 185, 129, 0.8);">
                                Cultivo
                            </span>
                        </div>
                    </div>
                    <div class="photo-info">
                        <p class="photo-desc text-center mb-1" style="font-size: 1.1rem; color: #065f46;">
                            {{ $cultivo->codigo }}
                        </p>
                        <p class="text-center text-sm text-muted mb-3">{{ $cultivo->variedad->nombre ?? 'Sin variedad' }}</p>
                        <div class="photo-meta text-center" style="justify-content: center;">
                            <a href="{{ route('cultivos.show', $cultivo) }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Ver Cultivo <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Contenido: Portadas de Lotes -->
    <div id="tab-lotes" class="tab-content-pane">
        @if($lotes->isEmpty())
            <div class="glass-card empty-state">
                <i class="fas fa-map block"></i>
                <h5>No hay lotes con imagen de portada.</h5>
                <p>Puedes subir una imagen principal al crear o editar un lote.</p>
            </div>
        @else
            <div class="photo-grid mb-4">
                @foreach($lotes as $lote)
                <div class="photo-card">
                    <div class="photo-wrapper">
                        <a href="{{ asset('storage/' . $lote->fotografia) }}" target="_blank">
                            <img src="{{ asset('storage/' . $lote->fotografia) }}" alt="Portada de Lote">
                        </a>
                        <div class="photo-overlay">
                            <span class="photo-badge" style="background: rgba(37, 99, 235, 0.8);">
                                Lote
                            </span>
                        </div>
                    </div>
                    <div class="photo-info">
                        <p class="photo-desc text-center mb-1" style="font-size: 1.1rem; color: #1e3a8a;">
                            {{ $lote->identificador }} - {{ $lote->nombre }}
                        </p>
                        <p class="text-center text-sm text-muted mb-3">{{ Str::limit($lote->ubicacion, 35) }}</p>
                        <div class="photo-meta text-center" style="justify-content: center;">
                            <a href="{{ route('lotes.show', $lote) }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Ver Lote <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function openTab(tabId, btnElement) {
    // Hide all panes
    const panes = document.querySelectorAll('.tab-content-pane');
    panes.forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Remove active class from all buttons
    const btns = document.querySelectorAll('.custom-tab-btn');
    btns.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected pane and set button active
    document.getElementById(tabId).classList.add('active');
    btnElement.classList.add('active');
}
</script>
@endpush
