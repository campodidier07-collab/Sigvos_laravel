@extends('layouts.adminlte')

@section('title', 'Reportes y Estadísticas')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 24px;
        border: 1px solid rgba(255,255,255,0.4);
        margin-bottom: 24px;
        height: 100%;
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
    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .chart-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .icon-pie { background: #dcfce7; color: #10b981; }
    .icon-doughnut { background: #fef3c7; color: #f59e0b; }
    .icon-bar { background: #dbeafe; color: #3b82f6; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
@php
    $logoBase64 = '';
    $logoPath = public_path('img/icono.png');
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
@endphp
<div class="container-fluid px-4 pt-3">
    
    <x-module-header 
        title="Reportes y Estadísticas" 
        subtitle="Visualiza el estado general de los cultivos, actividades recientes y rendimiento de las cosechas." 
        icon="fa-chart-bar"
    >
        <button id="exportPdfBtn" class="btn btn-primary" style="background: #10b981; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(16,185,129,0.2);">
            <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF
        </button>
    </x-module-header>

    <div class="row" id="reportes-container" style="padding: 10px; background: #f4f6f9; border-radius: 20px;">
      <!-- Gráfico 1: Cultivos por Estado -->
      <div class="col-md-6 mb-4">
        <div class="glass-card">
          <div class="chart-title">
            <div class="chart-icon-box icon-pie"><i class="fas fa-chart-pie"></i></div>
            Estado de los Cultivos
          </div>
          <div class="chart-body">
            <canvas id="chartCultivos" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>

      <!-- Gráfico 2: Actividades (últimos 30 días) -->
      <div class="col-md-6 mb-4">
        <div class="glass-card">
          <div class="chart-title">
            <div class="chart-icon-box icon-doughnut"><i class="fas fa-chart-pie"></i></div>
            Tareas (Últimos 30 días)
          </div>
          <div class="chart-body">
            <canvas id="chartActividades" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>

      <!-- Gráfico 3: Rendimiento por Variedad -->
      <div class="col-md-12 mb-4">
        <div class="glass-card" style="height: auto;">
          <div class="chart-title">
            <div class="chart-icon-box icon-bar"><i class="fas fa-chart-bar"></i></div>
            Rendimiento de Cosecha (Kg) por Variedad
          </div>
          <div class="chart-body">
            @if(empty($nombresVariedades))
              <div class="text-center py-5 text-muted">
                <div class="mb-3">
                    <i class="fas fa-box-open fa-3x" style="color: #cbd5e1;"></i>
                </div>
                <p style="color: #94a3b8; font-weight: 600;">Aún no hay registros de cosechas finalizadas para calcular el rendimiento.</p>
              </div>
            @else
              <canvas id="chartRendimiento" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            @endif
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-3.2.0/plugins/chart.js/Chart.min.js') }}"></script>
<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
  $(function () {
    
    // --- 1. Gráfico Cultivos (Pie) ---
    var donutDataCultivos = {
      labels: @json(array_map('ucfirst', $estadosCultivo)),
      datasets: [
        {
          data: @json($datosCultivoEstado),
          backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
          borderWidth: 0
        }
      ]
    }
    var pieChartCanvas = $('#chartCultivos').get(0).getContext('2d')
    var pieOptions     = {
      maintainAspectRatio : false,
      responsive : true,
      legend: {
          labels: { fontColor: '#475569', fontFamily: "'Outfit', sans-serif" }
      }
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
          backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#64748b'],
          borderWidth: 0
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
          backgroundColor     : '#3b82f6',
          borderColor         : '#2563eb',
          borderWidth         : 1,
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

    // --- Export to PDF Logic ---
    $('#exportPdfBtn').click(function() {
        var btn = $(this);
        var originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Generando...');
        btn.prop('disabled', true);

        var targetElement = document.getElementById('reportes-container'); 
        
        // Add a slight delay to ensure charts are fully rendered and animations finished
        setTimeout(function() {
            html2canvas(targetElement, {
                scale: 2, // Higher resolution
                useCORS: true,
                backgroundColor: '#f4f6f9' // background color for the PDF page
            }).then(function(canvas) {
                var imgData = canvas.toDataURL('image/png');
                var pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                var pdfWidth = pdf.internal.pageSize.getWidth();
                var pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                
                // --- PROFESSIONAL PDF DESIGN ---
                
                // 1. Header Background Block
                pdf.setFillColor(22, 51, 43); // Dark Green Theme (#16332b)
                pdf.rect(0, 0, pdfWidth, 26, 'F');
                
                // 2. Add System Logo
                var logoBase64 = '{!! $logoBase64 !!}';
                if (logoBase64) {
                    pdf.addImage(logoBase64, 'PNG', 10, 4, 18, 18);
                }

                // 3. Header Texts (White)
                pdf.setTextColor(255, 255, 255);
                pdf.setFontSize(16);
                pdf.setFont("helvetica", "bold");
                pdf.text("SIGVOS", 32, 12);
                
                pdf.setFontSize(10);
                pdf.setFont("helvetica", "normal");
                pdf.text("Sistema de Gestión Agrícola", 32, 18);
                
                // Timestamp on the right
                pdf.setFontSize(9);
                pdf.text("Generado: " + new Date().toLocaleDateString() + " " + new Date().toLocaleTimeString(), pdfWidth - 10, 15, { align: 'right' });
                
                // 4. Main Document Title
                pdf.setTextColor(30, 41, 59); // Slate-800
                pdf.setFontSize(14);
                pdf.setFont("helvetica", "bold");
                pdf.text("REPORTE GENERAL DE ESTADÍSTICAS", pdfWidth / 2, 40, { align: 'center' });
                
                // 5. Colored Dividing Line
                pdf.setDrawColor(16, 185, 129); // Emerald-500 (#10b981)
                pdf.setLineWidth(0.8);
                pdf.line(pdfWidth / 2 - 50, 44, pdfWidth / 2 + 50, 44);

                // 6. Add the captured image of the charts
                // Adjust Y position to start below the title area (Y=52)
                pdf.addImage(imgData, 'PNG', 5, 52, pdfWidth - 10, pdfHeight * ((pdfWidth - 10) / pdfWidth));
                
                // 7. Footer
                pdf.setFontSize(8);
                pdf.setTextColor(148, 163, 184); // Slate-400
                pdf.text("Documento generado automáticamente por SIGVOS - Módulo de Reportes", pdfWidth / 2, 290, { align: 'center' });
                
                pdf.save('Reporte_Oficial_SIGVOS.pdf');
                
                btn.html(originalText);
                btn.prop('disabled', false);
            }).catch(function(error) {
                console.error("Error generating PDF", error);
                alert("Hubo un error al generar el PDF.");
                btn.html(originalText);
                btn.prop('disabled', false);
            });
        }, 500);
    });
  })
</script>
@endpush
