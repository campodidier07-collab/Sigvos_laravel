La logica de esta funcionalidad se maneja principalmente desde los modales anexos en las vistas de detalles (por ejemplo, en cultivos/show.blade.php).

Para realizar esta accion, el usuario interactua con el formulario seleccionando una imagen (jpg, png) desde su dispositivo.

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador FotoCultivoController.php (o equivalentes dependiendo del modulo) mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion store.

Este controlador se apoya en el modelo FotoCultivo.php para interactuar con la tabla de fotografias en la base de datos. La funcion contiene dos candados criticos: primero, valida que el archivo no supere el peso permitido y tenga un formato de imagen real; segundo, verifica a nivel de seguridad que la persona que sube la foto este de verdad asignada a ese terreno especifico (cruzando el ID del lote y el trabajador). Si la validacion es correcta, el controlador utiliza la funcion de Storage para guardar el archivo fisico dentro de la carpeta oculta public/cultivos del servidor.

Al finalizar el proceso con exito, el controlador guarda la ruta de texto en la tabla y se encarga de hacer el redireccionamiento para devolver al usuario a la vista que estaba mirando, mostrando un mensaje de exito.

Posibles fallos y solucion de errores (Evaluacion):
- Si un trabajador o actor de un predio externo logra evadir la restriccion y subir un archivo a un modulo ajeno: Denota la ausencia del filtro condicional. Se soluciona revisando la funcion store del FotoCultivoController.php y constatando la implementacion de la validacion logica en la que el usuario autenticado deba pertenecer a la asignacion vinculada del cultivo al cual intenta anadir el recurso grafico, abortando con codigo 403 si falla.
- Si al proceder a guardar, el framework genera una excepcion vinculada a fallos del sistema o directorios no encontrados, ademas de omitir el archivo final al observar la carpeta de disco local: Remite a una anomalia o supresion del metodo Storage::disk utilizado para canalizar el documento a la ubicacion permanente. Se soluciona verificando que, tras validacion, se procese explicitamente la propiedad archivo empleando su respectivo metodo store('cultivos', 'public') antes de enviar los metadatos al ORM.
