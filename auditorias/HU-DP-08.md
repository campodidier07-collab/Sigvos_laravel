# Auditoría de Calidad de Software: Historia de Usuario DP-8

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-8` |
| **Rol** | Trabajador |
| **Funcionalidad** | Consultar mis lotes, cultivos y actividades asignadas |
| **Propósito de Negocio** | Para saber qué debo atender durante la jornada y en qué estado se encuentra cada cultivo |
| **Módulo** | Módulo de Consulta Operativa del Trabajador / Dashboard y Lotes |
| **Calificación Inicial** | 🟡 **40 / 100** (CUMPLE PARCIALMENTE - Faltaba bloqueo de acceso a lotes ajenos, soporte offline en panel y mensaje claro sin asignaciones) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Visualización de lotes asignados | **Dado** que tengo lotes asignados por el administrador, **cuando** ingreso al módulo de consulta operativa, **entonces** el sistema debe mostrar únicamente la información que me corresponde. | 🟡 PARCIAL *(Filtro básico en lotes.index pero estadísticas de dashboard no acotadas al trabajador)* | 🟢 **CUMPLE** *(Filtro estricto por `whereHas('trabajadores')` y KPIs de dashboard segmentados por trabajador)* |
| **2** | Detalle del cultivo activo por lote | **Dado** que selecciono uno de mis lotes asignados, **cuando** abro su detalle, **entonces** el sistema debe mostrar la información del cultivo activo. | 🟢 CUMPLE | 🟢 **CUMPLE** *(Pestaña Cultivo Actual en `lotes/show.blade.php` con variedad, fechas, estado y barra de progreso)* |
| **3** | Consulta de actividades pendientes del día | **Dado** que tengo actividades programadas en mis cultivos, **cuando** reviso mi agenda diaria, **entonces** el sistema debe mostrar las tareas pendientes con su información principal. | 🟡 PARCIAL *(Rango `now()` excluía actividades programadas más temprano el mismo día)* | 🟢 **CUMPLE** *(Consulta con `whereDate('fecha_programada', today())` y sección visual "Actividades de hoy")* |
| **4** | Bloqueo de acceso a lotes no asignados | **Dado** que existen lotes asignados a otros trabajadores, **cuando** intento consultar uno que no me corresponde, **entonces** el sistema debe restringir el acceso y mostrar que no tengo permisos (403 Forbidden). | 🔴 NO CUMPLE *(Cualquier trabajador podía ver el detalle de cualquier lote o cultivo por URL directa)* | 🟢 **CUMPLE** *(Autorización estricta en `LoteController@show` y `CultivoController@show` arrojando HTTP 403)* |
| **5** | Sin asignaciones activas | **Dado** que no tengo lotes activos asignados, **cuando** ingreso a mi panel operativo, **entonces** el sistema debe informarlo claramente. | 🔴 NO CUMPLE *(Empty state genérico "No hay lotes registrados aún")* | 🟢 **CUMPLE** *(Banner y empty state explícito indicando "Sin asignaciones activas" en dashboard y listado)* |
| **6** | Consulta en modo offline | **Dado** que me encuentro sin conexión, **cuando** abro el módulo de mis lotes y cultivos sin conexión a internet, **entonces** el sistema debe permitir la consulta de la información disponible. | 🔴 NO CUMPLE *(Faltaba motor offline y caché en `dashboard_worker.blade.php`)* | 🟢 **CUMPLE** *(Motor offline con `localStorage`, banner reactivo y endpoint `/dashboard/data`)* |

---

## 2. Análisis Técnico Detallado por Criterio

### Criterio 1: Visualización de lotes asignados (CUMPLE)

* **Brecha Inicial:** Aunque en `LoteController@index` existía un filtro para trabajadores, las tarjetas y contadores del dashboard mostraban el total general de lotes de la finca (e.g., 10 lotes), generando confusión en el operador.
* **Solución Implementada:**
  1. En [app/Http/Controllers/DashboardController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/DashboardController.php#L25-L35), se segmentaron las métricas del trabajador:
     ```php
     if ($usuario->esTrabajador()) {
         $totalLotes = $usuario->lotes()->count();
         $cultivosActivos = Cultivo::whereNotNull('activo_en_lote')
             ->whereHas('lote.trabajadores', fn($q) => $q->where('id_usuario', $usuario->id))
             ->count();
     }
     ```
  2. En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L24-L29), se mantiene el filtrado para que el trabajador únicamente liste lotes donde tenga asignación activa (`wherePivot('activo', true)`).

---

### Criterio 2: Detalle del cultivo activo por lote (CUMPLE)

* **Comportamiento Verificado:**
  1. En [resources/views/lotes/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php#L358-L436), la pestaña **Cultivo Actual** renderiza la información agronómica del cultivo activo:
     - Código del cultivo (con hipervínculo a su detalle).
     - Fecha de siembra formateada (`d/m/Y`).
     - Tipo de cultivo y variedad.
     - Estado agronómico (`sembrado`, `creciendo`, etc.).
     - Fecha estimada de cosecha.
     - Indicador porcentual dinámico y barra de progreso hacia la fecha estimada de cosecha.
  2. Si el lote no posee un cultivo activo en desarrollo, despliega un estado informativo limpio que indica la disponibilidad del lote.

---

### Criterio 3: Consulta de actividades pendientes del día (CUMPLE)

* **Brecha Inicial:** La consulta de actividades utilizaba `whereBetween('fecha_programada', [now(), now()->addDays(7)])`. Si el trabajador consultaba el panel en horas de la tarde, las actividades matutinas del mismo día quedaban fuera por la comparación horaria contra `now()`.
* **Solución Implementada:**
  1. En [app/Http/Controllers/DashboardController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/DashboardController.php#L42-L65), se separó la consulta específica para la jornada actual utilizando:
     ```php
     $actividadesHoy = Actividad::with(['cultivo.lote', 'tipoActividad', 'asignadoA'])
         ->where('estado', 'pendiente')
         ->whereNotNull('fecha_programada')
         ->whereDate('fecha_programada', today())
         ->when($usuario->esTrabajador(), fn ($q) => $q->where('asignado_a', $usuario->id))
         ->orderBy('fecha_programada')
         ->get();
     ```
  2. En [resources/views/dashboard_worker.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard_worker.blade.php#L225-L255), se presenta la tarjeta "Actividades de hoy" con tipo de actividad, código del cultivo, nombre del lote y badge de estado pendiente.

---

### Criterio 4: Bloqueo de acceso a lotes no asignados (CUMPLE)

* **Brecha Inicial Crítica:** Existía una vulnerabilidad de control de acceso (IDOR). Si un trabajador modificaba el ID en la URL para consultar un lote que no le pertenecía (`/lotes/2`), el controlador permitía ver toda la información del lote ajeno.
* **Solución Implementada:**
  1. **Control de Acceso en Lotes:** En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L122-L130):
     ```php
     if ($usuario && $usuario->esTrabajador() && ! $lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
         abort(403, 'No tienes permisos para consultar este lote.');
     }
     ```
  2. **Control de Acceso en Cultivos:** En [app/Http/Controllers/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php#L166-L174):
     ```php
     if ($usuario && $usuario->esTrabajador() && ! $cultivo->lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
         abort(403, 'No tienes permisos para consultar la información de este cultivo.');
     }
     ```
  3. Ambos accesos no autorizados retornan respuesta HTTP `403 Forbidden`.

---

### Criterio 5: Sin asignaciones activas (CUMPLE)

* **Brecha Inicial:** Si un trabajador nuevo ingresaba sin tener lotes asignados, la vista mostraba un mensaje genérico para administradores ("No hay lotes registrados aún"), sin orientar al trabajador sobre su situación operativa.
* **Solución Implementada:**
  1. **En Dashboard Operativo:** En [resources/views/dashboard_worker.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard_worker.blade.php#L165-L180), cuando `total_lotes === 0`, se muestra un banner destacado:
     > **Sin asignaciones activas:** Actualmente no tienes lotes activos asignados bajo tu responsabilidad. El administrador debe asignarte un lote para comenzar a gestionar tus cultivos y actividades.
  2. **En Listado de Lotes:** En [resources/views/lotes/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/index.blade.php#L268-L280), el empty state comunica de manera inequívoca la ausencia de lotes asignados al usuario.

---

### Criterio 6: Consulta en modo offline (CUMPLE)

* **Implementación de Resiliencia Sin Conexión:**
  1. **Endpoint de Sincronización:** [app/Http/Controllers/DashboardController.php@data](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/DashboardController.php#L115-L244) provee la carga completa en formato JSON acotada al trabajador (`estadisticas`, `lotesAsignados`, `cosechasProximas`, `actividadesHoy`, `timestamp`).
  2. **Caché en Cliente:** En [resources/views/dashboard_worker.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard_worker.blade.php#L260-L380), el script gestiona la clave `sigvos_dashboard_worker_cache` en `localStorage`.
  3. **Banner Offline Reactivo:** Al dispararse el evento `window.addEventListener('offline')` o fallar la red, el sistema activa automáticamente el `#offlineWorkerBanner` mostrando:
     *"Modo Offline activado: Te encuentras sin conexión a internet. Mostrando la última información sincronizada disponible (Última sincronización: HH:MM:SS)"*.
  4. Al restablecerse la conexión (`online`), sincroniza inmediatamente los datos en segundo plano sin recargar la página.

