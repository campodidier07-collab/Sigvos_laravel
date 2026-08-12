@extends('layouts.adminlte')

@section('title', 'Galería de Cultivos')

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
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="glass-card mb-4" style="padding: 20px;">
        <div class="header-title">
            <h2>Galería de Cultivos</h2>
            <p>Visualiza el progreso fotográfico de todas las siembras.</p>
        </div>
    </div>

    @if($fotos->isEmpty())
        <div class="glass-card text-center p-5">
            <i class="fas fa-camera-retro fa-3x text-muted mb-3 block"></i>
            <h5 class="text-muted">Aún no se han registrado fotografías en el sistema.</h5>
            <p class="text-sm text-slate-400">Las fotos se suben directamente desde el perfil de cada cultivo.</p>
        </div>
    @else
        <div class="photo-grid mb-4">
            @foreach($fotos as $foto)
            <div class="photo-card">
                <div class="photo-wrapper">
                    <a href="{{ Storage::url($foto->ruta) }}" target="_blank">
                        <img src="{{ Storage::url($foto->ruta) }}" alt="Foto de cultivo">
                    </a>
                    <div class="photo-overlay">
                        <span class="photo-badge">
                            {{ $foto->cultivo->codigo ?? 'N/A' }}
                        </span>
                        
                        @if(auth()->user()->isAdmin() || auth()->id() === $foto->id_usuario)
                        <form action="{{ route('fotos.destroy', $foto) }}" method="POST" onsubmit="confirmarEliminacion(event, this, '¿Eliminar foto?', '¿Seguro que deseas eliminar esta foto?');" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-photo" title="Eliminar Foto">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="photo-info">
                    <p class="photo-desc">
                        @if($foto->descripcion)
                            "{{ $foto->descripcion }}"
                        @else
                            <i class="text-muted text-sm">Sin descripción</i>
                        @endif
                    </p>
                    <div class="photo-meta">
                        <span><i class="far fa-user mr-1"></i> {{ explode(' ', $foto->usuario->nombre ?? 'Usuario')[0] }}</span>
                        <span><i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($foto->fecha_captura)->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($fotos->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $fotos->links('pagination::bootstrap-4') }}
        </div>
        @endif
    @endif

</div>
@endsection
