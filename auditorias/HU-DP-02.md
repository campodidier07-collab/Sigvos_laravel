# Auditoría de Calidad de Software: Historia de Usuario DP-2

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-2` |
| **Rol** | Trabajador |
| **Funcionalidad** | Iniciar sesión y acceder únicamente a las funciones autorizadas para mi rol, validación de formato de correo y menú operativo restringido |
| **Módulo** | Autenticación, Control de Acceso (RBAC) y Navegación |
| **Estado de Cumplimiento Inicial** | 🟡 CUMPLE PARCIALMENTE |
| **Estado de Cumplimiento Final** | 🟢 **CUMPLE AL 100% (SUBSANADO Y VERIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Validación de campos obligatorios | **Dado** que el trabajador está en la pantalla de inicio de sesión, **cuando** deja vacío el correo o la contraseña y hace clic en "Iniciar sesión", **entonces** el sistema no permite continuar y muestra que los campos son obligatorios. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **2** | Validación del formato del correo | **Dado** que el trabajador diligencia el formulario de inicio de sesión, **cuando** ingresa un correo con formato inválido, **entonces** el sistema muestra un mensaje de error y no permite continuar. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **3** | Acceso exitoso con menú operativo restringido | **Dado** que la cuenta del trabajador está activa, **cuando** ingresa su correo y contraseña correctos y hace clic en "Iniciar sesión", **entonces** el sistema inicia sesión y muestra solo las funciones autorizadas para el trabajador. | ⚠️ CUMPLE PARCIALMENTE | 🟢 **CUMPLE** |
| **4** | Credenciales incorrectas | **Dado** que el trabajador está en la pantalla de inicio de sesión, **cuando** ingresa un correo o contraseña incorrectos y hace clic en "Iniciar sesión", **entonces** el sistema muestra un mensaje de error y no permite ingresar. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **5** | Bloqueo temporal por intentos fallidos | **Dado** que el trabajador ha realizado varios intentos fallidos, **cuando** supera el número de intentos permitidos, **entonces** el sistema bloquea el acceso e informa la restricción. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **6** | Cierre de sesión seguro | **Dado** que el trabajador tiene una sesión activa, **cuando** hace clic en "Cerrar sesión", **entonces** el sistema finaliza la sesión y lo redirige al inicio de sesión. | ✅ CUMPLE | 🟢 **CUMPLE** |

---

## 2. Acciones Correctivas Aplicadas en el Código

### 🔧 Corrección 1: Protección de Rutas Administrativas con Middleware `role:admin`
* **Archivo modificado:** [routes/web.php](file:///c:/laragon/www/Sigvos_laravel/routes/web.php)
* **Solución implementada:**
  1. Se reubicaron las rutas `/asignaciones`, `/cosecha` y `/calendario` dentro del grupo protegido por `middleware('role:admin')`.
  2. Si un trabajador intenta forzar el acceso ingresando directamente la URL por navegador, el sistema deniega el acceso arrojando HTTP 403 Forbidden.
  3. Se aplicó el middleware perimetral `role:admin,worker` a todas las rutas autenticadas para garantizar la verificación activa de cuentas desactivadas o bloqueadas en tiempo real.

### 🔧 Corrección 2: Subsanación de Atributo en Middleware CheckRole
* **Archivo modificado:** [app/Http/Middleware/CheckRole.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Middleware/CheckRole.php)
* **Solución implementada:**
  Se corrigió el nombre de la propiedad de verificación de bloqueo temporal:
  ```php
  // Corregido:
  if ($user->bloqueado_hasta && $user->bloqueado_hasta->isFuture()) {
  ```
  Permitiendo expulsar e invalidar la sesión inmediatamente si el usuario se encuentra con restricción temporal activa.

### 🔧 Corrección 3: Adaptación y Consistencia del Panel de Control del Trabajador
* **Archivos intervenidos:**
  - [resources/views/dashboard.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard.blade.php) adaptado con condicional por rol.
  - [resources/views/dashboard_worker.blade.php](file:///c:/laragon/www/Sigvos_laravel/resources/views/dashboard_worker.blade.php) y [DashboardController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/DashboardController.php) entregan al trabajador el panel operativo exclusivo con *"Mi Panel"*, resumen de labores asignadas y métricas personales de lotes, cultivos y actividades.

### 🔧 Corrección 4: Creación de Suite de Pruebas de Control de Acceso (RBAC)
* **Archivo creado:** [tests/Feature/Auth/WorkerAccessControlTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/Auth/WorkerAccessControlTest.php)
* **Casos cubiertos:**
  - Acceso de trabajador al dashboard operativo personalizado.
  - Denegación 403 al intentar acceder a `/usuarios`.
  - Denegación 403 al intentar acceder a `/reportes`.
  - Denegación 403 al intentar acceder a `/asignaciones`.
  - Acceso legítimo de administrador a `/asignaciones`.
  - Cierre y redirección forzosa al login si `bloqueado_hasta` está en el futuro.

---

## 3. Evidencias de Verificación Automatizada

```bash
php artisan test tests/Feature/Auth/WorkerAccessControlTest.php
✓ worker can access dashboard with worker panel
✓ worker cannot access admin usuarios route
✓ worker cannot access admin reportes route
✓ worker cannot access admin asignaciones route
✓ admin can access asignaciones
✓ worker is logged out if bloqueado hasta is future

Tests:    6 passed (10 assertions)
Duration: 0.99s
```

---

## 4. Dictamen Final del QA Lead

* **Resultado:** 🟢 **APROBADO AL 100%**.
* **Estado:** Todos los criterios de aceptación de la Historia de Usuario `DP-2` se encuentran rigurosamente implementados, con aislamiento de privilegios validado tanto a nivel visual como en el backend y pruebas automatizadas en verde.
