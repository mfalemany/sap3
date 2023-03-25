<?php
class ci_evaluacion extends sap_ci
{
	protected $evaluador;
	protected $evaluaciones;
	function servicio__generar_comprobante_evaluaciones()
	{
		$dni = toba::usuario()->get_id();
		$filtro = array('nro_documento_evaluador'=>$dni);
		$evaluador = array('nro_documento'=>$dni,'ayn'=>toba::consulta_php('co_personas')->get_solo_ayn($dni));
		$evaluaciones = toba::consulta_php('co_proyectos')->get_evaluaciones_realizadas($filtro);
		$datos = array('evaluador'=>$evaluador,'evaluaciones'=>$evaluaciones);
		$pdf = new Informe_evaluaciones_realizadas($datos);
		$pdf->mostrar();
	}

	function servicio__ver_reporte()
	{
		$seleccion = toba::memoria()->get_parametros();
		if (in_array($seleccion['tipo'],array('0','D'))) {
			$datos = toba::consulta_php('co_proyectos')->get_reporte_proyecto($seleccion['id']);
			$reporte_proyecto = new Reporte_proyecto($datos);
			$reporte_proyecto->mostrar();
		} elseif($seleccion['tipo'] = 'C') {
			$punto = toba::puntos_montaje()->get('proyecto');
			toba_cargador::cargar_clase_archivo($punto->get_id(),
				'generadores_pdf/proyectos/programas/reporte_programa.php' , 'sap' );
			$datos = toba::consulta_php('co_programas')->get_reporte_programa($seleccion);
			$reporte_proyecto = new Reporte_programa($datos);
			$reporte_proyecto->mostrar();
		}else{
			return;
		}
		
			

	}
	function servicio__ver_anexo()
	{
		$params = toba::memoria()->get_parametros();
		$url_archivo = '/documentos/proyectos/'.$params['codigo'].'/anexo.pdf';
		header("Location: ".$url_archivo);

	}

	function servicio__generar_certificado_eval()
	{
		$datos = array('evaluaciones'=>$this->evaluaciones, 'evaluador'=> $this->evaluador);
		$pdf = new Certificado_evaluacion($datos);
		$pdf->mostrar();
	}

	//-----------------------------------------------------------------------------------
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function servicio__ver_pdf_informe()
	{
		$params = toba::memoria()->get_parametros();
		$tipos = array('0' => 'pi', 'D' => 'pdts');
		if( ! array_key_exists($params['tipo'], $tipos)) die("El tipo de informe seleccionado ({$params['tipo']}) no está entre los disponibles");
		$tipo_proyecto = $tipos[$params['tipo']];
		if( ! isset($params['id_informe'])) die ('No se recibió un ID de informe para generar su reporte');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/proyectos/informes/reporte_informe_proyecto.php' , 'sap' );
		
		//Detalles del informe
		$informe = toba::consulta_php('co_proyectos_informes')->get_reporte_informe($tipo_proyecto, $params['id_informe']);

		$reporte = new Reporte_informe_proyecto($informe);
		
		$reporte->mostrar();
	}
	
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		$usuario = toba::usuario()->get_id();
		
		$this->evaluaciones = toba::consulta_php('co_proyectos')->get_evaluaciones_realizadas(array('nro_documento_evaluador'=>$usuario));

		$this->evaluador = toba::consulta_php('co_proyectos')->get_detalles_evaluador($usuario);

		
		// Si la persona existe, y además evaluó la misma cantidad de proyectos que tenia asignado para evaluar
		if (!($this->evaluador && toba::consulta_php('co_proyectos')->ha_finalizado_evaluaciones($usuario))) {
			$this->pantalla()->eliminar_evento('generar_comp_evaluaciones');
		}

		//El campo "puede_descargar_certificado" lo habilita alguna persona de la Secretaría cuando recibe un mail del evaluador con el comprobante de todas las evaluaciones realizadas.
		if( ! toba::consulta_php('co_proyectos')->puede_descargar_certificado($usuario)){
			$this->pantalla()->eliminar_evento('generar_certificado_eval');
		}
			
	}

}
?>