La logica de esta funcionalidad se maneja principalmente desde la vista calendario/index.blade.php.

Para realizar esta accion, el usuario ingresa a la pestana del calendario. La vista carga el script de Javascript (FullCalendar) el cual esta vacio inicialmente.

El Javascript interactua automaticamente haciendo una peticion asincrona tipo AJAX (o fetch) a la ruta definida, la cual esta conectada con el controlador CalendarioController.php. Dentro de este controlador, el sistema pasa por la funcion eventos.

Este controlador se apoya en el modelo Actividad.php para buscar todos los registros en la base de datos. La funcion recopila la informacion y utiliza el metodo map() de las colecciones de Laravel para transformar los datos extraidos de la tabla a un formato especifico. Dentro de esta transformacion, el controlador aplica logica condicional para inyectar un color hexadecimal al evento dependiendo del estado (por ejemplo, color verde si esta completada).

Al finalizar el proceso, a diferencia de otras rutas, este controlador no devuelve una vista. En su lugar, el mismo controlador se encarga de devolver un response()->json() que el navegador de Javascript interpreta y usa para dibujar los elementos en el calendario.

Posibles fallos y solucion de errores (Evaluacion):
- Si el calendario aparece dibujado en pantalla pero completamente en blanco (sin cuadrantes coloreados): Se trata de un fallo tipico de recoleccion de datos Javascript hacia el backend. Se soluciona revisando la vista calendario/index.blade.php donde se llama a la libreria FullCalendar, verificando que la URL que provee los eventos sea la correcta (frecuentemente definida como events: '/calendario/eventos'). Si esa linea ha sido borrada, Javascript jamas descargara la matriz JSON proveniente de PHP.
- Si un usuario reporta que al cargar los eventos del mes la aplicacion web se traba o consume mucha memoria: Resulta altamente probable que falte un filtro en el query al modelo. Se soluciona yendo al CalendarioController y asegurandose de acotar la busqueda en el modelo (limitandola por ejemplo a las actividades correspondientes unicamente al mes en curso, usando variables o fechas recibidas mediante parametros) en lugar de arrojar la totalidad historica de las tareas de la granja.
