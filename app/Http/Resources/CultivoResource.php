<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CultivoResource extends JsonResource
{
    /**
     * Transforma el recurso Cultivo en un array estructurado para la API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'codigo'                 => $this->codigo,
            'estado'                 => $this->estado,
            'fecha_siembra'          => $this->fecha_siembra?->format('Y-m-d'),
            'fecha_cosecha_estimada' => $this->fecha_cosecha_estimada?->format('Y-m-d'),
            'fecha_cosecha_real'     => $this->fecha_cosecha_real?->format('Y-m-d'),
            'cantidad_cosechada_kg'  => $this->cantidad_cosechada_kg ? (float) $this->cantidad_cosechada_kg : null,
            'observaciones'          => $this->observaciones,
            'fotografia'             => $this->fotografia ? asset('storage/' . $this->fotografia) : null,
            'activo_en_lote'         => $this->activo_en_lote,
            'lote'                   => $this->whenLoaded('lote', function () {
                return $this->lote ? [
                    'id'            => $this->lote->id,
                    'identificador' => $this->lote->identificador,
                    'nombre'        => $this->lote->nombre,
                    'ubicacion'     => $this->lote->ubicacion,
                    'area_ha'       => (float) $this->lote->area_ha,
                ] : null;
            }),
            'variedad'               => $this->whenLoaded('variedad', function () {
                return $this->variedad ? [
                    'id'           => $this->variedad->id,
                    'nombre'       => $this->variedad->nombre,
                    'tipo_cultivo' => $this->variedad->tipoCultivo?->nombre ?? 'General',
                ] : null;
            }),
            'registrado_por'         => $this->whenLoaded('registradoPor', function () {
                return $this->registradoPor ? [
                    'id'     => $this->registradoPor->id,
                    'nombre' => $this->registradoPor->nombre,
                ] : null;
            }),
            'actividades_count'      => $this->whenCounted('actividades'),
            'fotos_count'            => $this->whenCounted('fotos'),
            'created_at'             => $this->created_at?->toIso8601String(),
            'updated_at'             => $this->updated_at?->toIso8601String(),
        ];
    }
}
