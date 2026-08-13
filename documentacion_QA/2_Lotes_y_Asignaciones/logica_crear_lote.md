La logica de esta funcionalidad se maneja principalmente desde la vista lotes/create.blade.php.

Para realizar esta accion, el usuario interactua con el formulario para ingresar el identificador del lote, nombre, area en hectareas y una fotografia si lo desea.

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador LoteController.php mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion store.

Este controlador se apoya en el modelo Lote.php para interactuar con la tabla de lotes. La funcion primero valida que el usuario sea administrador. Luego valida los datos para asegurarse de que el identificador del lote sea unico en la base de datos. Si se adjunto una fotografia, usa los metodos de Storage para guardar el archivo fisico en la carpeta public/lotes.

Al finalizar el proceso con exito o si ocurre algun error de validacion, el mismo controlador se encarga de hacer el redireccionamiento para devolver al usuario a la vista de lotes, mostrando un mensaje flotante con el resultado de la operacion.

Posibles fallos y solucion de errores (Evaluacion):
- Si un trabajador o intruso intenta crear un lote enviando la peticion directamente sin usar el formulario: El sistema fallara y creara un lote sin permiso si el controlador no lo impide. Si ocurre esto, significa que el instructor borro la linea de proteccion al inicio de la funcion. Se soluciona yendo al LoteController@store y reescribiendo la linea: if (! $request->user()->isAdmin()) abort(403);
- Si se adjunta una foto, el registro se guarda, pero la foto sale rota en la pagina: Es un fallo comun donde se ha roto el vinculo simbolico de las carpetas publicas del servidor. No hay que tocar el codigo PHP; se soluciona ejecutando el comando php artisan storage:link desde la terminal de comandos para que Laravel vuelva a vincular la carpeta storage con la carpeta publica.
