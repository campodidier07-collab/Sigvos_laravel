# Auditoría de Calidad de Software: Historia de Usuario DP-9

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-9` |
| **Rol** | Administrador |
| **Funcionalidad** | Buscar rápidamente cultivos, actividades o lotes específicos |
| **Propósito de Negocio** | Para localizar información sin navegar por múltiples pantallas |
| **Módulo** | Módulo de Búsqueda Global y Filtros Avanzados (Omnibox y Explorador Centralizado) |
| **Calificación Inicial** | 🔴 **25 / 100** (NO CUMPLE / INCOMPLETO - Buscador del navbar era estático, sin controlador de búsqueda global, sin filtros combinados ni historial) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Búsqueda rápida por nombre de cultivo | **Dado** que necesito encontrar información de un cultivo específico, **cuando** realizo la búsqueda por nombre (o código), **entonces** el sistema debe mostrar los resultados relacionados. | 🔴 NO CUMPLE *(Input en navbar era estático sin endpoint ni búsqueda real)* | 🟢 **CUMPLE** *(Controlador [BusquedaController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/BusquedaController.php) busca por código, variedad, tipo de cultivo y lote)* |
| **2** | Búsqueda por rango de fechas | **Dado** que necesito revisar actividades de un período específico, **cuando** aplico un rango de fechas (`fecha_desde` y `fecha_hasta`), **entonces** el sistema debe filtrar la información correspondiente. | 🔴 NO CUMPLE *(Sin filtros por rango de fechas transversal)* | 🟢 **CUMPLE** *(Filtrado estricto por rango temporal sobre actividades y fechas de siembra)* |
| **3** | Filtro por lote específico | **Dado** que necesito consultar la información de un lote en particular, **cuando** aplico el filtro por lote, **entonces** el sistema debe mostrar solo lo relacionado con ese lote. | 🔴 NO CUMPLE *(Sin filtro transversal por lote)* | 🟢 **CUMPLE** *(Filtro `id_lote` segmenta cultivos, actividades y métricas del lote seleccionado)* |
| **4** | Filtro combinado múltiple | **Dado** que necesito encontrar información más específica, **cuando** aplico varios filtros al mismo tiempo (texto + lote + estado + fechas), **entonces** el sistema debe mostrar solo los resultados coincidentes. | 🔴 NO CUMPLE *(Inexistente)* | 🟢 **CUMPLE** *(Intersección lógica de múltiples condiciones en Eloquent retornando solo registros coincidentes)* |
| **5** | Filtro por estado de cultivo | **Dado** que necesito consultar cultivos según su estado, **cuando** aplico el filtro correspondiente (`sembrado`, `creciendo`, `cosechado`, `perdido`), **entonces** el sistema debe mostrar los cultivos que coincidan. | 🔴 NO CUMPLE *(Solo existía filtro básico dentro de cultivos.index)* | 🟢 **CUMPLE** *(Filtro directo por estado de cultivo con badges semánticos)* |
| **6** | Búsqueda sin resultados | **Dado** que realizo una búsqueda sin coincidencias, **cuando** ingreso un término no registrado, **entonces** el sistema debe informarlo claramente. | 🔴 NO CUMPLE *(Inexistente)* | 🟢 **CUMPLE** *(Panel interactivo con icono, mensaje explicativo y botón para restablecer filtros)* |
| **7** | Historial de búsquedas recientes | **Dado** que realizo búsquedas con frecuencia, **cuando** ingreso al buscador, **entonces** el sistema debe mostrar el historial de búsquedas recientes. | 🔴 NO CUMPLE *(Sin persistencia de búsquedas previas)* | 🟢 **CUMPLE** *(Historial reactivo en `localStorage`, pills clickeables para relanzar búsqueda y botón de limpieza)* |

---

## 2. Análisis Técnico Detallado por Criterio

### Criterio 1: Búsqueda rápida por nombre de cultivo (CUMPLE)

* **Brecha Inicial:** La barra de navegación superior en [resources/views/layouts/adminlte.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/layouts/adminlte.blade.php) contenía un input `<input class="top-search-input" placeholder="Buscar...">` puramente cosmético que no enviaba solicitudes ni interactuaba con el backend.
* **Solución Implementada:**
  1. Se creó el controlador dedicado [app/Http/Controllers/BusquedaController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/BusquedaController.php).
  2. El método `index()` y el endpoint liviano `rapida()` realizan búsquedas por coincidencia parcial (`LIKE %q%`) en:
     - Código del cultivo (`cultivos.codigo`).
     - Nombre de la variedad (`variedades.nombre`).
     - Nombre del tipo de cultivo (`tipos_cultivo.nombre`).
     - Identificador y nombre del lote asociado.
  3. En la barra superior de `adminlte.blade.php`, el input fue encapsulado en un formulario funcional apuntando a `route('busqueda.index')`, permitiendo al administrador buscar desde cualquier pantalla del sistema.

---

### Criterio 2: Búsqueda por rango de fechas (CUMPLE)

* **Implementación:**
  1. El formulario en [resources/views/busqueda/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/busqueda/index.blade.php#L68-L82) proporciona selectores HTML5 de fecha: `fecha_desde` y `fecha_hasta`.
  2. En [BusquedaController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/BusquedaController.php#L60-L98):
     - Para **Actividades:** filtra por `fecha_programada >= $fechaDesde` y `fecha_programada <= $fechaHasta`.
     - Para **Cultivos:** filtra por `fecha_siembra >= $fechaDesde` y `fecha_siembra <= $fechaHasta`.
  3. Los registros fuera del rango especificado son omitidos del resultado.

---

### Criterio 3: Filtro por lote específico (CUMPLE)

* **Implementación:**
  1. El selector `<select name="id_lote">` despliega dinámicamente los lotes habilitados en la explotación agrícola.
  2. Al seleccionar un lote:
     - La sección de Cultivos filtra estrictamente por `id_lote = $idLote`.
     - La sección de Actividades filtra por cultivos de ese lote mediante `whereHas('cultivo', fn($c) => $c->where('id_lote', $idLote))`.
     - La sección de Lotes muestra la ficha de identificación del lote con su superficie y estado actual.

---

### Criterio 4: Filtro combinado múltiple (CUMPLE)

* **Comportamiento Verificado:**
  1. El Administrador puede ingresar simultáneamente:
     - Texto `q`: ej. `"CUL-"`
     - Lote: ej. `Lote San José`
     - Estado: ej. `sembrado`
     - Rango de fechas: ej. `2026-10-20` a `2026-10-30`
  2. El controlador encadena los filtros con condiciones `AND` sobre la consulta Eloquent.
  3. La interfaz despliega badges informativos con los filtros activos (`Filtros activos: Texto, Lote ID, Estado, Rango`) y muestra única y exclusivamente los registros que satisfacen la totalidad de los parámetros combinados.

---

### Criterio 5: Filtro por estado de cultivo (CUMPLE)

* **Implementación:**
  1. Selector con los estados agronómicos normalizados: `sembrado`, `creciendo`, `cosechado`, `perdido`.
  2. El query ejecuta `where('estado', $estadoCultivo)`, aislando los cultivos en el estado seleccionado y desplegando badges con codificación semántica de colores (ej. azul para `sembrado`, verde para `cosechado`, amarillo para `creciendo`).

---

### Criterio 6: Búsqueda sin resultados (CUMPLE)

* **Implementación:**
  1. Cuando el operador ejecuta una búsqueda con un término o combinación de filtros que no produce coincidencias (`$totalResultados === 0`), el sistema renderiza un panel centrado con icono de advertencia suave:
     > **No se encontraron resultados**
     > No se encontraron registros que coincidan con los criterios de búsqueda seleccionados. Intenta modificar los términos, verificar las fechas o limpiar algunos filtros.
  2. Proporciona un botón directo de acción rápida: `Restablecer todos los filtros`.

---

### Criterio 7: Historial de búsquedas recientes (CUMPLE)

* **Implementación:**
  1. En [resources/views/busqueda/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/busqueda/index.blade.php#L225-L270), el cliente JavaScript gestiona la clave `sigvos_busquedas_recientes` en `localStorage`.
  2. Al enviar cualquier búsqueda, el término ingresado se normaliza y se almacena en la pila de historial (conservando hasta 8 búsquedas recientes sin duplicados).
  3. Al abrir la pantalla del buscador, la sección **Búsquedas Recientes** renderiza pills clickeables (`search-pill-history`). Un clic sobre cualquier término rellena automáticamente el campo de texto y ejecuta la búsqueda.
  4. Incluye botón para limpiar el historial con un solo clic (`limpiarHistorialBusquedas()`).

---

## 3. Matriz de Archivos e Impacto Técnico

| Archivo Modificado / Creado | Tipo | Descripción de la Modificación |
| :--- | :--- | :--- |
| [app/Http/Controllers/BusquedaController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/BusquedaController.php) | Creación | Controlador de Búsqueda Global con métodos `index()` (vista y filtros) y `rapida()` (API JSON). |
| [routes/web.php](file:///c:/laragon/www/Sigvos_laravel/routes/web.php) | Modificación | Rutas `busqueda.index` y `busqueda.rapida` bajo middleware `role:admin`. |
| [resources/views/busqueda/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/busqueda/index.blade.php) | Creación | Vista integral con formulario de filtros combinados, historial en `localStorage`, resultados categorizados y estado vacío. |
| [resources/views/layouts/adminlte.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/layouts/adminlte.blade.php) | Modificación | Formulario de búsqueda superior en navbar y enlace "Buscador Global" en sidebar administrativo. |
| [tests/Feature/BusquedaGlobalTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/BusquedaGlobalTest.php) | Creación | Suite automatizada de 7 pruebas unitarias y de integración para cada criterio de aceptación. |

---

## 4. Evidencia de Pruebas Automatizadas

Se ejecutó la suite de pruebas automatizadas en [tests/Feature/BusquedaGlobalTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/BusquedaGlobalTest.php):

```bash
php artisan test tests/Feature/BusquedaGlobalTest.php
```

### Salida de Ejecución:

```
PASS  Tests\Feature\BusquedaGlobalTest
✓ criterio 1 busqueda rapida por nombre de cultivo
✓ criterio 2 busqueda por rango de fechas
✓ criterio 3 filtro por lote especifico
✓ criterio 4 filtro combinado multiple
✓ criterio 5 filtro por estado de cultivo
✓ criterio 6 busqueda sin resultados
✓ criterio 7 historial de busquedas recientes

Tests:    7 passed (27 assertions)
Duration: 0.79s
```

### Ejecución de Suite Acumulada de Auditorías (DP-1 a DP-9):

```bash
php artisan test tests/Feature/BusquedaGlobalTest.php tests/Feature/TrabajadorConsultaOperativaTest.php tests/Feature/AsignacionLoteTest.php tests/Feature/CultivoCreateTest.php tests/Feature/LoteEditTest.php tests/Feature/LoteTest.php tests/Feature/DashboardTest.php tests/Feature/Auth/WorkerAccessControlTest.php tests/Feature/Auth/AuthenticationTest.php
```

```
Tests:    51 passed (231 assertions)
Duration: 2.56s
```

---

## 5. Dictamen del QA Lead

La Historia de Usuario **DP-9: Buscar rápidamente cultivos, actividades o lotes específicos** ha sido auditada, implementada y validada en su totalidad. Se dotó al sistema de un mecanismo de búsqueda centralizado y reactivo que elimina la necesidad de navegar entre pantallas independientes, soportando filtros simples, combinados, rangos de fechas, estados y persistencia de historial de búsquedas recientes.

**Calificación Final Otorgada:** 🟢 **100 / 100 — CERTIFICADO Y LISTO PARA PRODUCCIÓN**.
