# Auditoría de Calidad de Software: Historia de Usuario DP-7

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-7` |
| **Rol** | Administrador |
| **Funcionalidad** | Asignar lotes a trabajadores activos |
| **Propósito de Negocio** | Para distribuir responsabilidades operativas y controlar qué trabajador atiende cada lote |
| **Módulo** | Módulo de Asignaciones y Personal / Gestión de Lotes |
| **Calificación Inicial** | 🔴 **30 / 100** (NO CUMPLE / INCOMPLETO - Sin modelo Eloquent ni endpoints de gestión) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Asignación exitosa de lote a trabajador | **Dado** que existe un trabajador activo y un lote habilitado para operar, **cuando** realizo la asignación, **entonces** el sistema debe registrarla correctamente (crea la asignación activa y muestra confirmación). | 🔴 NO CUMPLE *(Sin modelo ni endpoints para crear asignaciones de lote)* | 🟢 **CUMPLE** *(Endpoint `asignarLote`, creación en `asignaciones_lote` y flash de confirmación)* |
| **2** | Evitar duplicidad de asignación activa | **Dado** que el trabajador ya tiene una asignación activa sobre ese mismo lote, **cuando** intento registrarla nuevamente, **entonces** el sistema debe validarlo (rechaza la operación y muestra mensaje de error). | 🔴 NO CUMPLE *(Sin validación de duplicidad a nivel controlador)* | 🟢 **CUMPLE** *(Validación de `activo = true` previa + restricción única en `clave_activa`)* |
| **3** | Validar rol permitido para asignación | **Dado** que deseo asignar un lote a un usuario del sistema, **cuando** este no tiene el rol de trabajador (o está inactivo), **entonces** el sistema no debe permitir la asignación (bloquea la asignación y muestra la restricción). | 🔴 NO CUMPLE *(Sin validación de rol o estado del operador)* | 🟢 **CUMPLE** *(Validación estricta de `$usuario->esTrabajador()` y `$usuario->activo`)* |
| **4** | Consulta de lotes asignados por trabajador | **Dado** que necesito revisar la distribución actual de responsabilidades, **cuando** consulto el detalle de un trabajador, **entonces** el sistema debe mostrar sus lotes asignados y su estado. | 🟡 PARCIAL *(Vista de usuario solo mostraba nombre básico sin badge de estado ni superficie)* | 🟢 **CUMPLE** *(Detalle en `usuarios/show.blade.php` con badge de estado, hectáreas, fecha y desvinculación)* |
| **5** | Desactivar asignación sin perder historial | **Dado** que un trabajador deja de ser responsable de un lote, **cuando** desactivo la asignación vigente, **entonces** el sistema debe conservar el historial (desactiva la asignación y conserva su registro histórico). | 🔴 NO CUMPLE *(Sin endpoint para desasignar con trazabilidad)* | 🟢 **CUMPLE** *(Endpoint `desactivarLote` actualiza `activo = false`, `clave_activa = null` manteniendo la fila)* |
| **6** | Reasignación controlada de lote | **Dado** que necesito mover la responsabilidad de un lote a otro trabajador, **cuando** cierro una asignación y registro una nueva, **entonces** el sistema debe mantener la trazabilidad (conserva historial y deja activa solo la nueva asignación). | 🔴 NO CUMPLE *(Sin flujo ni endpoint de reasignación con trazabilidad)* | 🟢 **CUMPLE** *(Endpoint `reasignarLote` cierra asignación previa y activa la nueva preservando ambas)* |

---

## 2. Análisis Técnico Detallado por Criterio

### Criterio 1: Asignación exitosa de lote a trabajador (CUMPLE)

* **Brecha Inicial:** Existía la tabla `asignaciones_lote` en la base de datos, pero no existía el modelo Eloquent `AsignacionLote`, ni rutas ni controladores para que el Administrador pudiera registrar una asignación.
* **Solución Implementada:**
  1. **Modelo Eloquent:** Se creó [app/Models/AsignacionLote.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/AsignacionLote.php) con soporte de relaciones `usuario()`, `lote()`, `asignadoPor()` y casts booleanos y fechas.
  2. **Relaciones en Modelos:** En [app/Models/Usuario.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Usuario.php#L80-L91), se definió `lotes()` (`belongsToMany` filtrando únicamente asignaciones activas con pivote) y `asignacionesLote()` (`hasMany` para trazabilidad de todo el historial). En [app/Models/Lote.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Lote.php#L77-L88), se definieron las relaciones recíprocas `trabajadores()` y `asignaciones()`.
  3. **Controlador y Flujo:** En [app/Http/Controllers/AsignacionController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/AsignacionController.php#L43-L97), el método `asignarLote()` valida que el lote esté habilitado y el trabajador activo, genera la clave compuesta única `lote_{lote_id}_user_{user_id}`, persiste la asignación con `activo = true` y retorna un mensaje flash de confirmación con el nombre del lote y del trabajador.

---

### Criterio 2: Evitar duplicidad de asignación activa (CUMPLE)

* **Comportamiento Verificado:**
  1. Si un trabajador ya tiene una asignación con `activo = true` sobre el mismo lote, el método `asignarLote()` realiza una comprobación previa:
     ```php
     $existeActiva = AsignacionLote::where('id_lote', $lote->id)
         ->where('id_usuario', $usuario->id)
         ->where('activo', true)
         ->exists();
     ```
  2. Si existe, arroja una excepción de validación: `"El trabajador ya tiene una asignación activa sobre este lote."`, rechazando la operación.
  3. A nivel de base de datos, la columna `clave_activa` cuenta con un índice `UNIQUE KEY`, garantizando integridad ante concurrencia.

---

### Criterio 3: Validar rol permitido para asignación (CUMPLE)

* **Validación de Regla de Negocio:**
  1. **Rol Trabajador:** Se valida explícitamente `$usuario->esTrabajador()` (`id_rol === 2`). Si se intenta asignar a un administrador u otro rol, se rechaza con: `"Solo se pueden asignar lotes a usuarios con el rol de Trabajador."`.
  2. **Estado Activo del Trabajador:** Si el trabajador se encuentra desactivado (`!$usuario->activo`), el sistema rechaza la solicitud indicando: `"El trabajador seleccionado no se encuentra activo."`.
  3. **Lote Habilitado para Operar:** Si el lote se encuentra inactivo (`!$lote->activo`), se bloquea indicando: `"El lote seleccionado no está habilitado para operar."`.

---

### Criterio 4: Consulta de lotes asignados por trabajador (CUMPLE)

* **Implementación en UI y Controlador:**
  1. **Controlador:** [app/Http/Controllers/UsuarioController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/UsuarioController.php#L77) carga la relación `lotes` mediante `$usuario->load(['rol', 'lotes'])`.
  2. **Vista de Detalle:** En [resources/views/usuarios/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/usuarios/show.blade.php#L113-L177), si el usuario es trabajador, se habilita la pestaña **Lotes Asignados**.
  3. **Detalles Visuales:** Por cada lote asignado, se despliega el identificador y nombre, badge estilizado con el estado del lote (`disponible`, `ocupado`, `en descanso`), superficie en hectáreas (`area_ha`), ubicación, fecha en que fue asignado y botón de acceso rápido para ver el lote o desactivar la asignación.
  4. **Panel de Asignaciones:** En [resources/views/asignaciones/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/asignaciones/index.blade.php#L198-L253), cada tarjeta de trabajador lista sus lotes asignados con su estado y opciones de reasignación y desvinculación.

---

### Criterio 5: Desactivar asignación sin perder historial (CUMPLE)

* **Preservación Histórica:**
  1. El endpoint `POST /asignaciones/lotes/desactivar` ([app/Http/Controllers/AsignacionController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/AsignacionController.php#L102-L127)) busca la asignación activa (`activo = true`).
  2. Ejecuta una actualización suave de trazabilidad:
     ```php
     $asignacion->update([
         'activo'       => false,
         'clave_activa' => null,
     ]);
     ```
  3. El registro físico permanece en la tabla `asignaciones_lote`. El trabajador deja de tenerlo en `$trabajador->lotes` (lotes activos), pero se conserva en `$trabajador->asignacionesLote` para auditorías históricas.
  4. Al liberar `clave_activa = null`, se permite que el lote pueda volver a asignarse en el futuro sin violar la restricción de unicidad activa.

---

### Criterio 6: Reasignación controlada de lote (CUMPLE)

* **Flujo Controlado de Reasignación:**
  1. En [resources/views/asignaciones/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/asignaciones/index.blade.php#L350-L386), el modal `#modalReasignarLote` permite seleccionar un nuevo trabajador responsable, deshabilitando automáticamente al trabajador actual en el combo de selección.
  2. En [app/Http/Controllers/AsignacionController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/AsignacionController.php#L132-L192), el método `reasignarLote()` valida que el nuevo usuario sea un trabajador activo y que no tenga asignado previamente el lote.
  3. Cierra la asignación anterior (`activo = false`, `clave_activa = null`), registrando la fecha/hora de cierre.
  4. Crea la nueva asignación activa para el nuevo trabajador (`activo = true`, `clave_activa = "lote_{id}_user_{id}"`).
  5. Ambos registros coexisten en la base de datos, garantizando una auditoría completa del ciclo de vida del lote.

---

## 3. Matriz de Archivos e Impacto Técnico

| Archivo Modificado / Creado | Tipo | Descripción de la Modificación |
| :--- | :--- | :--- |
| [app/Models/AsignacionLote.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/AsignacionLote.php) | Creación | Modelo Eloquent para `asignaciones_lote` con relaciones hacia Usuario, Lote y AsignadoPor. |
| [app/Models/Usuario.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Usuario.php) | Modificación | Relación `lotes()` (activos con pivote) y `asignacionesLote()` (historial completo). |
| [app/Models/Lote.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Lote.php) | Modificación | Relación `trabajadores()` (activos con pivote) y `asignaciones()` (historial completo). |
| [app/Http/Controllers/AsignacionController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/AsignacionController.php) | Modificación | Métodos `asignarLote()`, `desactivarLote()` y `reasignarLote()` con validaciones de negocio. |
| [routes/web.php](file:///c:/laragon/www/Sigvos_laravel/routes/web.php) | Modificación | Rutas POST `asignaciones.lotes.store`, `asignaciones.lotes.desactivar` y `asignaciones.lotes.reasignar`. |
| [resources/views/usuarios/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/usuarios/show.blade.php) | Modificación | Pestaña Lotes Asignados con badges de estado, hectáreas, fecha y desvinculación rápida. |
| [resources/views/asignaciones/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/asignaciones/index.blade.php) | Modificación | Modales de asignación y reasignación controlada, y listado de lotes por trabajador. |
| [tests/Feature/AsignacionLoteTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/AsignacionLoteTest.php) | Creación | Suite automatizada de 6 pruebas unitarias y de integración para cada criterio de aceptación. |

---

## 4. Evidencia de Pruebas Automatizadas

Se diseñó y ejecutó la suite de pruebas unitarias y de integración en [tests/Feature/AsignacionLoteTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/AsignacionLoteTest.php):

```bash
php artisan test tests/Feature/AsignacionLoteTest.php
```

### Salida de Ejecución:

```
PASS  Tests\Feature\AsignacionLoteTest
✓ criterio 1 asignacion exitosa de lote a trabajador
✓ criterio 2 evitar duplicidad de asignacion activa
✓ criterio 3 validar rol permitido para asignacion
✓ criterio 4 consulta de lotes asignados por trabajador
✓ criterio 5 desactivar asignacion sin perder historial
✓ criterio 6 reasignacion controlada de lote

Tests:    6 passed (30 assertions)
Duration: 0.78s
```

### Ejecución de Suite Completa de Auditorías:

```bash
php artisan test tests/Feature/AsignacionLoteTest.php tests/Feature/CultivoCreateTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/DashboardTest.php tests/Feature/Auth/WorkerAccessControlTest.php tests/Feature/Auth/AuthenticationTest.php
```

```
Tests:    38 passed (167 assertions)
Duration: 2.38s
```

---

## 5. Dictamen del QA Lead

La Historia de Usuario **DP-7: Asignar lotes a trabajadores activos** ha sido auditada, implementada y validada en su totalidad. Se implementó la capa de persistencia mediante el modelo [AsignacionLote](file:///c:/laragon/www/Sigvos_laravel/app/Models/AsignacionLote.php), endpoints protegidos por perfil de Administrador, vistas con feedback visual y estado en tiempo real, así como la trazabilidad de asignaciones activas e históricas.

**Calificación Final Otorgada:** 🟢 **100 / 100 — CERTIFICADO Y LISTO PARA PRODUCCIÓN**.
