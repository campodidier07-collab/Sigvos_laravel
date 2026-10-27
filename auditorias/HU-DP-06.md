# Auditoría de Calidad de Software: Historia de Usuario DP-6

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-6` |
| **Rol** | Administrador |
| **Funcionalidad** | Registrar cultivos especificando tipo, variedad, fecha de siembra y lote |
| **Propósito de Negocio** | Para llevar un control detallado de las siembras y calcular automáticamente la fecha de cosecha |
| **Módulo** | Módulo Agrícola / Gestión de Cultivos y Siembras |
| **Calificación Inicial** | 🟡 **50 / 100** (CUMPLE PARCIALMENTE) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Registro de cultivo con asignación de lote exitosa | **Dado** que tengo lotes disponibles, **cuando** registro un cultivo indicando tipo, variedad, fecha de siembra y selecciono un lote, **entonces** el sistema crea el cultivo y lo asigna al lote seleccionado. | 🟢 CUMPLE | 🟢 **CUMPLE** |
| **2** | Cálculo automático de fecha de cosecha para maíz | **Dado** que registro un cultivo de maíz, **cuando** especifico la fecha de siembra para maíz, **entonces** el sistema calcula automáticamente la fecha estimada de cosecha. | 🔴 NO CUMPLE *(Exigía ingreso manual de fecha estimada sin cálculo automático)* | 🟢 **CUMPLE** *(Cálculo dinámico en JS en tiempo real + backend fallback automático)* |
| **3** | Cálculo automático de fecha de cosecha para café arábica | **Dado** que registro un cultivo de café variedad arábica, **cuando** especifico la fecha de siembra para café arábica, **entonces** el sistema calcula automáticamente la fecha estimada de cosecha. | 🔴 NO CUMPLE *(Exigía ingreso manual de fecha estimada sin cálculo automático)* | 🟢 **CUMPLE** *(Cálculo dinámico en JS en tiempo real + backend fallback automático)* |
| **4** | Validación de lote disponible | **Dado** que intento registrar un cultivo, **cuando** selecciono un lote no disponible (en estado ocupado o en descanso), **entonces** el sistema muestra un error y no permite continuar con el registro. | 🟡 PARCIAL *(Solo verificaba activo_en_lote, no validaba estado ocupado ni en_descanso)* | 🟢 **CUMPLE** *(Filtro en create + validación estricta de estado en store y API)* |

---

## 2. Análisis Técnico Detallado por Criterio

### ✅ Criterio 1: Registro de cultivo con asignación de lote exitosa (CUMPLE)
* **Comportamiento Verificado:** En [app/Http/Controllers/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php#L87-L140), el método `store()` recibe la información del cultivo (`id_lote`, `id_variedad`, `fecha_siembra`, `fecha_cosecha_estimada`, `observaciones`), persiste la siembra mediante `Cultivo::create($datos)`, vincula el identificador único del lote activo (`$datos['activo_en_lote'] = $lote->id`) y actualiza inmediatamente el estado del lote a `ocupado` (`$lote->update(['estado' => 'ocupado'])`).
* **Notificación y Trazabilidad:** Si quien registra es un Administrador, el sistema notifica de forma reactiva a todos los trabajadores asignados a dicho lote.

---

### ✅ Criterio 2: Cálculo automático de fecha de cosecha para maíz (CUMPLE)
* **Brecha Inicial:** La vista de registro requería que el usuario ingresara manualmente la fecha de cosecha estimada (`fecha_cosecha_estimada`), y el controlador la exigía como campo obligatorio (`'required'`), careciendo de cualquier lógica de proyección agronómica basada en la variedad seleccionada.
* **Solución Implementada:**
  1. **Catálogo de Maíz:** En el catálogo ([database/seeders/CatalogSeeder.php](file:///c:/laragon/www/Sigvos_laravel/database/seeders/CatalogSeeder.php#L29-L32)), las variedades de Maíz cuentan con sus ciclos agronómicos estandarizados (ej: Maíz Amarillo con ciclo promedio de **115 días**, mínimo 100 y máximo 130).
  2. **Cálculo en Tiempo Real (Frontend):** En [resources/views/cultivos/create.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/cultivos/create.blade.php#L244-L300), se embebió en los `<option>` los atributos `data-dias`, `data-min` y `data-max`. Al seleccionar Maíz (o variar la fecha de siembra), JavaScript calcula al instante `fecha_siembra + 115 días`, completa el campo `fecha_cosecha_estimada`, activa la insignia `<span id="badge-auto-calc">Calculada autom.</span>` y muestra el detalle: *"Cálculo automático para Maíz (Maíz Amarillo): +115 días"*.
  3. **Garantía en Backend:** En [app/Http/Controllers/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php#L118-L124) y en la API ([app/Http/Controllers/Api/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/Api/CultivoController.php#L97-L103)), si no se provee la fecha estimada, el backend toma `$variedad->dias_cosecha_promedio` y suma los días automáticamente sobre `fecha_siembra`.

---

### ✅ Criterio 3: Cálculo automático de fecha de cosecha para café arábica (CUMPLE)
* **Comportamiento Verificado:**
  1. **Catálogo de Café Arábica:** La variedad Arábica posee un ciclo promedio de **270 días** (mínimo 240, máximo 300 días).
  2. **Frontend:** Al seleccionar Café Arábica en el formulario, JavaScript detecta `data-dias="270"` y proyecta la fecha sumando exactamente 270 días naturales a la fecha de siembra seleccionada, informando el rango estimado al operador.
  3. **Backend:** El controlador resuelve la estimación automática tanto por interfaz web como por peticiones REST API.

---

### ✅ Criterio 4: Validación de lote disponible (CUMPLE)
* **Brecha Inicial:** La validación previa únicamente verificaba la restricción `activo_en_lote`. Lotes con estado `'ocupado'` o en descanso (`'en_descanso'`) no eran validados explícitamente y podían admitir siembras inconsistentes.
* **Solución Implementada:**
  1. **Filtrado en Formulario:** En [CultivoController.php@create](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php#L56-L77), la consulta de lotes disponibles exige de forma estricta:
     ```php
     Lote::where('activo', true)
         ->where('estado', 'disponible')
         ->whereDoesntHave('cultivos', fn($q) => $q->whereNotNull('activo_en_lote'))
     ```
     Lotes ocupados o en descanso son excluidos de la lista desplegable.
  2. **Validación Defensiva en Backend:** En `store()`, si se envía un lote con estado diferente a `disponible` o que posea un cultivo activo, la petición se detiene de inmediato devolviendo error de validación en el campo `id_lote`:
     ```php
     if ($lote->estado !== 'disponible' || $lote->cultivoActivo()->exists()) {
         $estadoTxt = match ($lote->estado) {
             'ocupado'       => 'ocupado',
             'en_descanso'   => 'en descanso',
             'mantenimiento' => 'en mantenimiento',
             default         => $lote->estado,
         };
         return back()->withInput()->withErrors([
             'id_lote' => "El lote seleccionado no está disponible (se encuentra {$estadoTxt}). Solo se pueden registrar siembras en lotes disponibles.",
         ]);
     }
     ```

---

## 3. Matriz de Cambios Aplicados en el Código Fuente

| Archivo Modificado | Descripción del Cambio |
| :--- | :--- |
| [app/Http/Controllers/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CultivoController.php) | **Controlador Web:** Filtrado de lotes estrictamente disponibles en `create()`, validación de rechazo para lotes en estado `ocupado` o `en_descanso`, y cálculo automático en backend de `fecha_cosecha_estimada` basado en `dias_cosecha_promedio`. |
| [app/Http/Controllers/Api/CultivoController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/Api/CultivoController.php) | **Controlador API:** Rechazo con código HTTP 422 si el lote no está disponible y cálculo automático de fecha estimada para integraciones REST. |
| [resources/views/cultivos/create.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/cultivos/create.blade.php) | **Vista Blade:** Agrupación semántica por tipo de cultivo, metadata agronómica en `<option>` (`data-dias`, `data-min`, `data-max`) y script interactivo que proyecta la fecha estimada de cosecha en tiempo real con feedback visual. |
| [tests/Feature/CultivoCreateTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/CultivoCreateTest.php) | **Suite de Pruebas:** 4 pruebas automatizadas con 26 aserciones que certifican los 4 criterios de aceptación de DP-6. |

---

## 4. Evidencia de Pruebas Automatizadas

Se ejecutó la suite de pruebas automatizadas creada específicamente para la Historia de Usuario **DP-6**:

```bash
php artisan test tests/Feature/CultivoCreateTest.php
```

### Resultados de Ejecución:
```text
   PASS  Tests\Feature\CultivoCreateTest
  ✓ criterio 1: registro de cultivo con asignacion de lote exitosa       0.19s  
  ✓ criterio 2: calculo automatico de fecha de cosecha para maiz         0.14s  
  ✓ criterio 3: calculo automatico de fecha de cosecha para cafe arabica 0.13s  
  ✓ criterio 4: validacion de lote no disponible ocupado o en descanso   0.15s  

  Tests:    4 passed (26 assertions)
  Duration: 1.08s
```

Verificación adicional de endpoints API:
```bash
php artisan test tests/Feature/Api/LotesCultivosApiTest.php
  Tests:    3 passed (24 assertions)
  Duration: 0.91s
```

---

## 5. Dictamen de Calidad

> ### 🟢 DICTAMEN FINAL: APROBADO AL 100% (100 / 100)
> **La Historia de Usuario DP-6 cumple fielmente con la totalidad de los criterios de aceptación.** Las proyecciones de fecha estimada de cosecha para maíz (+115 días) y café arábica (+270 días) operan en tiempo real en la interfaz de usuario y como salvaguarda automática en backend. La asignación del lote y la validación de no-disponibilidad (lotes ocupados o en descanso) garantizan la integridad del ciclo agronómico.
