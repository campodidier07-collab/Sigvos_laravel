# Auditoría de Calidad de Software: Historia de Usuario DP-13

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-13` |
| **Rol** | Administrador |
| **Funcionalidad** | Registrar actividades agrícolas realizadas |
| **Propósito de Negocio** | Para documentar todas las labores realizadas en los cultivos y mantener un historial completo |
| **Módulo** | Módulo de Gestión de Actividades Agrícolas e Historial de Cultivo |
| **Calificación Inicial** | 🔴 **30 / 100** (DEFICIENTE - El sistema solo contaba con el flujo de programar a futuro, no existía endpoint ni modal para asentar labores ya ejecutadas en campo con fecha pasada/hoy, faltaban filtros específicos por cultivo y por tipo de actividad en el listado, y no se validaba longitud mínima en la descripción) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Matriz de Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Registro de actividad realizada exitosamente | **Dado** que he completado una actividad en un cultivo, **cuando** registro la información correspondiente indicando tipo, fecha, descripción y cultivo asociado, **entonces** el sistema debe guardarla correctamente y muestra confirmación. | 🔴 NO CUMPLE *(Solo existía `actividades.store` enfocado en programación futura con estado pendiente; no permitía registrar directamente la labor realizada con `fecha_ejecucion` y estado completada)* | 🟢 **CUMPLE** *(Se implementó endpoint `POST /actividades/realizada` que almacena con `estado = 'completada'`, fija `fecha_ejecucion` y muestra alerta de éxito)* |
| **2** | Registro de actividades agrícolas por tipo | **Dado** que el administrador puede registrar distintos tipos de actividades realizadas en un cultivo, el sistema debe guardar la información y asociarla al historial correspondiente **cuando** selecciona el tipo de actividad (fertilización, fumigación o poda) y completa la información requerida, **entonces** el sistema guarda la actividad registrada y la vincula al historial del cultivo correspondiente. | 🟡 PARCIAL *(Existían los tipos en BD pero no un modal ergonómico en la vista del cultivo para seleccionarlos y alimentar su bitácora)* | 🟢 **CUMPLE** *(Modal interactivo `#modalRegistrarLaborRealizada` en `cultivos.show` con catálogo dinámico de tipos de labor vinculados al cultivo)* |
| **3** | Validación de fecha no futura | **Dado** que estoy registrando una actividad realizada, **cuando** ingreso una fecha futura, **entonces** el sistema debe validar la restricción, muestra un error y no permite continuar. | 🔴 NO CUMPLE *(No existía regla `before_or_equal:today`; de hecho el formulario existente forzaba fechas futuras)* | 🟢 **CUMPLE** *(Regla de validación estricta `['required', 'date', 'before_or_equal:today']` con mensaje: "La fecha de la actividad realizada no puede ser una fecha futura.")* |
| **4** | Consulta de historial de actividades por cultivo | **Dado** que tengo actividades registradas, **cuando** consulto el historial de un cultivo filtrando por un cultivo específico, **entonces** el sistema debe mostrar la información correspondiente. | 🔴 NO CUMPLE *(El listado de actividades no contaba con filtro por cultivo `id_cultivo` en el controlador ni en la vista)* | 🟢 **CUMPLE** *(Filtro desplegable en [actividades/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/actividades/index.blade.php) y scope `$query->where('id_cultivo', ...)` en el controlador)* |
| **5** | Consulta de historial por tipo de actividad | **Dado** que necesito revisar actividades de un mismo tipo, **cuando** aplico el filtro correspondiente por tipo de actividad, **entonces** el sistema debe mostrar los resultados asociados. | 🔴 NO CUMPLE *(Filtro por tipo de actividad inexistente en la interfaz general)* | 🟢 **CUMPLE** *(Filtro select por `id_tipo_actividad` implementado en frontend y filtrado en backend en `ActividadController@index`)* |
| **6** | Validación de descripción mínima | **Dado** que estoy registrando una actividad, **cuando** ingreso una descripción insuficiente menor a la permitida, **entonces** el sistema debe validarla, mostrar un error y no permite guardar. | 🔴 NO CUMPLE *(No había validación de longitud mínima `min:10` en descripciones de actividades)* | 🟢 **CUMPLE** *(Regla `'descripcion' => ['required', 'string', 'min:10', 'max:1000']` aplicada tanto en el registro de labor realizada como en la programación general)* |

---

## 2. Cambios Implementados y Arquitectura del Código

### 2.1 Enrutamiento ([routes/web.php](file:///c:/laragon/www/Sigvos_laravel/routes/web.php))
- Se registró la ruta específica para la acción de registrar labores terminadas:
  ```php
  Route::post('/actividades/realizada', [ActividadController::class, 'registrarRealizada'])
      ->name('actividades.registrar-realizada')
      ->middleware('role:administrador');
  ```

### 2.2 Controlador [App\Http\Controllers\ActividadController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/ActividadController.php)
1. **Filtros por Cultivo y Tipo en `index()` (Criterios 4 y 5):**
   ```php
   if ($request->filled('id_cultivo')) {
       $query->where('id_cultivo', $request->id_cultivo);
   }

   if ($request->filled('id_tipo_actividad')) {
       $query->where('id_tipo_actividad', $request->id_tipo_actividad);
   }
   ```
