<?php
class ci_edicion_proyecto extends sap_ci
{
	function conf()
	{
		//se asigna el template a la pantalla
		$template = file_get_contents(__DIR__.'/template_proyecto.php');
		//$this->pantalla()->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- form_proyecto ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_proyecto(sap_ei_formulario $form)
	{
		if ($this->get_datos()->esta_cargada()) {
			$form->set_datos($this->get_datos('datos','sap_proyectos')->get());
			$form->desactivar_efs(array('sap_dependencia_id','tipo'));
			$form->set_solo_lectura(array('codigo'));
		}else{
			if($form->existe_ef('director')){
				$form->desactivar_efs(array('director'));	
			}
			$anio = date("Y") + 1;
			$form->ef('tipo')->set_estado("0");
			$form->ef('fecha_desde')->set_estado($anio."-01-01");
			$form->ef('fecha_hasta')->set_estado(($anio+3)."-12-31");
			$form->ef('entidad_financiadora')->set_estado("Sec. Gral. de Ciencia y Técnica - Universidad Nacional del Nordeste");
			
		}
	}

	function evt__form_proyecto__modificacion($datos)
	{
		//Si no se asign?el c?igo a mano, se auto-genera uno
		$datos['director'] = toba::consulta_php('co_personas')->get_ayn($datos['nro_documento_dir']);
		
		/* =============== Procesamiento del EF tipo Upload ====================== */
		
		$ext = explode(".",$datos['archivo_proyecto']['name']);
		$ext = end($ext);
		$ruta = 'proyectos/'.$datos['codigo'].'/';
		$efs_archivos = array(array('ef'          => 'archivo_proyecto',
							  		'descripcion' => 'Contenido comprimido de la documentacion del proyecto',
							  		'nombre'      => 'Contenido.'.$ext)
							);

		toba::consulta_php('helper_archivos')->procesar_campos($efs_archivos,$datos,$ruta);
		/* =============== Procesamiento del EF tipo Upload ====================== */
		if(!$datos['codigo']){
			unset($datos['codigo']);
		}
		$this->get_datos('datos','sap_proyectos')->set($datos);
	}
	//-----------------------------------------------------------------------------------
	//---- ml_integrantes ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_integrantes(sap_ei_formulario_ml $form_ml)
	{
		$proyecto = $datos = $this->get_datos('datos','sap_proyectos')->get();
		//Fechas del proyecto
		$desde = date('d-m-Y',strtotime($proyecto['fecha_desde']));
		$hasta = date('d-m-Y',strtotime($proyecto['fecha_hasta']));
		
		//título del formulario
		$titulo = sprintf('Integrantes del proyecto: %s (desde el %s al %s)',$proyecto['codigo'], $desde, $hasta);
		$form_ml->set_titulo($titulo);

		$datos = $this->get_datos('datos','proyecto_integrante')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}

	}

	function evt__ml_integrantes__modificacion($datos)
	{
		$this->get_datos('datos','proyecto_integrante')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//-------------- DATOS --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($relacion = 'datos',$tabla = NULL)
	{
		return $this->controlador()->get_datos($relacion,$tabla);
	}
	

	function extender_objeto_js()
	{
		$ml_integrantes_js = $this->dep('ml_integrantes')->objeto_js;
		$form_proyecto_js = $this->dep('form_proyecto')->objeto_js;
		echo "
			const desde = console.log({$form_proyecto_js}.ef('fecha_desde').get_estado());
			const hasta = console.log({$form_proyecto_js}.ef('fecha_hasta').get_estado());
			const filas = {$ml_integrantes_js}.filas();


			{$ml_integrantes_js}.boton_agregar().addEventListener('click', elem => {
				console.log(filas);
				console.log(filas.length);
				{$ml_integrantes_js}.ef('fecha_desde').ir_a_fila(filas[filas.length]).set_estado('2021-12-18');
				{$ml_integrantes_js}.ef('fecha_hasta').ir_a_fila(filas[filas.length]).set_estado(hasta);
				
			})";
	}

	function ajax__get_detalles_director($nro_documento, toba_ajax_respuesta $respuesta){
		$resultado = toba::consulta_php('co_personas')->get_detalles_director($nro_documento);
		$respuesta->set($resultado);

	}

	function get_ayn($nro_documento){
		return toba::consulta_php('co_personas')->get_ayn($nro_documento);
	}
}

?>