<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * Mostrar listado de usuarios.
     */
    public function index(Request $request)
    {
        $query = Usuario::with('rol');

        // Búsqueda
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->where('id_rol', $request->rol);
        }

        $usuarios = $query->orderBy('nombre')->paginate(10);
        $roles = Rol::where('activo', true)->get();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $roles = Rol::where('activo', true)->get();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Almacenar un nuevo usuario.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_rol'   => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = true;
        $datos['email_verified_at'] = now(); // Auto-verificamos porque lo crea el admin

        Usuario::create($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * Mostrar detalles del usuario.
     */
    public function show(Usuario $usuario)
    {
        $usuario->load(['rol', 'lotes', 'actividadesAsignadas.tipoActividad', 'actividadesAsignadas.cultivo']);
        
        // Solo las últimas 5 actividades pendientes
        $actividadesPendientes = $usuario->actividadesAsignadas()
            ->where('estado', 'pendiente')
            ->orderBy('fecha_programada')
            ->limit(5)
            ->get();

        return view('usuarios.show', compact('usuario', 'actividadesPendientes'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Usuario $usuario)
    {
        $roles = Rol::where('activo', true)->get();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $reglas = [
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique('usuarios')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_rol'   => ['required', 'exists:roles,id'],
            'activo'   => ['boolean'],
        ];

        // Solo validamos password si enviaron algo
        if ($request->filled('password')) {
            $reglas['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $datos = $request->validate($reglas);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $datos['activo'] = $request->has('activo');

        // Evitar que el admin se desactive a sí mismo si es el único
        if ($usuario->id === auth()->id() && !$datos['activo']) {
            return back()->withInput()->withErrors(['activo' => 'No puedes desactivar tu propia cuenta activa.']);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Datos de usuario actualizados.');
    }

    /**
     * Eliminar usuario.
     */
    public function destroy(Request $request, Usuario $usuario)
    {
        // Evitar auto-eliminación
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Si el usuario tiene lotes o actividades, mejor hacer soft delete o evitar borrado
        if ($usuario->actividadesCreadas()->count() > 0 || $usuario->actividadesAsignadas()->count() > 0) {
            $usuario->update(['activo' => false]);
            return redirect()->route('usuarios.index')->with('success', 'El usuario se ha desactivado porque tiene historial en el sistema.');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado definitivamente.');
    }
}
