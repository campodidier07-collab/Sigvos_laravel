# Auditoría de Calidad de Software: Historia de Usuario DP-5

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-5` |
| **Rol** | Administrador |
| **Funcionalidad** | Modificar la información básica de los lotes registrados |
| **Propósito de Negocio** | Para actualizar datos cuando cambien las características del terreno |
| **Módulo** | Módulo Agrícola / Gestión de Lotes y Parcelas |
| **Calificación Inicial** | 🟡 **40 / 100** (CUMPLE PARCIALMENTE) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Modificación exitosa de información básica | **Dado** que necesito actualizar la información de un lote, **cuando** modifico sus datos básicos (nombre, ubicación o área), **entonces** el sistema guarda la información actualizada y muestra confirmación. | 🟢 CUMPLE | 🟢 **CUMPLE** |
| **2** | Restricción de modificación de identificador | **Dado** que estoy editando un lote existente, **cuando** intento cambiar su identificador, **entonces** el sistema bloquea ese campo y muestra que no puede ser editado. | 🔴 NO CUMPLE *(Editable en form y backend)* | 🟢 **CUMPLE** *(Bloqueado readonly + backend)* |
| **3** | Modificación de ubicación | **Dado** que necesito actualizar la ubicación de un lote, **cuando** realizo el cambio, **entonces** el sistema actualiza la ubicación y refleja el cambio guardado. | 🟢 CUMPLE | 🟢 **CUMPLE** |
| **4** | Validación de área mínima al modificar | **Dado** que estoy actualizando el área del lote, **cuando** ingreso un valor menor al permitido (`< 0.01` ha o `<= 0`), **entonces** el sistema muestra un error y no permite guardar. | 🟢 CUMPLE | 🟢 **CUMPLE** |
| **5** | Restricción de modificación con cultivo activo | **Dado** que el lote tiene un cultivo activo, **cuando** intento modificar información sensible (ej: área del terreno), **entonces** el sistema muestra una advertencia y limita la modificación. | 🔴 NO CUMPLE *(Permitía alterar área con siembra activa)* | 🟢 **CUMPLE** *(Banner advertencia + área bloqueada en UI y backend)* |
| **6** | Historial de modificaciones del lote | **Dado** que el lote ha sido modificado previamente, **cuando** consulto su historial, **entonces** el sistema muestra el registro de modificaciones realizadas. | 🔴 NO CUMPLE *(No se guardaba auditoría ni se mostraba historial de cambios de datos)* | 🟢 **CUMPLE** *(Tabla auditoria conectada + Tab Historial de Modificaciones en show)* |
| **7** | Cancelación de edición sin guardar | **Dado** que estoy editando un lote, **cuando** hago clic en "Cancelar", **entonces** el sistema no guarda los cambios y restaura la información anterior. | 🟡 PARCIAL *(Botón retornaba a index sin link a show)* | 🟢 **CUMPLE** *(Botones de cancelar dirigen a show sin guardar cambios)* |

---

## 2. Análisis Técnico Detallado por Criterio

### ✅ Criterio 1: Modificación exitosa de información básica (CUMPLE)
* **Comportamiento Verificado:** Al editar los campos permitidos de un lote (nombre, ubicación, área disponible), el sistema procesa la petición en [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L145-L215), actualiza la base de datos y redirige al usuario al detalle del lote con el mensaje flash de confirmación: `"Lote actualizado exitosamente."`.

---

### ✅ Criterio 2: Restricción de modificación de identificador (CUMPLE)
* **Brecha Inicial:** En el formulario [resources/views/lotes/edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/edit.blade.php), el campo `identificador` era un input de texto editable y el backend validaba unicidad sin bloquear modificaciones.
* **Solución Implementada:**
  1. En el frontend ([edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/edit.blade.php#L151-L167)), el campo se transformó a `readonly` con estilo deshabilitado (`background-color: #f1f5f9; cursor: not-allowed;`), acompañado de una etiqueta visual distintiva: `<span class="badge badge-secondary"><i class="fas fa-lock"></i> Bloqueado</span>` y el texto explicativo: *"El identificador único no puede ser modificado una vez creado el lote"*.
  2. En el backend ([LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L151-L157)), se agregó una validación defensiva:
     ```php
     if ($request->filled('identificador') && strtoupper(trim($request->identificador)) !== strtoupper($lote->identificador)) {
         throw ValidationException::withMessages([
             'identificador' => 'El identificador del lote está bloqueado y no puede ser modificado.',
         ]);
     }
     $datos['identificador'] = $lote->identificador;
     ```

