La logica de esta funcionalidad se maneja principalmente desde la vista actividades/create.blade.php.

Para realizar esta accion, el administrador interactua con el formulario para asignar una nueva tarea, indicando que tipo de actividad es, seleccionando un trabajador y la fecha de ejecucion.

Al cargar la vista, el controlador ActividadController.php pasa por la funcion create. En esta funcion, el sistema consulta al modelo Usuario.php filtrando estrictamente por id_rol = 2 y activo = true, para asegurar que el desplegable solo muestre trabajadores vigentes. 

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador ActividadController.php mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion store. El controlador inyecta automaticamente el ID del usuario en sesion dentro de la variable creado_por, dejando rastro de quien ordeno la tarea.

Una vez que el modelo Actividad.php guarda la informacion en la tabla correspondiente de la base de datos, el controlador ejecuta una accion secundaria: utiliza el modelo Notificacion.php para insertar una nueva alerta dirigida al ID del trabajador seleccionado, avisandole de su nueva tarea.

Al finalizar el proceso con exito, el mismo controlador se encarga de hacer el redireccionamiento para devolver al usuario a la vista index de actividades, mostrando un mensaje flotante de asignacion correcta.

Posibles fallos y solucion de errores (Evaluacion):
- Si el selector de trabajadores en el formulario aparece completamente vacio o muestra usuarios desactivados y administradores: El fallo esta en la preparacion de la vista. Se soluciona revisando la funcion create de ActividadController.php, garantizando que al modelo Usuario se le apliquen los metodos de consulta where('id_rol', 2) y where('activo', true) antes del ->get(). Sin el metodo ->get(), las instancias nunca se crearan, provocando que la vista falle.
- Si se crea una actividad exitosamente pero al trabajador asignado no le llega jamas la notificacion en el sistema: La logica desencadenante secundaria fue eliminada. Se soluciona navegando al ActividadController@store y restaurando el fragmento de codigo responsable de hacer Notificacion::create() pasandole el id del trabajador asignado, justo antes de ejecutar el return redirect() final.
