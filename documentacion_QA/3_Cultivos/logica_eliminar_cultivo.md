La logica de esta funcionalidad se maneja principalmente desde la vista cultivos/show.blade.php o desde la tabla principal del listado.

Para realizar esta accion, el administrador interactua con el boton de eliminacion (usualmente un icono de papelera) que envia un formulario oculto.

Al momento de enviar los datos, el formulario en su atributo action esta conectado directamente con el controlador CultivoController.php mediante una peticion DELETE (simulada por Laravel con el metodo @method('DELETE')). Dentro de este controlador, el sistema pasa por la funcion destroy.

Este controlador se apoya en el modelo Cultivo.php para buscar y manipular el registro en la base de datos. La logica critica que ocurre aqui no es solo borrar el registro. Antes de ejecutar el delete(), el controlador verifica si la variable activo_en_lote es verdadera. De ser asi, el controlador se comunica con el modelo Lote relacionado y actualiza su estado a "disponible". Esto evita que, si se borra un cultivo por error, el lote quede bloqueado permanentemente con el estado "ocupado".

Al finalizar el proceso con exito, el controlador se encarga de hacer el redireccionamiento para devolver al usuario a la vista index, mostrando un mensaje flotante de eliminacion correcta.

Posibles fallos y solucion de errores (Evaluacion):
- Si el usuario borra exitosamente un cultivo, pero al regresar a los lotes el terreno sigue figurando como "ocupado" misteriosamente: Esto significa que la logica de desvinculacion en cascada fue removida del controlador. Se soluciona yendo al CultivoController@destroy y asegurandose de incluir la actualizacion del lote padre antes del delete(): if ($cultivo->activo_en_lote) { $cultivo->lote->update(['estado' => 'disponible']); }
- Si al hacer clic en borrar el sistema simplemente recarga la pagina pero no borra nada y falla silenciosamente: El error proviene de la vista HTML donde esta el boton de borrado. Frecuentemente esto ocurre cuando falta la directiva @method('DELETE') dentro de la etiqueta del form, lo que causa que Laravel no identifique correctamente la peticion como una orden de destruccion. Se soluciona inyectando dicha directiva en la vista correspondiente.
