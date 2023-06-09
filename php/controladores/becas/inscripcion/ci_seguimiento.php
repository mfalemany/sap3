<?php
class ci_seguimiento extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_inicial(toba_ei_pantalla $pantalla)
	{
		//defino la ubicaci�n del archivo template
		$ubicacion_template = __DIR__ . '/templates/template_seguimiento.php';

		//obtengo todos los datos necesarios para el seguimiento de la solicitud
		$insc = $this->get_datos('inscripcion_conv_beca')->get();
		
		if(count($insc)){
			$datos = toba::consulta_php('co_comision_asesora')->get_detalles_seguimiento($insc);
			$datos['publicar_adm']                 = toba::consulta_php('co_convocatoria_beca')->get_campo('publicar_admisibilidad',$insc['id_convocatoria']);
			$datos['publicar_resultados']          = toba::consulta_php('co_convocatoria_beca')->get_campo('publicar_resultados',$insc['id_convocatoria']);
			$datos['estado_aval']                  = toba::consulta_php('co_inscripcion_conv_beca')->get_estado_aval_solicitud($insc);
			$datos['fecha_limite_reconsideracion'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('becas_fecha_limite_pedido_reconsideracion');
			$datos['genero_secretario']            = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('genero_secretario');
			$datos['nombre_secretario']            = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('nombre_secretario');
		}
		

		
		
		$template = $this->armar_template($ubicacion_template,$datos);
		$pantalla->set_template($template);
	}

	function get_estado_aval_desc($aval, $tipo_aval){
		// Este array mapea el tipo de aval que se recibe como parametro con los indices del array $aval
		$persona_avalo = [
			'aval_director'     => 'director_avalo_desc',
			'aval_dir_proyecto' => 'dir_proyecto_avalo_desc',
			'aval_secretaria'   => 'secretario_avalo_desc',
			'aval_decanato'     => 'decano_avalo_desc'
		];

		if($aval){
			if($aval[$tipo_aval] === TRUE){
				return "Avalado (" . $aval[$persona_avalo[$tipo_aval]] . ')';
			}elseif($aval[$tipo_aval] === FALSE){
				return "No Avalado (rechazado)";
			}else{
				return "Todav�a no se registr� el aval";
			}
		}else{
			return "Todav�a no se registr� el aval";
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_seguimiento -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_seguimiento(becas_ei_formulario $form)
	{
		$form->set_datos($this->get_datos('inscripcion_conv_beca')->get());
	}

	function get_datos($tabla = NULL)
	{
		return $tabla ? $this->controlador()->dep('inscripcion')->tabla($tabla) : $this->controlador()->dep('inscripcion');

	}

	

}
?>