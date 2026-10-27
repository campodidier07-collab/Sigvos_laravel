# Auditoría de Calidad de Software: Historia de Usuario DP-12

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-12` |
| **Rol** | Administrador |
| **Funcionalidad** | Modificar o reagendar actividades que aún no se han ejecutado |
| **Propósito de Negocio** | Para ajustar la planificación según cambios climáticos o necesidades del cultivo |
| **Módulo** | Módulo de Gestión y Reagendamiento de Actividades Agrícolas |
| **Calificación Inicial** | 🔴 **35 / 100** (NO CUMPLE / DEFICIENTE - Se permitía editar actividades ya completadas alterando el historial de ejecución, el selector de cultivo estaba bloqueado impidiendo corregir el cultivo objetivo, no se validaban fechas pasadas al reagendar, y no existía soporte para edición masiva de labores recurrentes) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Matriz de Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Reagendar actividad programada | **Dado** que tengo una actividad programada pendiente, **cuando** cambio su fecha, **entonces** el sistema debe actualizar la programación, guardar la nueva fecha y actualizar el recordatorio (cancelando el anterior si existía). | 🟡 PARCIAL *(Se actualizaba en BD pero no se depuraban las notificaciones obsoletas vinculadas a la fecha previa)* | 🟢 **CUMPLE** *(Se almacena la nueva fecha programada y se purgan recordatorios obsoletos)* |
| **2** | Modificar descripción de actividad pendiente | **Dado** que necesito ajustar la información de una actividad pendiente, **cuando** modifico su descripción, **entonces** el sistema debe guardar la nueva descripción sin alterar los demás datos. | 🟢 CUMPLE *(Se preservan intactos el cultivo, tipo de labor, usuario asignado y fechas)* | 🟢 **CUMPLE** *(Validado y cubierto por suite de pruebas automatizadas)* |
| **3** | Cambiar cultivo objetivo de actividad | **Dado** que necesito corregir el cultivo asociado a una actividad, **cuando** realizo el cambio, **entonces** el sistema debe actualizar la relación correspondiente y guardar el cambio. | 🔴 NO CUMPLE *(El input del cultivo en [edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/actividades/edit.blade.php) era de solo lectura `disabled` y el backend descartaba cambios en `id_cultivo`)* | 🟢 **CUMPLE** *(Selector dinámico para el Administrador que lista todos los cultivos activos de la explotación y actualiza la relación en BD)* |
| **4** | Restricción de modificación de actividades completadas | **Dado** que una actividad ya fue completada, **cuando** intento modificarla, **entonces** el sistema no debe permitirlo, debe bloquear la edición y mostrar una advertencia. | 🔴 NO CUMPLE *(Vulnerabilidad grave: se permitía editar y alterar la información o reabrir una labor ya ejecutada y reportada)* | 🟢 **CUMPLE** *(Protección doble: `edit()` y `update()` bloquean el acceso, redirigen al detalle con alerta flash: `"Operación denegada: La actividad ya fue completada y no permite modificaciones"`)* |
| **5** | Cancelar actividad programada | **Dado** que decido no realizar una actividad programada, **cuando** selecciono la opción de cancelar actividad, **entonces** el sistema cancela la actividad y la retira de la programación activa. | 🟢 CUMPLE *(Pasa a estado `cancelada` y cancela los recordatorios automáticos asociados)* | 🟢 **CUMPLE** *(Validado vía endpoint `POST /actividades/{id}/cancelar` y formulario general)* |
| **6** | Validación de fecha futura al reagendar | **Dado** que estoy reprogramando una actividad, **cuando** intento guardar una fecha anterior a la actual, **entonces** el sistema debe validar esa restricción, mostrar un error y no permitir guardar. | 🔴 NO CUMPLE *(Se permitía ingresar cualquier fecha pasada en la edición)* | 🟢 **CUMPLE** *(Regla de validación `after_or_equal:today` en backend, atributo `min="YYYY-MM-DD"` en frontend y mensaje específico)* |
| **7** | Modificación masiva de actividades recurrentes | **Dado** que una actividad pertenece a una programación recurrente, **cuando** realizo una modificación, **entonces** el sistema debe permitir elegir el alcance del cambio (una o varias ocurrencias). | 🔴 NO CUMPLE *(Inexistente; no había distinción ni control de alcance para series de tareas repetitivas)* | 🟢 **CUMPLE** *(Columna `grupo_recurrencia`, selector de alcance `solo_esta` vs `todas_pendientes` en el formulario y sincronización masiva en Eloquent)* |
| **8** | Notificación de cambios en actividades | **Dado** que realizo cambios en actividades programadas, **cuando** guardo la modificación, **entonces** el sistema debe informar la actualización mediante una notificación o feedback exitoso. | 🟢 CUMPLE *(Mensaje de sesión `success`: `"Actividad actualizada correctamente."` desplegado en el banner de la vista)* | 🟢 **CUMPLE** *(Verificado en redirección y en suite de pruebas)* |

---

## 2. Cambios Implementados y Arquitectura del Código

### 2.1 Migración de Base de Datos y Modelo
- **Migración:** [database/migrations/2026_10_25_024059_add_grupo_recurrencia_to_actividades_table.php](file:///c:/laragon/www/Sigvos_laravel/database/migrations/2026_10_25_024059_add_grupo_recurrencia_to_actividades_table.php)
  - Agregó el campo nullable e indexado `grupo_recurrencia` (VARCHAR 64) en la tabla `actividades`.
- **Modelo [App\Models\Actividad.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Actividad.php):**
  - Añadido `grupo_recurrencia` a la propiedad `$fillable`.

### 2.2 Controlador [App\Http\Controllers\ActividadController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/ActividadController.php)
1. **Bloqueo de Actividades Completadas (Criterio 4):**
   - En `edit()`:
     ```php
     if ($actividad->estado === 'completada') {
         return redirect()->route('actividades.show', $actividad)
             ->with('error', 'No se puede modificar una actividad que ya ha sido completada.');
     }
     ```
   - En `update()`:
     ```php
     if ($actividad->estado === 'completada') {
         return redirect()->route('actividades.show', $actividad)
             ->with('error', 'Operación denegada: La actividad ya fue completada y no permite modificaciones.');
     }
     ```
2. **Reasignación de Cultivo Objetivo (Criterio 3):**
   - La regla `$reglas['id_cultivo'] = ['required', 'exists:cultivos,id']` se procesa activamente en el formulario.
3. **Validación de Fecha Futura al Reagendar (Criterio 6):**
   - Regla `'fecha_programada' => ['required', 'date', 'after_or_equal:today']` con mensaje amigable: *"La fecha de reagendamiento no puede ser una fecha pasada. Debe ser hoy o una fecha futura."*.
4. **Modificación Masiva de Tareas Recurrentes (Criterio 7):**
   - Al seleccionar `alcance_recurrencia === 'todas_pendientes'`, el sistema propaga los cambios de descripción, cultivo objetivo, tipo de labor y trabajador asignado a todas las demás tareas pendientes pertenecientes al mismo `grupo_recurrencia`.

### 2.3 Vista de Edición ([resources/views/actividades/edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/actividades/edit.blade.php))
- **Selector de Cultivo Habilitado:** Despliega los cultivos con su código y lote asignado para administradores.
- **Restricción de Fecha:** Atributo `min="{{ date('Y-m-d') }}"` en el input de fecha programada.
- **Selector de Alcance de Recurrencia:** Bloque interactivo resaltado en verde esmeralda cuando la actividad pertenece a una serie recurrente (`$totalRecurrentes > 1`).

---

## 3. Cobertura de Pruebas Automatizadas

Se desarrolló la suite completa de pruebas funcionales en [tests/Feature/ModificacionReagendamientoActividadesTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/ModificacionReagendamientoActividadesTest.php):

```bash
php artisan test tests/Feature/ModificacionReagendamientoActividadesTest.php
```

```
   PASS  Tests\Feature\ModificacionReagendamientoActividadesTest
  ✓ criterio 1: reagendar actividad programada                           0.22s  
  ✓ criterio 2: modificar descripcion de actividad pendiente             0.09s  
  ✓ criterio 3: cambiar cultivo objetivo de actividad                    0.07s  
  ✓ criterio 4: restriccion de modificacion de actividades completadas   0.09s  
  ✓ criterio 5: cancelar actividad programada                            0.07s  
  ✓ criterio 6: validacion de fecha futura al reagendar                  0.07s  
  ✓ criterio 7: modificacion masiva de actividades recurrentes           0.08s  
  ✓ criterio 8: notificacion de cambios en actividades                   0.07s  

  Tests:    8 passed (37 assertions)
  Duration: 0.77s
