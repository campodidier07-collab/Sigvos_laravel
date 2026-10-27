<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoCultivo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReporteController extends Controller
{
    /**
     * Muestra el panel principal de reportes y estadísticas con soporte para:
     * - Criterio 1: Reportes según tipo de análisis (productividad entre lotes, actividades por periodo, próximas cosechas).
     * - Criterio 4: Exportación a PDF (vía cliente/jsPDF).
     * - Criterio 5: Filtrado por tipo de cultivo.
     * - Criterio 6: Métricas clave en reportes (Kg/Ha, lote líder, etc.).
     */
    public function index(Request $request)
    {
        $tipoReporte = $request->query('tipo_reporte', 'productividad_lotes');
        $idTipoCultivo = $request->query('id_tipo_cultivo');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $tiposCultivo = TipoCultivo::where('activo', true)->orderBy('nombre')->get();
        $tipoCultivoSeleccionado = $idTipoCultivo ? TipoCultivo::find($idTipoCultivo) : null;

        $productividad = $tipoReporte === 'productividad_lotes'
            ? $this->generarReporteProductividad($idTipoCultivo, $fechaInicio, $fechaFin)
            : ['metricas' => [], 'tabla' => [], 'grafico' => ['labels' => [], 'rendimientos' => [], 'totales_kg' => []]];

        $actividades = $tipoReporte === 'actividades_periodo'
            ? $this->generarReporteActividades($idTipoCultivo, $fechaInicio, $fechaFin)
            : ['metricas' => [], 'tabla' => [], 'grafico' => []];

        $cosechas = $tipoReporte === 'proximas_cosechas'
            ? $this->generarReporteProximasCosechas($idTipoCultivo, $fechaInicio, $fechaFin)
            : ['metricas' => [], 'tabla' => []];

        // Exportación a Excel (.xlsx) con diseño profesional Sigvos
        if (in_array($request->query('export'), ['excel', 'xlsx']) && $tipoReporte === 'productividad_lotes') {
            return $this->exportarExcelProductividad($productividad['tabla'], $productividad['metricas'], $tipoCultivoSeleccionado);
        }

        // Hardening DP-20: Soporte de exportación a CSV
        if ($request->query('export') === 'csv' && $tipoReporte === 'productividad_lotes') {
            return $this->exportarCsvProductividad($productividad['tabla']);
        }

        return view('reportes.index', [
            'tipoReporte'             => $tipoReporte,
            'idTipoCultivo'           => $idTipoCultivo,
            'tiposCultivo'            => $tiposCultivo,
            'tipoCultivoSeleccionado' => $tipoCultivoSeleccionado,
            'fechaInicio'             => $fechaInicio,
            'fechaFin'                => $fechaFin,
            'metricasProductividad'   => $productividad['metricas'],
            'tablaProductividad'      => $productividad['tabla'],
            'datosGraficoLotes'       => $productividad['grafico'],
            'metricasActividades'     => $actividades['metricas'],
            'tablaActividades'        => $actividades['tabla'],
            'datosActividadEstado'    => $actividades['grafico'],
            'estadosActividad'        => ['completada', 'pendiente', 'cancelada'],
            'metricasCosechas'        => $cosechas['metricas'],
            'tablaProximasCosechas'   => $cosechas['tabla'],
        ]);
    }

    /**
     * Exporta el reporte de productividad de lotes a Excel (.xlsx)
     * replicando el diseño corporativo Sigvos con banner, tarjetas KPI, métricas y badges de eficiencia.
     */
    private function exportarExcelProductividad(array $tabla, array $metricas, ?TipoCultivo $tipoCultivoSeleccionado): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte_Productividad_Lotes_SIG');
        $sheet->setShowGridLines(true);
        $sheet->getTabColor()->setRGB('064E3B');

        // Altura de filas
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(10);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(26);
        $sheet->getRowDimension(6)->setRowHeight(12);
        $sheet->getRowDimension(7)->setRowHeight(30);

        // BANNER SUPERIOR A1:H2 (#064E3B)
        $sheet->getStyle('A1:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('064E3B');

        // Logo institucional Sigvos
        $logoPath = public_path('img/logo.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('img/icono.png');
        }

        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('SIGVOS Logo');
            $drawing->setDescription('Logo Sigvos');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(14);
            $drawing->setOffsetY(4);
            $drawing->setHeight(44);
            $drawing->setWorksheet($sheet);
        }

        // Subtítulo banner: SIGVOS / SISTEMA DE GESTIÓN DE CULTIVOS
        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'SIGVOS');
        $sheet->getStyle('B1')->getFont()->setName('Segoe UI')->setSize(17)->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('B1')->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('B2:D2');
        $sheet->setCellValue('B2', 'SISTEMA DE GESTIÓN DE CULTIVOS');
        $sheet->getStyle('B2')->getFont()->setName('Segoe UI')->setSize(8.5)->setBold(true)->getColor()->setRGB('A7F3D0');
        $sheet->getStyle('B2')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Título banner derecho: REPORTE DE PRODUCTIVIDAD DE LOTES
        $sheet->mergeCells('E1:H2');
        $sheet->setCellValue('E1', 'REPORTE DE PRODUCTIVIDAD DE LOTES');
        $sheet->getStyle('E1')->getFont()->setName('Segoe UI')->setSize(14)->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('E1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Cálculo de métricas para tarjetas KPI
        $totalAreaVal = !empty($metricas['total_ha']) && $metricas['total_ha'] > 0
            ? $metricas['total_ha']
            : (float) array_sum(array_column($tabla, 'area_ha'));

        $lotesActivosCount = collect($tabla)->where('num_cosechas', '>', 0)->count();
        if ($lotesActivosCount === 0) {
            $lotesActivosCount = count($tabla);
        }

        $totalKgVal = isset($metricas['total_kg'])
            ? $metricas['total_kg']
            : (float) array_sum(array_column($tabla, 'total_kg'));

        $cardStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ];

        // Tarjeta KPI 1: Total Área
        $sheet->mergeCells('A4:B4');
        $sheet->setCellValue('A4', 'Total Área:');
        $sheet->getStyle('A4')->getFont()->setName('Segoe UI')->setSize(9.5)->getColor()->setRGB('475569');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', number_format($totalAreaVal, 1, ',', '.') . ' Ha');
        $sheet->getStyle('A5')->getFont()->setName('Segoe UI')->setSize(15)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:B5')->applyFromArray($cardStyle);

        // Tarjeta KPI 2: Lotes Activos
        $sheet->mergeCells('C4:D4');
        $sheet->setCellValue('C4', 'Lotes Activos:');
        $sheet->getStyle('C4')->getFont()->setName('Segoe UI')->setSize(9.5)->getColor()->setRGB('475569');
        $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('C5:D5');
        $sheet->setCellValue('C5', (string) $lotesActivosCount);
        $sheet->getStyle('C5')->getFont()->setName('Segoe UI')->setSize(15)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C4:D5')->applyFromArray($cardStyle);

        // Tarjeta KPI 3: Total Cosechado
        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E4', 'Total Cosechado:');
        $sheet->getStyle('E4')->getFont()->setName('Segoe UI')->setSize(9.5)->getColor()->setRGB('475569');
        $sheet->getStyle('E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('E5:F5');
        $sheet->setCellValue('E5', number_format($totalKgVal, 2, ',', '.') . ' Kg');
        $sheet->getStyle('E5')->getFont()->setName('Segoe UI')->setSize(15)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('E4:F5')->applyFromArray($cardStyle);

        // Tarjeta Informativa Derecha: Fecha y Filtro
        $sheet->mergeCells('G4:H4');
        $sheet->setCellValue('G4', 'Fecha de Exportación:  ' . Carbon::now()->format('d/m/Y'));
        $sheet->getStyle('G4')->getFont()->setName('Segoe UI')->setSize(9.5)->getColor()->setRGB('334155');
        $sheet->getStyle('G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

        $nombreFiltro = $tipoCultivoSeleccionado ? $tipoCultivoSeleccionado->nombre : 'Todos los Lotes';
        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Filtro:  ' . $nombreFiltro);
        $sheet->getStyle('G5')->getFont()->setName('Segoe UI')->setSize(9.5)->getColor()->setRGB('334155');
        $sheet->getStyle('G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

        $metaCardStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
        ];
        $sheet->getStyle('G4:H5')->applyFromArray($metaCardStyle);

        // CABECERA DE TABLA (Fila 7)
        $encabezados = [
            'A7' => 'Lote',
            'B7' => 'Nombre',
            'C7' => 'Área (Ha)',
            'D7' => 'Variedades',
            'E7' => 'N° Cosechas',
            'F7' => 'Total (Kg)',
            'G7' => 'Rendimiento (Kg/Ha)',
            'H7' => 'Eficiencia',
        ];

        foreach ($encabezados as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A7:H7')->applyFromArray([
            'font' => [
                'name'  => 'Segoe UI',
                'size'  => 10.5,
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '064E3B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '043629'],
                ],
            ],
        ]);

        // FILAS DE DATOS (Fila 8 en adelante)
        $filaIndex = 8;
        foreach ($tabla as $item) {
            $sheet->getRowDimension($filaIndex)->setRowHeight(24);
            $isEven = ($filaIndex % 2 === 0);
            $bgColor = $isEven ? 'FFFFFF' : 'F0FDF4';

            $sheet->setCellValue("A{$filaIndex}", $item['identificador']);
            $sheet->setCellValue("B{$filaIndex}", $item['nombre']);
            $sheet->setCellValue("C{$filaIndex}", number_format($item['area_ha'], 1, ',', '.') . ' Ha');
            $sheet->setCellValue("D{$filaIndex}", $item['variedades'] ?: '-');
            $sheet->setCellValue("E{$filaIndex}", $item['num_cosechas']);
            $sheet->setCellValue("F{$filaIndex}", number_format($item['total_kg'], 2, ',', '.') . ' Kg');
            $sheet->setCellValue("G{$filaIndex}", number_format($item['rendimiento_kg_ha'], 2, ',', '.') . ' Kg/Ha');

            // Formato de badge para la columna Eficiencia
            $eficiencia = $item['eficiencia'] ?? 'Sin producción';
            $badgeText = $eficiencia;
            $badgeBg = 'E2E8F0';
            $badgeColor = '475569';

            if (stripos($eficiencia, 'alta') !== false) {
                $badgeText = 'Eficiencia alta ✔';
                $badgeBg = 'DCFCE7';
                $badgeColor = '15803D';
            } elseif (stripos($eficiencia, 'media') !== false) {
                $badgeText = 'Eficiencia media ⚠️';
                $badgeBg = 'FEF9C3';
                $badgeColor = 'A16207';
            } elseif (stripos($eficiencia, 'baja') !== false) {
                $badgeText = 'Baja eficiencia ⚠️';
                $badgeBg = 'FEE2E2';
                $badgeColor = 'B91C1C';
            } else {
                $badgeText = 'Sin producción';
                $badgeBg = 'E2E8F0';
                $badgeColor = '475569';
            }

            $sheet->setCellValue("H{$filaIndex}", $badgeText);

            // Estilos generales de la fila
            $sheet->getStyle("A{$filaIndex}:G{$filaIndex}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 10, 'color' => ['rgb' => '1E293B']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ]);

            // Alineaciones
            $sheet->getStyle("A{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("C{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("D{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("E{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("F{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("G{$filaIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

            // Estilo celda H (Badge Eficiencia)
            $sheet->getStyle("H{$filaIndex}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9.5, 'bold' => true, 'color' => ['rgb' => $badgeColor]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $badgeBg]],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ]);

            $filaIndex++;
        }

        // Anchos de columna optimizados
        $anchosColumna = [
            'A' => 12,
            'B' => 24,
            'C' => 16,
            'D' => 22,
            'E' => 15,
            'F' => 18,
            'G' => 22,
            'H' => 22,
        ];
        foreach ($anchosColumna as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }

        // Fijar paneles para scroll suave manteniendo banner y cabecera
        $sheet->freezePane('A8');

        $writer = new Xlsx($spreadsheet);

        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Reporte_Productividad_Lotes_SIGVOS.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ];

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $headers);
    }

    private function exportarCsvProductividad(array $tabla): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Reporte_Productividad_Lotes_SIGVOS.csv"',
        ];

        return response()->stream(function () use ($tabla) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Lote', 'Nombre', 'Area_Ha', 'Variedades', 'Num_Cosechas', 'Total_Kg', 'Rendimiento_Kg_Ha', 'Eficiencia'], ';');

            foreach ($tabla as $fila) {
                fputcsv($handle, [
                    $fila['identificador'],
                    $fila['nombre'],
                    number_format($fila['area_ha'], 2, ',', ''),
                    $fila['variedades'],
                    $fila['num_cosechas'],
                    number_format($fila['total_kg'], 2, ',', ''),
                    number_format($fila['rendimiento_kg_ha'], 2, ',', ''),
                    $fila['eficiencia'],
                ], ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function generarReporteProductividad(?string $idTipoCultivo, ?string $fechaInicio, ?string $fechaFin): array
    {
        $lotes = Lote::where('activo', true)->get();
        $tablaProductividad = [];
        $totalKgGeneral = 0;
        $totalHaGeneral = 0;

        foreach ($lotes as $lote) {
            $cultivosQuery = Cultivo::where('id_lote', $lote->id)->where('estado', 'cosechado');

            if ($idTipoCultivo) {
                $cultivosQuery->whereHas('variedad', function ($q) use ($idTipoCultivo) {
                    $q->where('id_tipo_cultivo', $idTipoCultivo);
                });
            }

            if ($fechaInicio) {
                $cultivosQuery->whereDate('fecha_cosecha_real', '>=', $fechaInicio);
            }
            if ($fechaFin) {
                $cultivosQuery->whereDate('fecha_cosecha_real', '<=', $fechaFin);
            }

            $totalKgLote = (float) $cultivosQuery->sum('cantidad_cosechada_kg');
            $numCosechas = $cultivosQuery->count();

            if ($idTipoCultivo && $numCosechas === 0 && $totalKgLote == 0) {
                continue;
            }

            $areaHa = (float) $lote->area_ha;
            $rendimientoKgHa = $areaHa > 0 ? round($totalKgLote / $areaHa, 2) : 0;

            $variedadesCosechadas = (clone $cultivosQuery)
                ->with('variedad.tipoCultivo')
                ->get()
                ->pluck('variedad.nombre')
                ->unique()
                ->values()
                ->toArray();

            $tablaProductividad[] = [
                'id'                => $lote->id,
                'identificador'     => $lote->identificador,
                'nombre'            => $lote->nombre,
                'area_ha'           => $areaHa,
                'total_kg'          => $totalKgLote,
                'num_cosechas'      => $numCosechas,
                'rendimiento_kg_ha' => $rendimientoKgHa,
                'variedades'        => implode(', ', $variedadesCosechadas) ?: 'Sin cosechas',
            ];

            $totalKgGeneral += $totalKgLote;
            if ($totalKgLote > 0 || $numCosechas > 0) {
                $totalHaGeneral += $areaHa;
            }
        }

        usort($tablaProductividad, fn($a, $b) => $b['rendimiento_kg_ha'] <=> $a['rendimiento_kg_ha']);

        $promedioGlobalRendimiento = $totalHaGeneral > 0 ? round($totalKgGeneral / $totalHaGeneral, 2) : 0;
        foreach ($tablaProductividad as &$item) {
            if ($item['rendimiento_kg_ha'] == 0) {
                $item['eficiencia'] = 'Sin producción';
                $item['badge_class'] = 'badge-secondary';
            } elseif ($item['rendimiento_kg_ha'] >= $promedioGlobalRendimiento * 1.1) {
                $item['eficiencia'] = 'Alta eficiencia';
                $item['badge_class'] = 'badge-success';
            } elseif ($item['rendimiento_kg_ha'] >= $promedioGlobalRendimiento * 0.9) {
                $item['eficiencia'] = 'Eficiencia media';
                $item['badge_class'] = 'badge-info';
            } else {
                $item['eficiencia'] = 'Baja eficiencia';
                $item['badge_class'] = 'badge-warning';
            }
        }
        unset($item);

        $loteMasProductivo = !empty($tablaProductividad) && $tablaProductividad[0]['rendimiento_kg_ha'] > 0
            ? $tablaProductividad[0]
            : null;

        $datosGraficoLotes = ['labels' => [], 'rendimientos' => [], 'totales_kg' => []];
        foreach ($tablaProductividad as $lotData) {
            $datosGraficoLotes['labels'][] = $lotData['identificador'] . ' (' . $lotData['nombre'] . ')';
            $datosGraficoLotes['rendimientos'][] = $lotData['rendimiento_kg_ha'];
            $datosGraficoLotes['totales_kg'][] = $lotData['total_kg'];
        }

        return [
            'metricas' => [
                'total_kg'                       => $totalKgGeneral,
                'total_ha'                       => $totalHaGeneral,
                'rendimiento_promedio'           => $promedioGlobalRendimiento,
                'total_lotes'                    => count($tablaProductividad),
                'lote_mas_productivo_nombre'     => $loteMasProductivo ? ($loteMasProductivo['identificador'] . ' - ' . $loteMasProductivo['nombre']) : 'N/A',
                'lote_mas_productivo_rendimiento' => $loteMasProductivo ? $loteMasProductivo['rendimiento_kg_ha'] : 0,
            ],
            'tabla'   => $tablaProductividad,
            'grafico' => $datosGraficoLotes,
        ];
    }

    private function generarReporteActividades(?string $idTipoCultivo, ?string $fechaInicio, ?string $fechaFin): array
    {
        $actividadesQuery = Actividad::with(['cultivo.lote', 'cultivo.variedad.tipoCultivo', 'trabajador', 'tipoActividad']);

        if ($idTipoCultivo) {
            $actividadesQuery->whereHas('cultivo.variedad', function ($q) use ($idTipoCultivo) {
                $q->where('id_tipo_cultivo', $idTipoCultivo);
            });
        }

        if ($fechaInicio) {
            $actividadesQuery->whereDate('fecha_programada', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $actividadesQuery->whereDate('fecha_programada', '<=', $fechaFin);
        }

        $tablaActividades = $actividadesQuery->orderBy('fecha_programada', 'desc')->get();
        $completadas = $tablaActividades->where('estado', 'completada')->count();
        $pendientes = $tablaActividades->where('estado', 'pendiente')->count();
        $canceladas = $tablaActividades->where('estado', 'cancelada')->count();
        $total = $tablaActividades->count();

        return [
            'metricas' => [
                'total'             => $total,
                'completadas'       => $completadas,
                'pendientes'        => $pendientes,
                'canceladas'        => $canceladas,
                'tasa_cumplimiento' => $total > 0 ? round(($completadas / $total) * 100, 1) : 0,
            ],
            'tabla'   => $tablaActividades,
            'grafico' => [$completadas, $pendientes, $canceladas],
        ];
    }

    private function generarReporteProximasCosechas(?string $idTipoCultivo, ?string $fechaInicio, ?string $fechaFin): array
    {
        $cosechasQuery = Cultivo::with(['lote', 'variedad.tipoCultivo', 'registradoPor'])
            ->whereIn('estado', ['sembrado', 'creciendo']);

        if ($idTipoCultivo) {
            $cosechasQuery->whereHas('variedad', function ($q) use ($idTipoCultivo) {
                $q->where('id_tipo_cultivo', $idTipoCultivo);
            });
        }

        if ($fechaInicio) {
            $cosechasQuery->whereDate('fecha_cosecha_estimada', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $cosechasQuery->whereDate('fecha_cosecha_estimada', '<=', $fechaFin);
        }

        $tablaCosechas = $cosechasQuery->orderBy('fecha_cosecha_estimada', 'asc')->get();
        $totalKgEstimados = (float) $tablaCosechas->sum('cantidad_estimada_kg');
        $hoy = Carbon::today();
        $en30Dias = Carbon::today()->addDays(30);

        $proximos30 = $tablaCosechas->filter(function ($c) use ($hoy, $en30Dias) {
            return $c->fecha_cosecha_estimada && $c->fecha_cosecha_estimada >= $hoy && $c->fecha_cosecha_estimada <= $en30Dias;
        })->count();

        return [
            'metricas' => [
                'total_cultivos'     => $tablaCosechas->count(),
                'total_kg_estimados' => $totalKgEstimados,
                'proximos_30_dias'   => $proximos30,
                'total_lotes'        => $tablaCosechas->pluck('id_lote')->unique()->count(),
            ],
            'tabla' => $tablaCosechas,
        ];
    }
}

