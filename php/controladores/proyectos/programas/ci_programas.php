<?php
class ci_programas extends sap_ci
{

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__eliminar()
	{
		$this->get_datos()->eliminar();
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__guardar()
	{
		$this->get_datos()->sincronizar();
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_programas -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_programas(sap_ei_cuadro $cuadro)
	{
		$es_admin = in_array('admin',toba::usuario()->get_perfiles_funcionales());
		$filtro = (!$es_admin) ? array('nro_documento_dir'=>toba::usuario()->get_id()) : array();
		$cuadro->desactivar_modo_clave_segura();
		$cuadro->set_datos(toba::consulta_php('co_programas')->get_programas($filtro));	
		
	}

	function evt__cu_programas__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	function conf_evt__cu_programas__ver_reporte(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		$datos = toba::consulta_php('co_programas')->
			get_reporte_programa(array('codigo'=>$params[0]));
		$evento->mostrar();
		
	}

	//-----------------------------------------------------------------------------------
	//---- form_programa ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_programa(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('sap_programas')->get();
		if($datos){
			$form->set_datos($datos);
			$form->set_solo_lectura(array('codigo'));
		}

		$es_admin = in_array('admin', toba::usuario()->get_perfiles_funcionales());
		if(!$es_admin){
			$form->desactivar_efs(
				array('codigo','nro_documento_dir','fecha_desde','fecha_hasta','resol_acreditacion','archivo_programa')
			);
		}
		

	}

	function evt__form_programa__modificacion($datos)
	{
		if(!isset($datos['nro_documento_dir'])){
			$datos['nro_documento_dir'] = toba::usuario()->get_id();
		}
		if(!isset($datos['fecha_desde'])){
			$datos['fecha_desde'] = (date('Y')+1)."-01-01";
			$datos['fecha_hasta'] = (date('Y')+5)."-12-31";
		}
		if(!$this->get_datos('sap_programas')->get()){
			$datos['codigo'] = $this->generar_codigo($datos['id_dependencia']);
		}
		// =============== Procesamiento del EF tipo Upload ====================== 
		if(isset($datos['archivo_programa']) && $datos['archivo_programa']){
			$ext = explode(".",$datos['archivo_programa']['name']);
			$ext = end($ext);
			$ruta = 'proyectos/'.$datos['codigo'].'/';
			$efs_archivos = array(array('ef'          => 'archivo_programa',
								  		'descripcion' => 'Contenido comprimido de la documentacion del programa',
								  		'nombre'      => 'Contenido.'.$ext)
								);
			toba::consulta_php('helper_archivos')->procesar_campos($efs_archivos,$datos,$ruta);
		}

		// =============== Procesamiento del EF tipo Upload ====================== 
		$this->get_datos('sap_programas')->set($datos);
	}

	function generar_codigo($id_ua)
	{
		$ultimo_id = toba::consulta_php('co_programas')->get_ultimo_id($id_ua);
		$codigo_ua = toba::consulta_php('co_tablas_basicas')->get_letra_dependencia($id_ua);
		//si existe ultimo id, le sumo uno y lo relleno con ceros a la izquierda (siempre logitud 2)
		$id = (isset($ultimo_id['id'])) ? str_pad( ($ultimo_id['id']+1), 2,'0',STR_PAD_LEFT) : '01';
		//C�digo con formato [a�o de dos digitos][unidad_academica][letra P][dos numeros]
		//18AP01
		return substr(date('Y'),2,2).strtoupper($codigo_ua).'P'.$id;
	}


	function get_datos($tabla=NULL)
	{
		if($tabla){
			return $this->dep('datos')->tabla($tabla);
		}else{
			return $this->dep('datos');
		}
	}

	function ajax__get_ayn($nro_documento, toba_ajax_respuesta $respuesta)
	{
		if(!$nro_documento){
			return '';
		}
		$respuesta->set($this->get_ayn($nro_documento));
	}
	function get_ayn($nro_documento)
	{
		return toba::consulta_php('co_personas')->get_ayn($nro_documento);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_proyectos -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_proyectos(sap_ei_formulario_ml $form_ml)
	{
		if($this->get_datos()->esta_cargada()){
			$form_ml->set_datos($this->get_datos('sap_programas_proyectos')->get_filas());
		}
	}

	function evt__ml_proyectos__modificacion($datos)
	{
		$this->get_datos('sap_programas_proyectos')->procesar_filas($datos);
	}

	function get_descripcion_proyecto($id=NULL){
		return toba::consulta_php('co_proyectos')->get_proyecto($id);
	}


	function extender_objeto_js()
	{
		echo "";
	}

	// SERVICIO QUE GENERA EL PDF DEL FORMULARIO DEL PROGRAMA
	function servicio__ver_reporte()
	{
		$seleccion = toba::memoria()->get_parametros();
		$datos = toba::consulta_php('co_programas')->get_reporte_programa($seleccion);
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/proyectos/programas/reporte_programa.php' , 'sap' );
		
		$reporte_programa = new Reporte_programa($datos);
		$reporte_programa->mostrar();

	}
	

}
?>