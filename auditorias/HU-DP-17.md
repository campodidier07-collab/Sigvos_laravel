# Auditoría de Calidad de Software — Historia de Usuario DP-17

**Proyecto:** Sigvos (Sistema de Información para la Gestión y Valoración de Operaciones del Sector Agrícola)  
**Evaluador:** Senior QA Lead & Principal Requirements Analyst  
**Fecha de Evaluación:** 24 de Octubre de 2026  
**Resultado de Auditoría:** **100/100 — APROBADO Y CERTIFICADO (CUMPLE AL 100%)**  

---

## 📌 Historia de Usuario DP-17

| Atributo | Detalle |
| :--- | :--- |
| **ID Historia** | `DP-17` |
| **Rol** | `Administrador` |
| **Funcionalidad** | Registrar cuando un cultivo es cosechado y liberar automáticamente el lote |
| **Propósito / Valor** | Para cerrar el ciclo productivo y preparar el lote para nuevos cultivos |
| **Estado de Cumplimiento** | **CUMPLE AL 100% (CERTIFICADO)** |
| **Puntaje Global** | **100 / 100** |

---

## 🎯 Criterios de Aceptación Identificados

1. **Criterio 1: Registro exitoso de cosecha**  
   *Dado que un cultivo está listo para cosechar, cuando registro la cosecha indicando la cantidad obtenida, entonces el sistema debe guardarla correctamente, marcar el cultivo como cosechado y liberar el lote.*
2. **Criterio 2: Lote disponible después de cosecha**  
   *Dado que se registró una cosecha en un lote, cuando consulto su estado, entonces el sistema debe reflejar su disponibilidad para un nuevo cultivo.*
3. **Criterio 3: Historial de producción por lote**  
   *Dado que un lote ha tenido cosechas registradas, cuando consulto su historial, entonces el sistema debe mostrar la información acumulada de las cosechas registradas en ese lote.*
4. **Criterio 4: Validación de cantidad mínima**  
   *Dado que estoy registrando una cosecha, cuando ingreso una cantidad no válida (menor a la permitida), entonces el sistema debe validar la información, mostrar un error y no permitir continuar.*
5. **Criterio 5: Registro de cosecha con observaciones**  
   *Dado que deseo agregar detalles sobre la cosecha, cuando registro observaciones, entonces el sistema debe guardarlas junto al registro.*
6. **Criterio 6: Comparación con estimación inicial**  
   *Dado que registro una cosecha, cuando el sistema compara el resultado con la estimación inicial, entonces debe mostrar la diferencia correspondiente entre la cosecha estimada y la obtenida.*

---

## 🔬 Diagnóstico Inicial y Hallazgos Encontrados

Durante la inspección de caja blanca y análisis de arquitectura se detectaron las siguientes discrepancias funcionales previas:

1. **Persistencia de liberación del lote:**  
   En la implementación original, el controlador `CosechaController` asignaba `activo_en_lote = false` (un booleano en una columna que almacena el ID del lote ocupado o null) y **no** actualizaba el atributo `estado` del modelo `Lote` a `'disponible'`. En consecuencia, el lote permanecía marcado como `'ocupado'` en base de datos y no aparecía en las listas de selección para nuevas asignaciones de cultivos (`cultivos.create`).
2. **Estimación inicial y comparación de rendimiento (Criterio 6 ausente):**  
   El modelo `Cultivo` no contaba con un atributo para almacenar la proyección o estimación inicial esperada en kilogramos (`cantidad_estimada_kg`), impidiendo calcular o desplegar la diferencia métrica entre el volumen cosechado y el proyectado.
3. **Validación de umbral mínimo de cosecha:**  
   Faltaba una regla estricta en el Request que impidiera el registro de cosechas de 0 kg o valores negativos, con mensajes localizados y claros para el usuario.
4. **Despliegue de trazabilidad e histórico:**  
   Era necesario asegurar que en la vista detallada del lote ([`resources/views/lotes/show.blade.php`](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php)) se desplegara fielmente el histórico de cosechas cerradas con sus kilogramos reales obtenidos.

---

## 🛠️ Modificaciones e Implementaciones Realizadas

