@extends('layouts.adminlte')

@section('title', 'Reportes y Estadísticas')
@section('page-title', 'Módulo de Reportes')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Reportes</li>
@endsection

@section('content')
<div class="row">
  
  <!-- Gráfico 1: Cultivos por Estado -->
  <div class="col-md-6">
    <div class="card card-success card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Estado de los Cultivos</h3>
      </div>
      <div class="card-body">
        <canvas id="chartCultivos" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico 2: Actividades (últimos 30 días) -->
  <div class="col-md-6">
    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-doughnut mr-1"></i> Tareas (Últimos 30 días)</h3>
      </div>
      <div class="card-body">
        <canvas id="chartActividades" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico 3: Rendimiento por Variedad -->
  <div class="col-md-12">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Rendimiento de Cosecha (Kg) por Variedad</h3>
      </div>
      <div class="card-body">
        @if(empty($nombresVariedades))
          <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
            <p>Aún no hay registros de cosechas finalizadas para calcular el rendimiento.</p>
          </div>
        @else
          <canvas id="chartRendimiento" style="min-height: 250px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/chart.js/Chart.min.js') }}"></script>
<script>
  $(function () {
    
    // --- 1. Gráfico Cultivos (Pie) ---
    var donutDataCultivos = {
      labels: @json(array_map('ucfirst', $estadosCultivo)),
      datasets: [
        {
          data: @json($datosCultivoEstado),
          backgroundColor: ['#17a2b8', '#007bff', '#28a745', '#dc3545'],
        }
      ]
    }
    var pieChartCanvas = $('#chartCultivos').get(0).getContext('2d')
    var pieOptions     = {
      maintainAspectRatio : false,
      responsive : true,
    }
    new Chart(pieChartCanvas, {
      type: 'pie',
      data: donutDataCultivos,
      options: pieOptions
    });

    // --- 2. Gráfico Actividades (Doughnut) ---
    var donutDataActividades = {
      labels: @json(array_map('ucfirst', $estadosActividad)),
      datasets: [
        {
          data: @json($datosActividadEstado),
          backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
        }
      ]
    }
    var donutChartCanvas = $('#chartActividades').get(0).getContext('2d')
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutDataActividades,
      options: pieOptions
    });

    // --- 3. Gráfico Rendimiento (Bar) ---
    @if(!empty($nombresVariedades))
    var barChartData = {
      labels  : @json($nombresVariedades),
      datasets: [
        {
          label               : 'Kg Cosechados',
          backgroundColor     : '#007bff',
          borderColor         : '#007bff',
          data                : @json($kgVariedades)
        }
      ]
    }

    var barChartCanvas = $('#chartRendimiento').get(0).getContext('2d')
    var barChartOptions = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false,
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
      }
    }

    new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    });
    @endif
  })
</script>
@endsection
