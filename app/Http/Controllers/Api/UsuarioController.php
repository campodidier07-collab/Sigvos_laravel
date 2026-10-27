<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * Listar usuarios con búsqueda y filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Usuario::with('rol');

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('id_rol', $request->rol);
        }

        $usuarios = $query->orderBy('nombre')->paginate(15);

        return response()->json([
            'data' => $usuarios->items(),
            'current_page' => $usuarios->currentPage(),
            'last_page' => $usuarios->lastPage(),
            'total' => $usuarios->total(),
        ]);
    }

    /**
     * Listar los roles activos del sistema.
     */
    public function roles(): JsonResponse
    {
        $roles = Rol::where('activo', true)->get(['id', 'nombre', 'descripcion']);
        return response()->json(['data' => $roles]);
    }

    /**
     * Mostrar detalles de un usuario específico.
     */
    public function show(int $id): JsonResponse
    {
        $usuario = Usuario::with(['rol', 'lotes', 'actividadesAsignadas.tipoActividad', 'actividadesAsignadas.cultivo'])
            ->findOrFail($id);

        $actividadesPendientes = $usuario->actividadesAsignadas()
            ->where('estado', 'pendiente')
            ->orderBy('fecha_programada')
            ->limit(10)
            ->get();

        return response()->json([
            'usuario' => $usuario,
            'actividades_pendientes' => $actividadesPendientes,
        ]);
    }

    /**
     * Crear un nuevo usuario en el sistema.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_rol'   => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = true;
        $datos['email_verified_at'] = now();

        $usuario = Usuario::create($datos);

        return response()->json([
            'message' => 'Usuario creado con éxito.',
            'usuario' => $usuario->load('rol'),
        ], 201);
    }

    /**
     * Actualizar datos de un usuario.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);

        $reglas = [
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique('usuarios')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_rol'   => ['required', 'exists:roles,id'],
            'activo'   => ['nullable', 'boolean'],
        ];

        if ($request->filled('password')) {
            $reglas['password'] = ['required', 'string', 'min:6'];
        }

        $datos = $request->validate($reglas);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($datos['password']);
        }

        // Evitar que el admin se desactive a sí mismo
        if (array_key_exists('activo', $datos) && $usuario->id === $request->user()->id && !$datos['activo']) {
            return response()->json([
                'message' => 'No puedes desactivar tu propia cuenta activa.',
            ], 422);
        }

        $usuario->update($datos);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'usuario' => $usuario->load('rol'),
        ]);
    }

    /**
     * Desactivar o eliminar un usuario.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === $request->user()->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta.',
            ], 422);
        }

        // Si tiene historial, desactivar
        if ($usuario->actividadesCreadas()->count() > 0 || $usuario->actividadesAsignadas()->count() > 0) {
            $usuario->update(['activo' => false]);
            return response()->json([
                'message' => 'El usuario ha sido desactivado porque tiene historial de actividades.',
                'desactivado' => true,
            ]);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado definitivamente.',
            'eliminado' => true,
        ]);
    }
}