---

### ✅ Criterio 3: Modificación de ubicación (CUMPLE)
* **Comportamiento Verificado:** Se permite actualizar la descripción geográfica o parcelaria del lote. El cambio se persiste en la columna `ubicacion` de la tabla `lotes` y se refleja inmediatamente tanto en la vista individual ([resources/views/lotes/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php#L255-L258)) como en la tabla de listado general.

---

### ✅ Criterio 4: Validación de área mínima al modificar (CUMPLE)
* **Reglas Validadas:**
  - En backend: `'area_ha' => 'required|numeric|min:0.01'`.
  - En frontend: `<input type="number" step="0.01" min="0.01">`.
  - Intentos de ingresar valores inferiores al mínimo permitido (cero, números negativos o menores a 0.01) son interceptados por Laravel Validation retornando error en `$errors->get('area_ha')` sin alterar los datos del lote.

---

### ✅ Criterio 5: Restricción de modificación con cultivo activo (CUMPLE)
* **Brecha Inicial:** Un administrador podía modificar arbitrariamente el área de un lote incluso si contenía un cultivo activo en crecimiento, generando graves inconsistencias agronómicas (densidad de siembra errónea, cálculos distorsionados de rendimiento).
* **Solución Implementada:**
  1. **Advertencia Visual:** En [resources/views/lotes/edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/edit.blade.php#L139-L150), si `$lote->cultivoActivo` existe, se renderiza una tarjeta de advertencia destacada en ámbar informando que el lote tiene una siembra en curso (`codigo`) y que las características críticas están protegidas.
  2. **Bloqueo de Campo:** El input `area_ha` se bloquea con `readonly` y muestra la etiqueta `<span class="badge badge-warning"><i class="fas fa-lock"></i> Protegido</span>`.
  3. **Protección en Controlador:** En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L159-L164), si el lote tiene un cultivo activo y la petición intenta variar el área (`area_ha`), se rechaza con excepción de validación:
     ```php
     if ($lote->cultivoActivo()->exists() && $request->filled('area_ha') && abs((float)$request->area_ha - (float)$lote->area_ha) > 0.0001) {
         throw ValidationException::withMessages([
             'area_ha' => 'No se puede modificar el área de un lote que tiene un cultivo activo en desarrollo.',
         ]);
     }
     ```

---

### ✅ Criterio 6: Historial de modificaciones del lote (CUMPLE)
* **Brecha Inicial:** La base de datos contaba con la tabla `auditoria` en la migración [2025_01_01_600000_create_audit_logs_table.php](file:///c:/laragon/www/Sigvos_laravel/database/migrations/2025_01_01_600000_create_audit_logs_table.php), pero no existía modelo Eloquent, no se guardaban registros en operaciones `update`, y la vista del lote solo mostraba cultivos anteriores, sin trazabilidad de cambios en los datos del terreno.
* **Solución Implementada:**
  1. Se creó el modelo Eloquent [app/Models/Auditoria.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Auditoria.php) mapeado a la tabla `auditoria` con casts para `datos_antes` y `datos_despues` (JSON) y relación `usuario()`.
  2. En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L190-L215), al ejecutar `update`, se detectan los atributos que cambiaron mediante `$lote->getChanges()` y se registra la auditoría con los valores anteriores y nuevos, el usuario responsable (`auth()->id()`), IP de origen y marca temporal.
  3. En [resources/views/lotes/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php#L306-L313 y #L438-L520), se añadió la pestaña **"Historial de Modificaciones"** con badge de conteo en tiempo real. Esta pestaña muestra una tabla moderna con:
     - Fecha y hora exacta del cambio.
     - Responsable con avatar inicial y nombre.
     - Acción efectuada (`UPDATE`).
     - Detalle visual de cada campo modificado, mostrando el valor anterior tachado en rojo y el nuevo valor resaltado en verde.

---

### ✅ Criterio 7: Cancelación de edición sin guardar (CUMPLE)
* **Comportamiento Verificado:** En [resources/views/lotes/edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/edit.blade.php#L137 y #L268), tanto el botón "Cancelar" inferior como el botón "X" superior dirigen explícitamente a la ruta del lote (`route('lotes.show', $lote)`). Ningún dato temporal se persiste y el usuario regresa a la vista de consulta con toda la información original intacta.

---

## 3. Matriz de Cambios Aplicados en el Código Fuente

| Archivo Modificado / Creado | Descripción del Cambio |
| :--- | :--- |
| [app/Models/Auditoria.php](file:///c:/laragon/www/Sigvos_laravel/app/Models/Auditoria.php) | **Nuevo Modelo:** Mapeo de la tabla `auditoria`, casteo de datos antes/después a array y relación con `Usuario`. |
| [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php) | **Controlador:** Restricción de identificador inmutable, restricción de modificación de área con cultivo activo, generación de logs de auditoría en `update()`, carga de historial de modificaciones en `show()`. |
| [resources/views/lotes/edit.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/edit.blade.php) | **Vista de Edición:** Campo identificador en `readonly` con badge de bloqueo, banner de advertencia para cultivos activos, bloqueo de campo área sensible y botones de cancelación dirigidos al detalle. |
| [resources/views/lotes/show.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php) | **Vista de Detalle:** Nueva pestaña "Historial de Modificaciones" con conteo dinámico y tabla de trazabilidad de cambios con desglose antes/después. |
| [tests/Feature/LoteEditTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/LoteEditTest.php) | **Suite de Pruebas:** 7 pruebas automatizadas que verifican de manera estricta los 7 criterios de aceptación de DP-5. |

---

## 4. Evidencia de Pruebas Automatizadas

Se ejecutó la suite de pruebas unitarias y de integración creada específicamente para **HU DP-5**:

```bash
php artisan test tests/Feature/LoteEditTest.php
```

### Resultados de Ejecución:
```text
   PASS  Tests\Feature\LoteEditTest
  ✓ criterio 1: modificacion exitosa de informacion basica               0.18s  
  ✓ criterio 2: restriccion de modificacion de identificador             0.14s  
  ✓ criterio 3: modificacion de ubicacion                                0.12s  
  ✓ criterio 4: validacion de area minima al modificar                   0.12s  
  ✓ criterio 5: restriccion de modificacion con cultivo activo           0.17s  
  ✓ criterio 6: historial de modificaciones del lote                     0.15s  
  ✓ criterio 7: cancelacion de edicion sin guardar                       0.12s  

  Tests:    7 passed (40 assertions)
  Duration: 1.34s
```

Asimismo, se verificó la no-regresión con la suite previa de registro de lotes:
```bash
php artisan test tests/Feature/LoteTest.php
  Tests:    5 passed (20 assertions)
  Duration: 1.02s
```

---

## 5. Dictamen de Calidad

> ### 🟢 DICTAMEN FINAL: APROBADO AL 100% (100 / 100)
> **La Historia de Usuario DP-5 cumple fielmente con todos los criterios de aceptación especificados en el documento de requerimientos.** Los mecanismos de seguridad agronómica (protección de área con cultivo activo e identificador inmutable) y el sistema de trazabilidad y auditoría de cambios se encuentran plenamente operativos y certificados mediante pruebas automatizadas.
