La logica de esta funcionalidad se maneja principalmente desde la vista cosecha/index.blade.php.

Para realizar esta accion, el usuario interactua con el formulario (generalmente un modal) proporcionando la cantidad de kilos recolectados y la fecha real en que se cosecho la planta. El formulario esta filtrado previamente desde el controlador para asegurar que en la lista de opciones solo aparezcan los cultivos que esten fisicamente sembrados.

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador CosechaController.php mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion store.

Este controlador se apoya en el modelo Cultivo.php (no existe un modelo independiente para Cosecha) para interactuar con la tabla de cultivos. La funcion toma el ID recibido y utiliza el metodo findOrFail por seguridad. Acto seguido, el controlador actualiza el campo de los kilos cosechados, cambia el estado a "cosechado" y, lo mas importante, apaga la variable activo_en_lote poniendola en valor falso.

Al finalizar el proceso con exito, el mismo controlador se encarga de hacer el redireccionamiento para devolver al usuario a la vista index de cosechas, mostrando un mensaje flotante con la notificacion de que el ciclo se cerro correctamente y el terreno ha quedado liberado.

Posibles fallos y solucion de errores (Evaluacion):
- Si al registrar una cosecha exitosamente, se visita la vista de Lotes y dicho terreno sigue apareciendo misteriosamente como ocupado y no se puede volver a sembrar en el: Esto senala que la logica de desenlace fue extraida del sistema. Se soluciona revisando el metodo store en CosechaController.php y cerciorandose de que, al actualizar la tabla de cultivos (update), el arreglo incluya la clave `'activo_en_lote' => false`. Su omision desencadenara dicho fallo, inhabilitando los espacios terrestres de por vida.
- Si un usuario visualiza el selector desplegable para cosechar pero logra ver cultivos en fase germinal recien sembrados que aun no se pueden recoger: Se infiere que se altero la busqueda del index principal que llena la vista. Se soluciona examinando el CosechaController@index, y garantizando la presencia del filtro when o whereIn, acotando a que unicamente se recuperen de base de datos aquellas instancias cuyo estado ya corresponda a "creciendo" o "maduro", descartando etapas tempranas o ya culminadas.
