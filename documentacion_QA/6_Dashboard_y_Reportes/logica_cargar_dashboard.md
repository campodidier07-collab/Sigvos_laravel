La logica de esta funcionalidad se maneja desde el mismo instante en que el usuario inicia sesion con exito.

Al ingresar, la ruta conectada (dashboard) dirige la peticion HTTP GET hacia el controlador DashboardController.php. Dentro de este controlador, el sistema pasa por la funcion index.

Este controlador es el mas pesado a nivel de consultas, ya que se apoya en multiples modelos: Lote.php, Cultivo.php y Actividad.php para interactuar con casi toda la base de datos simultaneamente. La funcion primero duplica las consultas (usando el metodo clone) para contar cuantas actividades estan en estado "pendiente" y cuantas en estado "completada" sin sobrecargar la memoria. Ademas, utiliza la libreria de tiempo Carbon para buscar actividades o cosechas que esten en un rango de los proximos 7 a 30 dias.

Durante todo el proceso, el controlador verifica el rol de la persona conectada para filtrar los conteos y limitar los resultados a lo que le corresponde ver unicamente al trabajador.

Al finalizar el proceso, el mismo controlador evalua la variable de sesion. Si el rol pertenece a un trabajador, devuelve al usuario a la vista dashboard_worker.blade.php; si es administrador, lo redirige a la vista dashboard.blade.php. Ambas vistas se encargan de pintar la informacion recolectada en las tarjetas correspondientes.

Posibles fallos y solucion de errores (Evaluacion):
- Si el conteo de indicadores en las tarjetas se paraliza o muestra numeros desorbitados sin relacion: Sugiere que las instrucciones where() limitantes se eliminaron del controlador en el instante de hacer count(). Se soluciona yendo al DashboardController@index, y garantizando que al modelo pertinente se le acompanen metodos como where('estado', 'completada')->count() antes de asignarsele a las variables correspondientes a inyectar en las vistas.
- Si despues de hacer login exitoso, la pagina de inicio del Dashboard arroja errores de variables faltantes, en particular porque muestra una interfaz administrativa a perfiles operativos: El defecto reside en el chequeo de roles que orquesta el controlador. Se soluciona verificando el bloque final del metodo index. De faltar, es mandatorio restituir la validacion condicional (como un if usando esTrabajador() o similar evaluando el id_rol) la cual debera invocar el retorno a la respectiva vista dashboard_worker.blade.php disenada en concordancia.