### 1. Migración de Base de Datos y Modelo Cultivo
- Se generó y aplicó la migración [`database/migrations/2026_10_25_030815_add_cantidad_estimada_to_cultivos_table.php`](file:///c:/laragon/www/Sigvos_laravel/database/migrations/2026_10_25_030815_add_cantidad_estimada_to_cultivos_table.php) añadiendo la columna decimal `cantidad_estimada_kg` (10, 2) nula por defecto.
- En [`app/Models/Cultivo.php`](file:///c:/laragon/www/Sigvos_laravel/app/Models/Cultivo.php), se incorporó `cantidad_estimada_kg` dentro de `$fillable` y se declaró el cast `'cantidad_estimada_kg' => 'decimal:2'`.

### 2. Controlador de Cosechas (`CosechaController.php`)
En [`app/Http/Controllers/CosechaController.php`](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/CosechaController.php):
- **Validación robusta (Criterio 4):** Regla `min:0.1` con mensaje descriptivo: *"La cantidad cosechada debe ser un valor numérico positivo mayor a 0 kg."* y validación de fecha no futura (`before_or_equal:today`).
- **Cierre del ciclo y liberación automática del lote (Criterios 1 y 2):**
  - Se actualiza el cultivo con `estado = 'cosechado'`, `fecha_cosecha_real`, `cantidad_cosechada_kg`, `observaciones` y se desvincula la ocupación activa fijando `activo_en_lote = null`.
  - Se ejecuta `$cultivo->lote->update(['estado' => 'disponible'])`, liberando de forma inmediata e irreversible el lote para permitir nuevas siembras.
- **Cálculo de variación vs. estimación inicial (Criterio 6):**
  - Se calcula la diferencia: `$diferencia = round($request->cantidad_cosechada_kg - $cultivo->cantidad_estimada_kg, 2)`.
  - Se formatea la notificación flash de SweetAlert / sesión detallando: `Comparación con estimación: +X kg respecto a lo proyectado (Y kg)`.

### 3. Vistas de Usuario
- **Listado y Cosechas ([`resources/views/cosecha/index.blade.php`](file:///c:/laragon/www/Sigvos_laravel/resources/views/cosecha/index.blade.php)):**
  - Se incorporó la columna y badge de Rendimiento con comparativa visual (`Dif: +X Kg vs est. (Y Kg)` en verde/rojo según superávit o déficit).
  - Se enriqueció el modal interactivo con advertencia explicativa sobre la liberación del lote y confirmación de observaciones.
- **Historial por Lote ([`resources/views/lotes/show.blade.php`](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/show.blade.php)):**
  - En la pestaña de histórico (`#historial`), se listan los cultivos cerrados asociados al lote reflejando el código, variedad, fecha de finalización y el volumen acumulado en kilogramos (`number_format($cultivoHist->cantidad_cosechada_kg, 2) . ' Kg'`).

---

## 🧪 Matriz de Verificación y Pruebas Automatizadas

Se implementó una suite completa de pruebas unitarias y de integración en [`tests/Feature/RegistroCosechasTest.php`](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/RegistroCosechasTest.php) cubriendo cada uno de los 6 criterios de aceptación:

| Criterio | Test Implementado | Estado | Aserciones |
| :--- | :--- | :---: | :---: |
| **Criterio 1: Registro exitoso de cosecha** | `test_criterio_1_registro_exitoso_de_cosecha` | **PASS** | `cultivo->estado == 'cosechado'`, `activo_en_lote == null`, `lote->estado == 'disponible'` |
| **Criterio 2: Lote disponible para nuevo cultivo** | `test_criterio_2_lote_disponible_despues_de_cosecha` | **PASS** | Lote disponible en BD y visible en `cultivos.create` para nueva selección |
| **Criterio 3: Historial de producción por lote** | `test_criterio_3_historial_de_produccion_por_lote` | **PASS** | Historial de `lotes.show` visualiza código y kg acumulados (`920.00 Kg`) |
| **Criterio 4: Validación de cantidad mínima** | `test_criterio_4_validacion_de_cantidad_minima` | **PASS** | Intento con `0 kg` genera error de validación de sesión y cultivo sigue activo |
| **Criterio 5: Cosecha con observaciones** | `test_criterio_5_registro_de_cosecha_con_observaciones` | **PASS** | Observaciones técnicas y fitosanitarias persisten íntegramente en BD |
| **Criterio 6: Comparación con estimación** | `test_criterio_6_comparacion_con_estimacion_inicial` | **PASS** | Muestra diferencia calculada (`+50 Kg`) en mensaje flash y vista `cosecha.index` |

### Resultado de Ejecución de la Suite DP-17:
```
Tests:    6 passed (29 assertions)
Duration: 0.67s
```

---

## 🛡️ Prueba de Regresión Acumulada

Se ejecutó la suite de regresión con todas las pruebas automatizadas del sistema Sigvos correspondientes a las Historias de Usuario auditadas (DP-01 a DP-17):

```
Tests:    94 passed (447 assertions)
Duration: 4.37s
Status:   ALL GREEN — CERO REGRESIONES DETECTADAS
```

---

## 🏆 Conclusión y Certificación de Calidad

La Historia de Usuario **DP-17: Registro de Cosechas y Liberación Automática de Lotes** cumple de manera exhaustiva, segura e íntegra con todos los criterios de aceptación y estándares de calidad agronómica y de ingeniería de software.

**Calificación Definitiva:** **100 / 100** — **APROBADO Y CERTIFICADO PARA PRODUCCIÓN**.
