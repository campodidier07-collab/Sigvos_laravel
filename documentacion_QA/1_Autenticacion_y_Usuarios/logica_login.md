La logica de esta funcionalidad se maneja principalmente desde la vista auth/login.blade.php.

Para realizar esta accion, el usuario interactua con el formulario de inicio de sesion, ingresando su correo y contrasena.

Al momento de enviar los datos, el formulario envia una peticion POST a la ruta que esta conectada directamente con el controlador AuthenticatedSessionController.php. Dentro de este controlador, el sistema pasa por la funcion store.

Este controlador se apoya en el archivo LoginRequest.php que es el encargado de validar los datos ingresados y realizar la conexion con la base de datos a traves de Auth::attempt. Si el usuario intenta demasiadas veces con credenciales incorrectas, esta misma clase usa RateLimiter para bloquear la cuenta temporalmente.

Al finalizar el proceso con exito, el controlador regenera la sesion para mayor seguridad y se encarga de hacer el redireccionamiento para devolver al usuario a la vista del dashboard. Si las credenciales fallan, devuelve al usuario a la vista de login con los errores correspondientes.

Posibles fallos y solucion de errores (Evaluacion):
- Si al intentar iniciar sesion el sistema devuelve un error indicando que falta el token CSRF o la pagina expiro (419): Es probable que se haya borrado la directiva @csrf en el formulario de la vista login.blade.php. Se soluciona abriendo dicha vista y agregando @csrf justo debajo de la etiqueta form.
- Si el login siempre falla a pesar de ingresar la contrasena correcta: Puede que se haya borrado la instruccion de Auth::attempt en el archivo LoginRequest.php, o que al crear el usuario no se haya usado Hash::make para encriptar la clave, causando que la base de datos guarde la contrasena en texto plano y no coincida. Se soluciona verificando el metodo authenticate() en LoginRequest.php y el metodo store() en UsuarioController.php.
