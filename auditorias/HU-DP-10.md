# Auditoría de Calidad de Software: Historia de Usuario DP-10

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-10` |
| **Rol** | Administrador |
| **Funcionalidad** | Programar actividades futuras con recordatorios automáticos |
| **Propósito de Negocio** | Para no olvidar fertilizaciones, fumigaciones u otras tareas importantes pendientes |
| **Módulo** | Módulo de Gestión de Actividades Agrícolas, Calendario y Motor de Notificaciones Automáticas |
| **Calificación Inicial** | 🔴 **30 / 100** (NO CUMPLE / DEFICIENTE - No existía motor de recordatorios automáticos, no había alertas automáticas de cosecha próxima ni de fertilización vencida, no se validaban fechas pasadas en la programación, y no se cancelaban notificaciones al reprogramar o cancelar actividades) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Matriz de Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Programación de actividad futura exitosa | **Dado** que necesito programar una actividad futura, **cuando** ingreso la información requerida (fecha futura, tipo, cultivo y descripción), **entonces** el sistema debe permitir su registro, crear la actividad en estado `pendiente` y mostrar confirmación. | 🟡 PARCIAL *(Se creaba el registro pero sin disparar el motor de evaluación de alertas)* | 🟢 **CUMPLE** *(Se crea la actividad, estado `pendiente`, validaciones completas y feedback exitoso)* |
| **2** | Recordatorio automático de actividad próxima | **Dado** que tengo una actividad programada para el día siguiente, **cuando** se acerca la fecha (día anterior de la actividad programada), **entonces** el sistema debe generar y enviar una notificación recordatoria al trabajador asignado y administradores. | 🔴 NO CUMPLE *(No existía servicio ni proceso de recordatorios por proximidad temporal)* | 🟢 **CUMPLE** *(Servicio de recordatorios detecta tareas para el día siguiente o para el día actual y genera notificaciones automáticas prioritarias)* |
| **3** | Alerta de cultivo próximo a cosechar | **Dado** que un cultivo está próximo a su fecha estimada de cosecha, **cuando** el sistema evalúa la información y detecta un cultivo cercano a cosecha (< 15 días), **entonces** debe generar y mostrar una alerta de cosecha próxima. | 🔴 NO CUMPLE *(No existía evaluación agronómica automática de proximidad de cosecha)* | 🟢 **CUMPLE** *(El motor agronómico analiza cultivos activos y genera alertas de tipo `cosecha` a los administradores)* |
| **4** | Alerta automática de fertilización vencida | **Dado** que un cultivo no registra fertilización en el tiempo definido (> 30 días o frecuencia sugerida), **cuando** el sistema revisa el historial, **entonces** debe generar y mostrar una alerta para programar la fertilización. | 🔴 NO CUMPLE *(Inexistente; no se auditaba la periodicidad de fertilización de los cultivos)* | 🟢 **CUMPLE** *(Inspección periódica de la última fertilización o fecha de siembra; genera alerta automática para programar fertilización inmediata)* |
| **5** | Marcar actividad programada como completada | **Dado** que una actividad programada ya fue realizada, **cuando** la marco como completada (cambio el estado a `completada`), **entonces** el sistema debe registrar la actividad como realizada, asignando `fecha_ejecucion` y cancelando recordatorios pendientes. | 🟡 PARCIAL *(Solo se podía hacer vía formulario general de edición sin cancelar recordatorios asociados)* | 🟢 **CUMPLE** *(Endpoint ágil `POST /actividades/{id}/completar` y vía formulario `update()`, actualizando fecha, ejecutor y cancelando notificaciones obsoletas)* |
| **6** | Modificar fecha de actividad programada | **Dado** que necesito reprogramar una actividad, **cuando** cambio su fecha antes de ejecutarla, **entonces** el sistema debe guardar la nueva fecha y actualizar el recordatorio (eliminando recordatorios obsoletos). | 🔴 NO CUMPLE *(No se sincronizaban las notificaciones; si se cambiaba la fecha, persistían recordatorios de la fecha anterior)* | 🟢 **CUMPLE** *(Al actualizar la fecha programada se purgan recordatorios obsoletos y se recalcula el recordatorio para la nueva fecha)* |
| **7** | Cancelar actividad programada | **Dado** que decido no realizar una actividad programada, **cuando** la cancelo desde el calendario o listado (o la elimino), **entonces** el sistema debe eliminar la programación/marcarla cancelada y cancelar el recordatorio. | 🔴 NO CUMPLE *(Eliminar o cancelar dejaba registros huérfanos de notificaciones activas)* | 🟢 **CUMPLE** *(Tanto el endpoint `POST /actividades/{id}/cancelar` como `DELETE /actividades/{id}` eliminan y anulan inmediatamente todos los recordatorios vinculados)* |
| **8** | Validación de fecha futura para programación | **Dado** que estoy programando una actividad, **cuando** ingreso una fecha pasada, **entonces** el sistema debe validar esa restricción, mostrar un error y no permitir continuar. | 🔴 NO CUMPLE *(Se permitía seleccionar cualquier fecha del pasado al momento de programar actividades nuevas)* | 🟢 **CUMPLE** *(Regla estricta `after_or_equal:today` en validación del backend y atributo HTML `min="YYYY-MM-DD"` en frontend con mensaje amigable)* |

---

## 2. Arquitectura de la Solución e Implementación Técnica

### 2.1 Motor de Recordatorios y Alertas Agronómicas ([RecordatorioService.php](file:///c:/laragon/www/Sigvos_laravel/app/Services/RecordatorioService.php))
Se implementó un servicio especializado desacoplado en `App\Services\RecordatorioService` que encapsula la lógica agronómica y de notificaciones:
1. **`evaluarActividadesProximas()`:**
   - Detecta actividades en estado `pendiente` con `fecha_programada` entre hoy y mañana.
   - Crea notificación de recordatorio para el trabajador asignado y para los administradores.
   - Evita duplicación verificando registros previos del mismo día para la misma actividad.
2. **`evaluarCosechasProximas()`:**
   - Detecta cultivos activos (`sembrado`, `creciendo`) con `fecha_cosecha_estimada` dentro de los próximos 15 días.
   - Notifica a los administradores con cálculo exacto de días restantes y enlace a la ficha del cultivo.
3. **`evaluarFertilizacionesVencidas()`:**
   - Consulta el catálogo `tipos_actividad` con código `FERTILIZACION` (frecuencia estándar de 30 días).
   - Analiza la última actividad de fertilización completada para cada cultivo activo; en su ausencia, evalúa los días transcurridos desde la fecha de siembra.
   - Si no hay fertilización programada a futuro y han transcurrido más de 30 días, genera automáticamente una alerta para programar fertilización inmediata.
4. **`cancelarRecordatoriosActividad($idActividad)`:**
   - Purga las notificaciones de tipo recordatorio asociadas a una actividad cuando esta es completada, cancelada, reprogramada o eliminada.

### 2.2 Controlador de Actividades ([ActividadController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/ActividadController.php))
- **Validación de fecha futura (Criterio 8):** Regla `'fecha_programada' => ['required', 'date', 'after_or_equal:today']` con mensaje personalizado: *"La fecha de programación no puede ser una fecha pasada. Debe ser hoy o una fecha futura."*.
- **Acción Rápida de Completar (Criterio 5):** Método `completar(Request $request, Actividad $actividade)` que registra la fecha de ejecución, usuario ejecutor y cancela recordatorios.
- **Acción Rápida de Cancelar (Criterio 7):** Método `cancelar(Request $request, Actividad $actividade)` que pasa la actividad a estado `cancelada` y remueve los recordatorios automáticos.
- **Eliminación limpia en `destroy()`:** Cancela las notificaciones asociadas antes de eliminar el registro físico de la base de datos.

### 2.3 Rutas del Sistema ([routes/web.php](file:///c:/laragon/www/Sigvos_laravel/routes/web.php))
Se registraron los endpoints específicos para completar y cancelar:
```php
Route::post('actividades/{actividade}/completar', [\App\Http\Controllers\ActividadController::class, 'completar'])->name('actividades.completar');
Route::post('actividades/{actividade}/cancelar', [\App\Http\Controllers\ActividadController::class, 'cancelar'])->name('actividades.cancelar');
Route::resource('actividades', \App\Http\Controllers\ActividadController::class);
```

---

## 3. Evidencia de Verificación y Pruebas Automatizadas

Se construyó la suite exhaustiva de pruebas funcionales en [tests/Feature/ProgramacionActividadesTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/ProgramacionActividadesTest.php), con cobertura del 100% para cada uno de los 8 criterios de aceptación.

### Resumen de Ejecución de Pruebas Unitarias y de Integración:
```bash
php artisan test tests/Feature/ProgramacionActividadesTest.php
```

```
   PASS  Tests\Feature\ProgramacionActividadesTest
  ✓ criterio 1: programacion de actividad futura exitosa                 0.18s  
  ✓ criterio 2: recordatorio automatico de actividad proxima             0.08s  
  ✓ criterio 3: alerta de cultivo proximo a cosechar                     0.06s  
  ✓ criterio 4: alerta automatica de fertilizacion vencida               0.07s  
  ✓ criterio 5: marcar actividad programada como completada              0.08s  
  ✓ criterio 6: modificar fecha de actividad programada                  0.11s  
  ✓ criterio 7: cancelar actividad programada                            0.09s  
  ✓ criterio 8: validacion de fecha futura para programacion             0.06s  

  Tests:    8 passed (34 assertions)
  Duration: 0.73s
```

### Prueba de Regresión Global:
Se ejecutó la suite de auditoría completa acumulada (HU DP-01 hasta HU DP-10 y APIs):
```bash
php artisan test tests/Feature/Api/ tests/Feature/AsignacionLoteTest.php tests/Feature/BusquedaGlobalTest.php tests/Feature/CultivoCreateTest.php tests/Feature/DashboardTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/ProgramacionActividadesTest.php tests/Feature/TrabajadorConsultaOperativaTest.php
```

```
  Tests:    65 passed (391 assertions)
  Duration: 3.44s
```

---

## 4. Veredicto Final de Aseguramiento de Calidad

> [!IMPORTANT]
> **CERTIFICACIÓN QA LEAD:**
> La Historia de Usuario **DP-10: Programar actividades futuras con recordatorios automáticos** cumple de manera rigurosa, robusta y verificable con el 100% de los criterios de aceptación especificados en el documento del proyecto. No se detectan regresiones en el sistema.

* **Puntuación Otorgada:** **100 / 100 (APROBADO AL 100% - CERTIFICADO)**
* **Estado:** ✅ **Completado y Verificado**