---

## 3. Matriz de Archivos e Impacto Técnico

| Archivo Modificado / Creado | Tipo | Descripción de la Modificación |
| :--- | :--- | :--- |
| [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php) | Modificación | Validación de autorización en `show()` bloqueando lotes ajenos para trabajadores con HTTP 403. |
| [app/Http/Controllers/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php) | Modificación | Validación de autorización en `show()` bloqueando cultivos en lotes no asignados con HTTP 403. |
| [app/Http/Controllers/DashboardController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/DashboardController.php) | Modificación | Métricas acotadas a lotes del trabajador, consulta estricta de `actividadesHoy` y soporte JSON enriquecido. |
| [resources/views/dashboard_worker.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard_worker.blade.php) | Modificación | Banner de modo offline, banner de "Sin asignaciones activas" y motor JS de sincronización / localStorage. |
| [resources/views/lotes/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/index.blade.php) | Modificación | Empty state específico para trabajadores sin asignaciones activas. |
| [tests/Feature/TrabajadorConsultaOperativaTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/TrabajadorConsultaOperativaTest.php) | Creación | Suite automatizada de 6 pruebas unitarias y de integración para cada criterio de aceptación. |

---

## 4. Evidencia de Pruebas Automatizadas

Se ejecutó la suite de pruebas automatizadas en [tests/Feature/TrabajadorConsultaOperativaTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/TrabajadorConsultaOperativaTest.php):

