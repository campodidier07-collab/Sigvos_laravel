La logica de esta funcionalidad se maneja principalmente desde la campana de notificaciones (vista global del layout) o desde el panel de notificaciones.

Para realizar esta accion, el usuario interactua con el listado haciendo clic sobre una notificacion especifica, lo que dispara el evento por detras en un formulario invisible.

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador NotificacionController.php mediante una peticion POST. Dentro de este controlador, el sistema pasa por la funcion marcarLeida.

Este controlador se apoya en el modelo Notificacion.php para consultar la fila especifica de la tabla en base al ID provisto. La logica de seguridad en esta funcion compara que el destinatario de la notificacion guardado en la base de datos sea igual al ID de la persona logueada; de no serlo, se corta la ejecucion para evitar accesos de terceros. Una vez validado, se hace el respectivo update cambiando el estado de falso a verdadero para que el contador de la campanita la descarte.

Al finalizar el proceso con exito, el mismo controlador verifica si la notificacion traia consigo una URL incrustada (por ejemplo, el link al cultivo). De ser asi, el controlador redirecciona al usuario directamente a ese enlace. De lo contrario, lo devuelve a la vista actual recargando la pagina sin la alerta visual.

Posibles fallos y solucion de errores (Evaluacion):
- Si el usuario reporta que interactua clickeando sobre las alertas multiples veces pero el globo contador rojo persiste con los mismos digitos sin desaparecer: Acredita que se extirpo la mutacion booleana del controlador. Se soluciona explorando el archivo NotificacionController.php, en su seccion marcarLeida, y asegurando que se ordene la variacion `$notificacion->update(['leida' => true]);` inmediatamente tras sortear el bloqueo de comprobacion, pues sin ella, MySQL sostendra de por vida el estado de ignota en falso.
- Si el sistema emite violacion 403 cuando el receptor legitimo y propietario de la cuenta presiona sobre un link para intentar leer: Alude directamente a la interferencia o configuracion errada de la comprobacion Anti-Hackeo. Se soluciona evaluando el operador comparativo inserto en el metodo marcarLeida, constatando que la condicion que active el abort(403) unicamente obre en caso de que `$notificacion->id_usuario !== $request->user()->id`. Si la desigualdad fue suplantada o alterada, acarreara este rechazo erroneo de la propia sesion activa.
