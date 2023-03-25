<?php
class ci_subir_recibos extends sap_ci
{
	protected $s__filtro;
	//-----------------------------------------------------------------------------------
	//---- form_subir_recibos -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__form_subir_recibos__subir()
	{
		$procesador = new ProcesadorRecibos();
		$errores = $procesador->procesar($_FILES['archivos']);
		if(count($errores) == 0){
			toba::notificacion()->agregar('Todos los archivos se subieron con éxito','info');
		}else{
			$lista = "<ul>";
			foreach ($errores as $error) {
				$lista .= "<li>{$error['archivo']}: {$error['motivo']}";
			}
			$lista .= "</ul>";
			toba::notificacion()->agregar("Se subieron los archivos seleccionados, pero ocurrieron los siguientes errores:<br>$lista",'warning');
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_borrado -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__form_borrado__borrar($datos)
	{
		try {
			toba::consulta_php('co_becas')->borrar_recibos($datos);
			toba::notificacion()->info('Se borraron los recibos con éxito!');
		} catch (toba_error $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		}
	}

	function extender_objeto_js(){
		echo "
		const boton = document.getElementById('form_4570_form_subir_recibos_subir');
		boton.addEventListener('click', (e) => {
			toba.inicio_aguardar();
			boton.disabled = true;
		})";
	}

	




}
?>