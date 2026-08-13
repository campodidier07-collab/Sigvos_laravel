La logica de esta funcionalidad se maneja principalmente desde la vista lotes/index.blade.php.

Para realizar esta accion, el usuario ingresa a la opcion de lotes desde el menu de navegacion. La peticion llega al controlador LoteController.php.

Dentro de este controlador, el sistema pasa por la funcion index. El controlador utiliza el modelo Lote.php para interactuar con la base de datos haciendo un join (mediante el metodo with) para traer tambien el tipo preferido de cultivo y el cultivo activo en una sola consulta. 

Una logica importante que ocurre aqui es la restriccion de permisos. El controlador verifica el rol de la persona que esta viendo la lista; si es un trabajador, el controlador agrega una condicion (whereHas) para filtrar los resultados, mostrando unicamente los lotes en donde la tabla intermedia de asignaciones contenga el ID del trabajador actual. Si es un administrador, le muestra todos los lotes.

Al finalizar el proceso, el controlador retorna a la vista lotes/index.blade.php empaquetando los datos obtenidos de la base de datos para mostrarlos en forma de tarjetas, y enviando tambien los conteos de estadisticas generales (KPIs).

Posibles fallos y solucion de errores (Evaluacion):
- Si el trabajador logra ver todos los lotes de la granja (una violacion de privacidad evidente): Esto significa que se rompio la condicion if que aisla a los trabajadores. Se soluciona revisando la funcion index del controlador LoteController y asegurando que exista y funcione correctamente el bloque de codigo if ($usuario->esTrabajador()) que ejecuta el whereHas('trabajadores') para atar los resultados al ID del usuario autenticado.
- Si la pagina de lotes tarda mucho en cargar o crashea por problemas de memoria (especialmente al crecer el volumen de datos): Es muy probable que se haya borrado la instruccion de "Eager Loading" en el query, desencadenando el problema N+1 al cargar cultivos individualmente. Se soluciona confirmando que la consulta inicie con Lote::with(['tipoPreferido', 'cultivoActivo']) en vez de usar un simple Lote::all().
