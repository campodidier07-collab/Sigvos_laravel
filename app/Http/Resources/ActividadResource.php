<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActividadResource extends JsonResource
{
    /**
     * Transforma el recurso Actividad para la API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'descripcion'      => $this->descripcion,
            'observaciones'    => $this->observaciones,
            'estado'           => $this->estado ?? 'pendiente',
            'fecha_programada' => $this->fecha_programada?->format('Y-m-d'),
            'fecha_ejecucion'  => $this->fecha_ejecucion?->format('Y-m-d'),
            'fotografia'       => $this->fotografia ? asset('storage/' . $this->fotografia) : null,
            'tipo_actividad'   => $this->whenLoaded('tipoActividad', function () {
                return $this->tipoActividad ? [
                    'id'     => $this->tipoActividad->id,
                    'nombre' => $this->tipoActividad->nombre,
                ] : null;
            }),
            'cultivo'          => $this->whenLoaded('cultivo', function () {
                if (! $this->cultivo) return null;
                return [
                    'id'          => $this->cultivo->id,
                    'codigo'      => $this->cultivo->codigo,
                    'lote_nombre' => $this->cultivo->lote?->nombre ?? 'Sin lote',
                    'variedad'    => $this->cultivo->variedad?->nombre ?? 'Variedad',
                ];
            }),
            'asignado_a'       => $this->whenLoaded('asignadoA', function () {
                return $this->asignadoA ? [
                    'id'     => $this->asignadoA->id,
                    'nombre' => $this->asignadoA->nombre,
                    'email'  => $this->asignadoA->email,
                ] : null;
            }),
            'ejecutado_por'    => $this->whenLoaded('ejecutadoPor', function () {
                return $this->ejecutadoPor ? [
                    'id'     => $this->ejecutadoPor->id,
                    'nombre' => $this->ejecutadoPor->nombre,
                ] : null;
            }),
            'creado_por'       => $this->whenLoaded('creadoPor', function () {
                return $this->creadoPor ? [
                    'id'     => $this->creadoPor->id,
                    'nombre' => $this->creadoPor->nombre,
                ] : null;
            }),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