```bash
php artisan test tests/Feature/TrabajadorConsultaOperativaTest.php
```

### Salida de Ejecución:

```
PASS  Tests\Feature\TrabajadorConsultaOperativaTest
✓ criterio 1 visualizacion de lotes asignados
✓ criterio 2 detalle del cultivo activo por lote
✓ criterio 3 consulta de actividades pendientes del dia
✓ criterio 4 bloqueo de acceso a lotes no asignados
✓ criterio 5 sin asignaciones activas
✓ criterio 6 consulta en modo offline

Tests:    6 passed (37 assertions)
Duration: 0.72s
```

### Ejecución de Suite Completa Acumulada (DP-1 a DP-8):

```bash
php artisan test tests/Feature/TrabajadorConsultaOperativaTest.php tests/Feature/AsignacionLoteTest.php tests/Feature/CultivoCreateTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/DashboardTest.php tests/Feature/Auth/WorkerAccessControlTest.php tests/Feature/Auth/AuthenticationTest.php
```

```
Tests:    44 passed (204 assertions)
Duration: 2.50s
```

---

## 5. Dictamen del QA Lead

La Historia de Usuario **DP-8: Consultar mis lotes, cultivos y actividades asignadas** ha sido auditada, corregida y certificada satisfactoriamente. Se cerraron las brechas de seguridad de control de acceso en la visualización de lotes y cultivos ajenos, se acotaron todas las métricas al ámbito operativo del trabajador autenticado, se garantizó la consulta de la agenda diaria de actividades y se implementó la persistencia y resiliencia offline ante caídas de conectividad.

**Calificación Final Otorgada:** 🟢 **100 / 100 — CERTIFICADO Y LISTO PARA PRODUCCIÓN**.
