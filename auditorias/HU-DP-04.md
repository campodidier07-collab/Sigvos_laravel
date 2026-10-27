# Auditoría de Calidad de Software: Historia de Usuario DP-4

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-4` |
| **Rol** | Administrador |
| **Funcionalidad** | Registrar lotes agrícolas con información completa |
| **Módulo** | Módulo Agrícola / Gestión de Lotes y Parcelas |
| **Calificación Inicial** | 🟢 **100 / 100** (CUMPLE) |
| **Calificación Final** | 🟢 **100 / 100 (APROBADO AL 100% - CERTIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Registro exitoso de nuevo lote | **Dado** que necesito registrar un nuevo lote, **cuando** ingreso el identificador, nombre, ubicación y área del lote, **entonces** el sistema crea el lote y lo muestra en la lista de lotes. | 🟢 **CUMPLE** | 🟢 **CUMPLE** |
| **2** | Intento de duplicar identificador | **Dado** que intento registrar un lote, **cuando** ingreso un identificador que ya está registrado, **entonces** el sistema muestra un error y no permite continuar con el registro. | 🟢 **CUMPLE** | 🟢 **CUMPLE** |
| **3** | Consultar estado de lotes | **Dado** que tengo lotes registrados, **cuando** consulto la lista de lotes, **entonces** el sistema muestra el estado actual de cada lote. | 🟢 **CUMPLE** | 🟢 **CUMPLE** |
| **4** | Validación de campos obligatorios | **Dado** que estoy registrando un lote, **cuando** dejo campos obligatorios vacíos y guardo el registro, **entonces** el sistema resalta los campos vacíos y no permite guardar. | 🟢 **CUMPLE** | 🟢 **CUMPLE** |

---

## 2. Análisis Técnico y Evidencias en Código

### ✅ Escenario 1: Registro exitoso de nuevo lote (CUMPLE)
* **Backend:** En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L76-L103), el método `store()` valida los datos requeridos, asigna por defecto el estado `disponible` y el valor `activo = true`, persiste el registro mediante `Lote::create($datos)` y redirige a la lista (`route('lotes.index')`) con mensaje flash de confirmación:
  ```php
  $datos['es_alternativo'] = $request->has('es_alternativo');
  $datos['estado'] = 'disponible';
  $datos['activo'] = true;
  Lote::create($datos);
  return redirect()->route('lotes.index')->with('success', 'Lote creado exitosamente.');
  ```
* **Frontend:** En [resources/views/lotes/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/index.blade.php#L223-L267), el nuevo lote se lista inmediatamente con su identificador, nombre, área, estado y accesos a detalle/edición.

---

### ✅ Escenario 2: Intento de duplicar identificador (CUMPLE)
* **Restricción de Unicidad:**
  - En la base de datos ([2025_01_01_200000_create_plots_tables.php](file:///c:/laragon/www/Sigvos_laravel/database/migrations/2025_01_01_200000_create_plots_tables.php#L17)): `$table->char('identificador', 1)->unique();`.
  - En el controlador ([LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L87)): `'identificador' => 'required|string|size:1|unique:lotes,identificador'`.
  - Si se intenta ingresar un identificador preexistente, Laravel detiene la ejecución arrojando error de validación específico con el mensaje: *"Este identificador de lote ya se encuentra registrado."*.
  - En el formulario ([resources/views/lotes/create.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/create.blade.php#L141-L146)), el campo se resalta con el borde y fondo de error `.is-invalid-custom` y muestra la leyenda de alerta.

---

### ✅ Escenario 3: Consultar estado de lotes (CUMPLE)
* **Visualización de Estados:**
  - En [LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L39-L56), se agrupan los estados (`disponible`, `ocupado`, `en_descanso`, `inactivo`) y se calculan los totales en tiempo real para las tarjetas de KPIs superiores.
  - En la tabla de [resources/views/lotes/index.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/index.blade.php#L243-L247), cada registro exhibe su estado actual mediante pills de color dedicados:
    - `.status-disponible`: Verde (#047857)
    - `.status-ocupado`: Azul (#1d4ed8)
    - `.status-en_descanso`: Ámbar (#b45309)
    - `.status-inactivo`: Rojo (#b91c1c)

---

### ✅ Escenario 4: Validación de campos obligatorios (CUMPLE)
* **Frontend y Backend Sincronizados:**
  - En [create.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/lotes/create.blade.php#L140-L177), los 4 campos principales (`identificador`, `nombre`, `ubicacion`, `area_ha`) poseen el atributo HTML5 `required`.
  - En [LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php#L86-L90), el backend exige obligatoriamente cada uno de los campos con reglas estrictas:
    ```php
    'identificador' => 'required|string|size:1|unique:lotes,identificador',
    'nombre'        => 'required|string|max:100',
    'ubicacion'     => 'required|string|max:200',
    'area_ha'       => 'required|numeric|min:0.01',
    ```
  - Si un usuario omite campos o intenta un submit en blanco, la vista intercepta y resalta visualmente los inputs no completados con la clase `.is-invalid-custom` y el mensaje de campo requerido.

---

## 3. Acciones de Aseguramiento de Calidad Aplicadas

1. **Normalización Automática a Mayúsculas:**
   En [app/Http/Controllers/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/LoteController.php) y [app/Http/Controllers/Api/LoteController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/Api/LoteController.php), se agregó la normalización `strtoupper(trim(...))` para garantizar que identificadores ingresados en minúsculas (ej: `a`) se estandaricen a `A` antes de la validación y almacenamiento, evitando colisiones de colación.
2. **Mensajes de Validación en Español:**
   Se incorporaron mensajes personalizados en español para feedback directo al usuario final.
3. **Restricción de Seguridad RBAC:**
   Se confirmó que los trabajadores operativos no pueden crear ni alterar lotes (`abort(403)` en `create` y `store`), reservando la potestad exclusivamente al rol Administrador.

---

## 4. Evidencias de Verificación Automatizada

Se implementó la suite completa de pruebas unitarias y de integración para validar los 4 escenarios de la historia:

```bash
php artisan test tests/Feature/LoteTest.php tests/Feature/Api/LotesCultivosApiTest.php
✓ admin can create lote successfully
✓ cannot create lote with duplicate identificador
✓ can view lotes list with current status
✓ cannot create lote with missing required fields
✓ worker cannot create lote
✓ can list lotes via api
✓ admin can create lote via api
✓ can list cultivos and catalogs

Tests:    8 passed (44 assertions)
Duration: 1.07s
```

---

## 5. Dictamen Final del QA Lead

* **Calificación Final:** 🟢 **100 / 100 (APROBADO AL 100%)**.
* **Estado:** Todos los criterios de aceptación de la Historia de Usuario `DP-4` se encuentran plenamente implementados, con validaciones robustas en base de datos y controlador, interfaz reactiva a errores y suite de pruebas automatizadas certificada en verde.
