La logica de esta funcionalidad se maneja principalmente desde la vista actividades/show.blade.php.

Para realizar esta accion, el trabajador interactua con el boton o selector para cambiar el estado de la tarea de "pendiente" a "completada".

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador ActividadController.php mediante una peticion PUT o PATCH. Dentro de este controlador, el sistema pasa por la funcion update.

Este controlador se apoya en el modelo Actividad.php para buscar la tarea especifica en la base de datos. La logica critica que ocurre aqui es la captura del tiempo. Si el controlador detecta que el estado entrante en la variable es "completada", el sistema utiliza la libreria nativa (la funcion now() de Carbon) para insertar la fecha y hora exacta en la columna fecha_completada, antes de ejecutar el save().

Al finalizar el proceso con exito, el mismo controlador se encarga de hacer el redireccionamiento para devolver al trabajador a la vista que estaba observando, mostrando un mensaje flotante de confirmacion.

Posibles fallos y solucion de errores (Evaluacion):
- Si al presionar el boton "completada" la tarea actualiza correctamente su estado pero el sistema olvida registrar la fecha de finalizacion: Esto ocurre frecuentemente cuando se borra la logica condicional en el archivo controlador. Se soluciona verificando en ActividadController@update que exista un condicional analizando el nuevo estado (ej. if($request->estado == 'completada')), el cual debe estar inyectando manualmente el valor de la funcion now() en la propiedad fecha_completada de la instancia obtenida, previo a mandar la orden a la base de datos.
- Si el intento de cambio de estado retorna un mensaje del framework tipo "Trying to get property of non-object": Implica que se esta perdiendo o alterando el identificador al consultar. Se soluciona yendo al controlador y verificando que en la busqueda de la tabla se este invocando a Actividad::findOrFail($id) para que la base de datos devuelva una instancia real manipulable.
