<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Iniciar sesión y generar token Sanctum para el cliente móvil/API.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = Usuario::with('rol')->where('email', strtolower($validated['email']))->first();

        if (! $usuario || ! Hash::check($validated['password'], $usuario->password)) {
            if ($usuario) {
                $usuario->increment('intentos_fallidos');
                if ($usuario->intentos_fallidos >= 5) {
                    $usuario->update(['bloqueado_hasta' => now()->addMinutes(15)]);
                }
            }

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Verificar si la cuenta está activa
        if (! $usuario->activo) {
            return response()->json([
                'message' => 'Tu cuenta ha sido desactivada. Comunícate con el administrador.',
            ], 403);
        }

        // Verificar si la cuenta está bloqueada temporalmente
        if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            $minutos = now()->diffInMinutes($usuario->bloqueado_hasta) + 1;
            return response()->json([
                'message' => "Cuenta temporalmente bloqueada por múltiples intentos fallidos. Intenta nuevamente en {$minutos} minuto(s).",
            ], 429);
        }

        // Restablecer contadores y registrar último acceso
        $usuario->update([
            'intentos_fallidos' => 0,
            'bloqueado_hasta'   => null,
            'ultimo_acceso'     => now(),
        ]);

        $deviceName = $validated['device_name'] ?? 'sigvos-mobile';
        $token = $usuario->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($usuario),
        ]);
    }

    /**
     * Registrar un nuevo usuario (rol Trabajador por defecto) y generar token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'device_name'           => ['nullable', 'string', 'max:100'],
            'telefono'              => ['nullable', 'string', 'max:30'],
        ]);

        $usuario = Usuario::create([
            'id_rol'            => 2, // 2 = Trabajador
            'nombre'            => $validated['name'],
            'email'             => strtolower($validated['email']),
            'password'          => Hash::make($validated['password']),
            'telefono'          => $validated['telefono'] ?? null,
            'activo'            => true,
            'intentos_fallidos' => 0,
            'ultimo_acceso'     => now(),
        ]);

        $usuario->load('rol');

        $deviceName = $validated['device_name'] ?? 'sigvos-mobile';
        $token = $usuario->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($usuario),
        ], 201);
    }

    /**
     * Obtener los datos del usuario autenticado actual.
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        if ($usuario) {
            $usuario->loadMissing('rol');
        }

        return response()->json([
            'user' => $this->formatUser($usuario),
        ]);
    }

    /**
     * Cerrar sesión revocando el token Sanctum actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Solicitar enlace de recuperación de contraseña.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Retornamos un mensaje consistente para evitar enumeración de usuarios
        return response()->json([
            'message' => 'Si el correo está registrado en SIGVOS, recibirás un enlace de recuperación.',
        ]);
    }

    /**
     * Formatear el usuario para consumo uniforme de la API y compatibilidad con Flutter.
     */
    private function formatUser(Usuario $usuario): array
    {
        return [
            'id'                => $usuario->id,
            'name'              => $usuario->nombre,
            'nombre'            => $usuario->nombre,
            'email'             => $usuario->email,
            'role'              => strtolower($usuario->rol?->nombre ?? ($usuario->id_rol === 1 ? 'administrador' : 'trabajador')),
            'rol'               => $usuario->rol?->nombre,
            'email_verified_at' => $usuario->email_verified_at?->toIso8601String(),
            'telefono'          => $usuario->telefono,
            'foto_perfil'       => $usuario->foto_perfil ? asset('storage/' . $usuario->foto_perfil) : null,
            'activo'            => (bool) $usuario->activo,
        ];
    }
}
