@extends('layouts.adminlte')

@section('title', 'Asignaciones')

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
    .worker-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 24px;
        transition: transform 0.2s;
    }
    .worker-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }
    .worker-header {
        background: #f8fafc;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .worker-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: bold;
    }
    .worker-info h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .worker-info p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    .task-list {
        padding: 16px 20px;
    }
    .task-item {
        padding: 12px;
        border-radius: 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-item:last-child {
        margin-bottom: 0;
    }
    .task-details h5 {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #065f46;
    }
    .task-details p {
        margin: 0;
        font-size: 0.8rem;
        color: #047857;
    }
    .task-status {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pending { background: #fef9c3; color: #a16207; }
    .status-progress { background: #dbeafe; color: #1d4ed8; }
    .status-completed { background: #dcfce7; color: #166534; }
    .history-section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #cbd5e1;
    }
    .history-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .task-item.history-item {
        background: #f8fafc;
        border-color: #f1f5f9;
        opacity: 0.85;
    }
    .task-item.history-item .task-details h5 {
        color: #475569;
    }
    .task-item.history-item .task-details p {
        color: #64748b;
    }
    .empty-tasks {
        padding: 20px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
        font-style: italic;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="glass-card mb-4" style="padding: 20px;">
        <div class="header-title">
            <h2>Asignaciones de Trabajadores</h2>
            <p>Supervisa las actividades pendientes o en progreso asignadas a cada trabajador.</p>
        </div>
    </div>

    <div class="row">
        @forelse($trabajadores as $trabajador)
        <div class="col-md-6 col-lg-4">
            <div class="worker-card">
                <div class="worker-header">
                    <div class="worker-avatar">
                        {{ strtoupper(substr($trabajador->nombre, 0, 1)) }}
                    </div>
                    <div class="worker-info">
                        <h4>{{ $trabajador->nombre }}</h4>
                        <p><i class="fas fa-envelope mr-1"></i> {{ $trabajador->email }}</p>
                    </div>
                </div>
                <div class="task-list">
                    @if($trabajador->actividadesAsignadas->isEmpty())
                        <div class="empty-tasks">
                            <i class="fas fa-check-circle mb-2" style="font-size: 1.5rem; color: #cbd5e1;"></i><br>
                            Sin actividades pendientes
                        </div>
                    @else
                        @foreach($trabajador->actividadesAsignadas as $actividad)
                        <div class="task-item">
                            <div class="task-details">
                                <h5>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</h5>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $actividad->cultivo->lote->nombre ?? 'N/A' }} - {{ $actividad->cultivo->codigo ?? 'N/A' }}</p>
                                <p style="font-size: 0.75rem; margin-top: 4px; color: #64748b;"><i class="far fa-calendar-alt"></i> {{ $actividad->fecha_programada ? \Carbon\Carbon::parse($actividad->fecha_programada)->format('d/m/Y') : 'Sin fecha' }}</p>
                            </div>
                            <div>
                                <span class="task-status {{ $actividad->estado == 'pendiente' ? 'status-pending' : 'status-progress' }}">
                                    {{ str_replace('_', ' ', $actividad->estado) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    @if($trabajador->actividadesCompletadas->isNotEmpty())
                        <div class="history-section">
                            <div class="history-title"><i class="fas fa-history mr-1"></i> Historial Reciente (Completadas)</div>
                            @foreach($trabajador->actividadesCompletadas as $actividad)
                            <div class="task-item history-item">
                                <div class="task-details">
                                    <h5>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</h5>
                                    <p><i class="fas fa-map-marker-alt"></i> {{ $actividad->cultivo->lote->nombre ?? 'N/A' }} - {{ $actividad->cultivo->codigo ?? 'N/A' }}</p>
                                    <p style="font-size: 0.75rem; margin-top: 4px; color: #94a3b8;"><i class="far fa-calendar-alt"></i> Completada el {{ $actividad->fecha_programada ? \Carbon\Carbon::parse($actividad->fecha_programada)->format('d/m/Y') : 'Sin fecha' }}</p>
                                </div>
                                <div>
                                    <span class="task-status status-completed">
                                        COMPLETADA
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="glass-card text-center p-5">
                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay trabajadores registrados o disponibles.</h5>
            </div>
        </div>
        @endforelse
    </div>

</div>
@endsection