```

### Verificación de Regresión Global:
Ejecución de todas las pruebas acumuladas de auditoría del proyecto (DP-01 a DP-12 + APIs):
```bash
php artisan test tests/Feature/Api/ tests/Feature/AsignacionLoteTest.php tests/Feature/BusquedaGlobalTest.php tests/Feature/CalendarioPlanificacionTest.php tests/Feature/CultivoCreateTest.php tests/Feature/DashboardTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/ModificacionReagendamientoActividadesTest.php tests/Feature/ProgramacionActividadesTest.php tests/Feature/TrabajadorConsultaOperativaTest.php
```

```
  Tests:    80 passed (463 assertions)
  Duration: 3.65s
```

---

## 4. Veredicto Final de Aseguramiento de Calidad

> [!IMPORTANT]
> **CERTIFICACIÓN QA LEAD:**
> La Historia de Usuario **DP-12: Modificar o reagendar actividades que aún no se han ejecutado** cumple de manera rigurosa con la totalidad de los criterios de aceptación especificados en el documento del proyecto. Se garantiza la integridad histórica de las labores ejecutadas y la flexibilidad operativa del administrador.

* **Puntuación Otorgada:** **100 / 100 (APROBADO AL 100% - CERTIFICADO)**
* **Estado:** ✅ **Completado y Verificado**
