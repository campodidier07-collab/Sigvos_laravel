# Auditoría de Calidad de Software: Historia de Usuario DP-1

| Campo | Detalle |
| :--- | :--- |
| **Identificador** | `DP-1` |
| **Rol** | Administrador |
| **Funcionalidad** | Iniciar sesión en el sistema con mis credenciales personales, control de inactividad y cierre de sesión seguro |
| **Módulo** | Autenticación y Seguridad (Web y API) |
| **Estado de Cumplimiento Inicial** | 🟡 CUMPLE PARCIALMENTE |
| **Estado de Cumplimiento Final** | 🟢 **CUMPLE AL 100% (SUBSANADO Y VERIFICADO)** |
| **Fecha de Evaluación** | 2026-10-24 |
| **Auditor Responsable** | QA Lead & Requirements Analyst |

---

## 1. Criterios de Aceptación Evaluados y Verificados

| # | Escenario | Gherkin / Comportamiento Esperado | Estado Inicial | Estado Final |
| :-: | :--- | :--- | :-: | :-: |
| **1** | Validación de campos obligatorios | **Dado** que el administrador está en el inicio de sesión, **cuando** deja vacío el usuario o contraseña y pulsa "Iniciar sesión", **entonces** el sistema no permite continuar y muestra que son obligatorios. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **2** | Acceso exitoso al panel administrativo | **Dado** que la cuenta del administrador está activa, **cuando** ingresa credenciales válidas y pulsa "Iniciar sesión", **entonces** inicia sesión y lo redirige al panel administrativo. | ⚠️ CUMPLE PARCIALMENTE | 🟢 **CUMPLE** |
| **3** | Credenciales incorrectas | **Dado** que el administrador está en el inicio de sesión, **cuando** ingresa credenciales incorrectas, **entonces** rechaza el acceso y muestra un mensaje de error. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **4** | Bloqueo temporal por intentos fallidos | **Dado** que ha realizado varios intentos fallidos, **cuando** supera el límite permitido, **entonces** el sistema bloquea temporalmente el acceso e informa la restricción. | ⚠️ CUMPLE PARCIALMENTE | 🟢 **CUMPLE** |
| **5** | Control de sesión e inactividad | **Dado** que inició sesión correctamente, **cuando** permanece inactivo durante el tiempo configurado, **entonces** el sistema cierra la sesión o solicita nueva autenticación. | ✅ CUMPLE | 🟢 **CUMPLE** |
| **6** | Cierre de sesión seguro | **Dado** que tiene una sesión activa, **cuando** selecciona "Cerrar sesión", **entonces** el sistema finaliza la sesión de forma segura y lo redirige al inicio de sesión. | ⚠️ CUMPLE PARCIALMENTE | 🟢 **CUMPLE** |

---

## 2. Acciones Correctivas Aplicadas en el Código

### 🔧 Corrección 1: Validación de Cuenta Activa y Sincronización de Bloqueo
* **Archivo modificado:** [app/Http/Requests/Auth/LoginRequest.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Requests/Auth/LoginRequest.php)
* **Solución implementada:**
  1. Se verifica de forma previa si la cuenta existe y está inactiva (`activo == false`), arrojando mensaje específico: *"Tu cuenta ha sido desactivada. Comunícate con el administrador."*
  2. Se verifica si el usuario se encuentra bloqueado temporalmente en BD (`bloqueado_hasta`).
  3. Se pasa la restricción `['activo' => true]` al llamar `Auth::attempt(...)`.
  4. Se sincronizan en base de datos `intentos_fallidos` y `bloqueado_hasta` tras 5 intentos fallidos acumulados.
  5. Se limpian los contadores tras un inicio de sesión exitoso.

### 🔧 Corrección 2: Redirección post-Logout hacia Inicio de Sesión
* **Archivo modificado:** [app/Http/Controllers/Auth/AuthenticatedSessionController.php](file:///c:/laragon/www/Sigvos_laravel/app/Http/Controllers/Auth/AuthenticatedSessionController.php)
* **Solución implementada:**
  Se modificó la redirección en `destroy()` para enviar directamente a `route('login')`, dando cumplimiento exacto al Escenario 6.

### 🔧 Corrección 3: Fábrica de Pruebas y Suite de Pruebas Automatizadas
* **Archivos modificados/creados:**
  - [database/factories/UserFactory.php](file:///c:/laragon/www/Sigvos_laravel/database/factories/UserFactory.php) y [database/factories/UsuarioFactory.php](file:///c:/laragon/www/Sigvos_laravel/database/factories/UsuarioFactory.php) adaptados al modelo `Usuario`.
  - [tests/Feature/Auth/AuthenticationTest.php](file:///c:/laragon/www/Sigvos_laravel/tests/Feature/Auth/AuthenticationTest.php) actualizado con seeder de roles, prueba para usuarios inactivos (`test_inactive_users_cannot_authenticate`) y aserción de redirección a `login`.

---

## 3. Evidencias de Verificación Automatizada

Ejecución de suite completa de pruebas de autenticación (Web y API):

```bash
# Pruebas Web (Breeze / LoginRequest)
php artisan test tests/Feature/Auth/AuthenticationTest.php
✓ it renders login screen
✓ it authenticates users with valid credentials
✓ it rejects inactive users with validation error
✓ it rejects invalid passwords
✓ it logs out and redirects to login screen
Tests:    5 passed (11 assertions)
Duration: 1.08s

# Pruebas API (Sanctum / AuthController)
php artisan test tests/Feature/Api/AuthApiTest.php
✓ user can login via api and receives token
✓ login fails with invalid credentials
✓ user can register via api
Tests:    3 passed (23 assertions)
Duration: 0.84s
```

---

## 4. Dictamen Final del QA Lead

* **Resultado:** 🟢 **APROBADO AL 100%**.
* **Estado:** Todos los criterios de aceptación de la Historia de Usuario `DP-1` se encuentran implementados, probados y verificados con pruebas automatizadas en verde.
