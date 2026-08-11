@extends('layouts.adminlte')

@section('title', 'Calendario de Planificación')

@push('styles')
<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
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
        font-weight: 700;
        color: #1e293b;
    }
    .fc .fc-button-primary {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        color: #475569;
        text-transform: capitalize;
        font-weight: 600;
        border-radius: 8px;
    }
    .fc .fc-button-primary:not(:disabled):active, 
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: #e2e8f0;
        border-color: #cbd5e1;
        color: #1e293b;
    }
    .fc .fc-col-header-cell-cushion {
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 0;
    }
    .fc .fc-daygrid-day-number {
        color: #334155;
        font-weight: 600;
    }
    .fc-event {
        border-radius: 6px;
        border: none;
        padding: 2px 4px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: transform 0.1s;
    }
    .fc-event:hover {
        transform: scale(1.02);
    }
    
    .legend-container {
        display: flex;
        gap: 16px;
        margin-top: 16px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
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
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-4 pt-3">
    
    <div class="glass-card mb-4" style="padding: 20px;">
        <div class="header-title">
            <h2>Calendario de Planificación</h2>
            <p>Vista global de las actividades programadas y proyecciones de cosecha.</p>
        </div>
        
        <div class="legend-container">
            <div class="legend-item"><div class="legend-color" style="background: #3b82f6;"></div> Actividad Pendiente / En Progreso</div>
            <div class="legend-item"><div class="legend-color" style="background: #10b981;"></div> Actividad Completada</div>
            <div class="legend-item"><div class="legend-color" style="background: #ef4444;"></div> Actividad Cancelada</div>
            <div class="legend-item"><div class="legend-color" style="background: #f59e0b;"></div> Cosecha Estimada</div>
            <div class="legend-item"><div class="legend-color" style="background: #15803d;"></div> Ya Cosechado</div>
        </div>
    </div>

    <div class="glass-card">
        <div id="calendar"></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: '{{ route("calendario.eventos") }}',
            eventDisplay: 'block',
            height: 'auto',
            dayMaxEvents: true // allow "more" link when too many events
        });
        calendar.render();
    });
</script>
@endpush
