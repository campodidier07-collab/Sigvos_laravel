<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoteResource extends JsonResource
{
    /**
     * Transforma el recurso Lote en un array estructurado para la API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'identificador'     => $this->identificador,
            'nombre'            => $this->nombre,
            'ubicacion'         => $this->ubicacion,
            'area_ha'           => (float) $this->area_ha,
            'fotografia'        => $this->fotografia ? asset('storage/' . $this->fotografia) : null,
            'es_alternativo'    => (bool) $this->es_alternativo,
            'estado'            => $this->estado ?? 'disponible',
            'activo'            => (bool) $this->activo,
            'tipo_preferido'    => $this->whenLoaded('tipoPreferido', function () {
                return $this->tipoPreferido ? [
                    'id'     => $this->tipoPreferido->id,
                    'nombre' => $this->tipoPreferido->nombre,
                ] : null;
            }),
            'cultivo_activo'    => $this->whenLoaded('cultivoActivo', function () {
                if (! $this->cultivoActivo) {
                    return null;
                }
                return [
                    'id'                     => $this->cultivoActivo->id,
                    'codigo'                 => $this->cultivoActivo->codigo,
                    'variedad'               => $this->cultivoActivo->variedad?->nombre ?? 'Sin variedad',
                    'tipo_cultivo'           => $this->cultivoActivo->variedad?->tipoCultivo?->nombre ?? 'General',
                    'fecha_siembra'          => $this->cultivoActivo->fecha_siembra?->format('Y-m-d'),
                    'fecha_cosecha_estimada' => $this->cultivoActivo->fecha_cosecha_estimada?->format('Y-m-d'),
                    'estado'                 => $this->cultivoActivo->estado,
                ];
            }),
            'trabajadores'      => $this->whenLoaded('trabajadores', function () {
                return $this->trabajadores->map(fn ($u) => [
                    'id'     => $u->id,
                    'nombre' => $u->nombre,
                    'email'  => $u->email,
                ]);
            }),
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
