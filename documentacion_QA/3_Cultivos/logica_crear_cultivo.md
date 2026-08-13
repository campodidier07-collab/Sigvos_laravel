La logica de esta funcionalidad se maneja principalmente desde la vista cultivos/create.blade.php.

Para realizar esta accion, el usuario interactua con el formulario para registrar un nuevo cultivo, proporcionando fechas, seleccionando el lote disponible y la variedad de planta. 

Antes de mostrar el formulario, el controlador CultivoController.php (en su funcion create) hace una consulta al modelo Lote.php para filtrar los lotes. Utiliza la instruccion whereDoesntHave para asegurarse de que en la lista desplegable solo aparezcan los lotes que esten desocupados (aquellos cuyo cultivo activo_en_lote sea nulo).

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador CultivoController.php mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion store. El controlador valida que la fecha de siembra no sea futura y que la fecha de cosecha sea posterior a la de siembra.

Adicionalmente, el controlador verifica nuevamente en la base de datos que el lote seleccionado siga disponible para evitar empalmes. Despues de insertar el registro en la tabla de cultivos usando el modelo Cultivo.php, el controlador realiza una segunda accion: actualiza la tabla del Lote seleccionado para cambiar su estado a "ocupado".

Al finalizar el proceso con exito, el controlador se encarga de hacer el redireccionamiento para devolver al usuario a la vista principal de cultivos, mostrando un mensaje flotante de operacion exitosa.

Posibles fallos y solucion de errores (Evaluacion):
- Si el desplegable de seleccion de lotes muestra terrenos que ya tienen cultivos activos sembrados en ellos: El error reside en la fase de creacion del formulario. Esto ocurre si se elimina la condicion whereDoesntHave en el controlador, lo que causa que se impriman todos los lotes indiscriminadamente. Se soluciona yendo al CultivoController@create y asegurando que la consulta de lotes incluya la verificacion de que activo_en_lote no sea nulo.
- Si el formulario permite registrar fechas imposibles (como sembrar en el ano 2050): Se han eliminado las reglas de validacion provistas por Laravel. Se soluciona revisando la funcion store del CultivoController y restaurando las reglas before_or_equal:today (para la siembra) y after:fecha_siembra (para la cosecha).
