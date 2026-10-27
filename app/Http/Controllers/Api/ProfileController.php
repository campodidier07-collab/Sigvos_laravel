<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Obtener los datos del perfil y métricas del usuario actual.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('rol');

        $actividadesAsignadas = $user->actividadesAsignadas()->count();
        $actividadesCompletadas = $user->actividadesCompletadas()->count();
        $actividadesPendientes = $user->actividadesAsignadas()->where('estado', 'pendiente')->count();

        return response()->json([
            'usuario' => [
                'id'          => $user->id,
                'nombre'      => $user->nombre,
                'email'       => $user->email,
                'telefono'    => $user->telefono,
                'foto_perfil' => $user->foto_perfil ? asset('storage/' . $user->foto_perfil) : null,
                'rol'         => $user->rol?->nombre ?? 'Usuario',
                'id_rol'      => $user->id_rol,
                'activo'      => (bool) $user->activo,
                'creado_en'   => $user->created_at?->format('Y-m-d'),
            ],
            'estadisticas' => [
                'asignadas'   => $actividadesAsignadas,
                'completadas' => $actividadesCompletadas,
                'pendientes'  => $actividadesPendientes,
            ],
        ]);
    }

    /**
     * Actualizar información básica del perfil (nombre, email, teléfono).
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique('usuarios')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($datos);
        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado exitosamente.',
            'usuario' => $user->loadMissing('rol'),
        ]);
    }

    /**
     * Actualizar contraseña validando la clave actual.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual no es correcta.',
                'errors'  => ['current_password' => ['La contraseña actual es incorrecta.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Contraseña cambiada con éxito.',
        ]);
    }

    /**
     * Actualizar foto de perfil del usuario.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        $user->update(['foto_perfil' => $path]);

        return response()->json([
            'message' => 'Foto de perfil actualizada correctamente.',
            'url'     => asset('storage/' . $path),
        ]);
    }
}