2. **Método `registrarRealizada(Request $request)` (Criterios 1, 2, 3 y 6):**
   ```php
   public function registrarRealizada(Request $request)
   {
       $request->validate([
           'id_cultivo'        => ['required', 'exists:cultivos,id'],
           'id_tipo_actividad' => ['required', 'exists:tipos_actividad,id'],
           'fecha_realizacion' => ['required', 'date', 'before_or_equal:today'],
           'descripcion'       => ['required', 'string', 'min:10', 'max:1000'],
           'observaciones'     => ['nullable', 'string', 'max:1000'],
           'evidencia_foto'    => ['nullable', 'image', 'max:4096'],
       ], [
           'fecha_realizacion.before_or_equal' => 'La fecha de la actividad realizada no puede ser una fecha futura.',
           'descripcion.min' => 'La descripción debe contener al menos 10 caracteres con el detalle técnico de la labor.',
       ]);

       // Guardar evidencia fotográfica si aplica
       $fotoRuta = null;
       if ($request->hasFile('evidencia_foto')) {
           $fotoRuta = $request->file('evidencia_foto')->store('actividades_evidencias', 'public');
       }

       $actividad = Actividad::create([
           'id_cultivo'        => $request->id_cultivo,
           'id_tipo_actividad' => $request->id_tipo_actividad,
           'creado_por'        => Auth::id() ?? 1,
           'descripcion'       => $request->descripcion,
           'observaciones'     => $request->observaciones,
           'fecha_programada'  => $request->fecha_realizacion,
           'fecha_ejecucion'   => $request->fecha_realizacion,
           'estado'            => 'completada',
           'evidencia_foto'    => $fotoRuta,
       ]);

       return redirect()->back()
           ->with('success', "Labor agrícola registrada exitosamente en el historial del cultivo.");
   }
   ```
3. **Validación de longitud mínima en programación general (`store`):**
   - Se adicionó la regla `'min:10'` para asegurar consistencia en todo el ciclo de vida de las actividades.

### 2.3 Interfaz de Usuario
1. **Filtros en [resources/views/actividades/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/actividades/index.blade.php):**
   - Se incorporaron selectores de filtro por Cultivo y por Tipo de Actividad en la barra de búsqueda y filtrado superior.
2. **Modal de Registro de Labor Realizada en [resources/views/cultivos/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/cultivos/show.blade.php):**
   - Botón directo `"Registrar Labor Realizada"` que despliega el modal interactivo `#modalRegistrarLaborRealizada`.
   - Selector de tipo de actividad cargado dinámicamente (`$tiposActividad`).
   - Campo de fecha restringido a hoy o fechas pasadas con atributo HTML5 `max="{{ date('Y-m-d') }}"`.
   - Campo de texto con `minlength="10"` y contador visual.
   - Carga opcional de fotografía de evidencia en campo.

---

## 3. Verificación y Pruebas Automatizadas

Se construyó la suite de pruebas automatizadas [tests/Feature/RegistroActividadesRealizadasTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/RegistroActividadesRealizadasTest.php), validando cada criterio de aceptación de manera aislada y rigurosa:

```bash
php artisan test tests/Feature/RegistroActividadesRealizadasTest.php
```

### Resultados de Ejecución:
```text
PASS  Tests\Feature\RegistroActividadesRealizadasTest
✓ test_criterio_1_registro_de_actividad_realizada_exitosamente           0.18s
✓ test_criterio_2_registro_de_actividades_agricolas_por_tipo             0.13s
✓ test_criterio_3_validacion_de_fecha_no_futura                          0.08s
✓ test_criterio_4_consulta_de_historial_de_actividades_por_cultivo       0.11s
✓ test_criterio_5_consulta_de_historial_por_tipo_de_actividad            0.12s
✓ test_criterio_6_validacion_de_descripcion_minima                       0.11s

Tests:    6 passed (27 assertions)
Duration: 0.78s
```

### Prueba de Regresión Completa (HU-DP-01 a HU-DP-13):
```bash
php artisan test tests/Feature/AsignacionLoteTest.php tests/Feature/BusquedaGlobalTest.php tests/Feature/CalendarioPlanificacionTest.php tests/Feature/CultivoCreateTest.php tests/Feature/DashboardTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/ModificacionReagendamientoActividadesTest.php tests/Feature/ProgramacionActividadesTest.php tests/Feature/RegistroActividadesRealizadasTest.php tests/Feature/TrabajadorConsultaOperativaTest.php
```
```text
Tests:    69 passed (343 assertions)
Duration: 3.78s
```
Zero regresiones detectadas en el sistema.

---

## 4. Dictamen Final de Calidad

> **CERTIFICACIÓN QA:**
> La Historia de Usuario **DP-13** cumple rigurosamente con los 6 criterios de aceptación definidos en la especificación de requerimientos de Sigvos. 
> 
> El flujo de documentación retrospectiva de labores agrícolas se encuentra plenamente operativo, seguro frente a fechas futuras inválidas o descripciones vacías/ambiguas, y completamente integrado con el historial y consultas por cultivo y tipo de actividad.
> 
> **CALIFICACIÓN: 100 / 100 — APROBADO AL 100% - CERTIFICADO**
