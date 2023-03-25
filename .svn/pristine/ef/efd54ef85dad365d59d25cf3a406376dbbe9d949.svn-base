<?php
class ci_evaluar_comunicacion extends sap_ci
{
    protected $s__filtro;
    protected $ruta_base;
    protected $soy_admin;

    function conf()
    {
    	$this->ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
    	$this->soy_admin = $this->soy_admin();
    }

   	//-----------------------------------------------------------------------------------
    //---- PANTALLA SELECCION -----------------------------------------------------------
    //-----------------------------------------------------------------------------------	

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(sap_ei_formulario $form)
	{
		$id_convocatoria = toba::consulta_php('co_convocatorias')->get_id_ultima_convocatoria('BECARIOS');
		//Si no es admin, no puede cambiar el filtro de convocatoria (evita que se evalúen comunicaciones anteriores).
		if( ! $this->soy_admin()){
			$filtro = array('id_convocatoria'=>$id_convocatoria);
			$form->set_solo_lectura(array('id_convocatoria'));
		}else{
			$filtro = array();
		}
		$this->s__filtro = (isset($this->s__filtro)) ? array_merge($this->s__filtro,$filtro) : $filtro;
		$form->set_datos($this->s__filtro);
		
	}

	function evt__filtro__filtrar($datos)
	{
        $this->s__filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
        unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cu_comunicaciones ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_comunicaciones(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$this->s__filtro['estado'] = 'C'; //Solo las que ya fueron cerradas
		$this->s__filtro['estado_aval'] = 'A'; //Solo las que ya fueron avaladas
        $cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_comunicaciones($this->s__filtro));
	}

	function evt__cu_comunicaciones__evaluar($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_evaluacion');
	}

	function conf_evt__cu_comunicaciones__ver_extendido(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		$id = $params[0];
		
		if(file_exists($this->ruta_base.'/comunicaciones/'.$id.'/extendido.pdf')){
			$evento->activar();
		}else{
			$evento->desactivar();
		}
	}

	function evt__cu_comunicaciones__es_mejor_trabajo($seleccion)
	{
		try {
			$this->get_datos('comunicacion')->cargar($seleccion);
			$this->get_datos('comunicacion')->set(array('es_mejor_trabajo'=>'S'));
			$this->get_datos('comunicacion')->sincronizar();
			$this->get_datos('comunicacion')->resetear();
			toba::notificacion()->info('La comunicación ha sido marcada como "Mejor trabajo"');
		} catch (toba_error_db $e) {
			toba::notificacion()->agregar('Ocurrió un error al intentar marcar la comunicación como "Mejor Trabajo"' ,'error',$e->get_mensaje());			
		}
	}

	function conf_evt__cu_comunicaciones__es_mejor_trabajo(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		//$params[1] tiene el valor de 'es_mejor_trabajo'
		if ($params[1] == 'S') {
			$evento->desactivar();
		} else {
			$evento->activar();
		}

		$ultima_eval = toba::consulta_php('co_comunicaciones')->get_ultima_evaluacion($params[0]);

		if($ultima_eval['evaluacion'] == 'SELECCIONADA'){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}


	}

	 //-----------------------------------------------------------------------------------
    //---- PANTALLA EVALUACION -----------------------------------------------------------
    //-----------------------------------------------------------------------------------	
    //-----------------------------------------------------------------------------------
	//---- cu_evaluaciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_evaluaciones(sap_ei_cuadro $cuadro)
	{
		//En esta operación no se utiliza la eliminación de evaluaciones (el evento se define para otra operación)
		$cuadro->eliminar_evento('eliminar');
		$cuadro->desactivar_modo_clave_segura();
		$datos = $this->get_datos('comunicacion')->get();
		if($datos){
			$cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_evaluaciones_comunicacion($datos['id']));
		}
	}

    //-----------------------------------------------------------------------------------
    //---- frm_evaluacion ---------------------------------------------------------------
    //-----------------------------------------------------------------------------------
	function conf__frm_evaluacion(sap_ei_formulario $form)
	{
		//Si no soy admin, anulo la posibilidad de evaluar si esa opción no está disponible
		if (!$this->soy_admin()) {
			$comunicacion = $this->get_datos('comunicacion')->get();
			//Si la evaluacion de esta convocatoria no está habilitada, se quita el boton de guardar
	    	if (!toba::consulta_php('co_comunicaciones')->evaluacion_esta_abierta($comunicacion['sap_convocatoria_id'])) {
	    		$form->eliminar_evento('modificacion');
	    		$form->agregar_notificacion('La evaluación de comunicaciones, para esta convocatoria, no está habilitada.');
	    	}
		}
	}
	function evt__frm_evaluacion__modificacion($datos)
	{
		try {
			$datos['evaluadores'] = implode('/',$datos['evaluadores']);
			

			//Si la evaluación solicita modificaciones, hay que abrir la comunicacion y eliminar el aval
			/*   ============ ESTO SE SACÓ PORQUE NO PUEDEN SEGUIR EVALUANDO (despues de solicitar modificaciones) 
			$evals = toba::consulta_php('co_tablas_basicas')->get_evaluaciones();
			$evals = array_column($evals, 'nombre', 'id');
			if(strtolower($evals[$datos['sap_evaluacion_id']]) == 'a modificar'){
				$this->get_datos('comunicacion')->set(array('estado'=>'A','aval'=>null));
				
			}
			*/
			$this->get_datos('comunicacion_evaluacion')->nueva_fila($datos);
			$this->get_datos()->sincronizar();
			
			//Notifica al becario que recibió una evaluación
			//$this->enviar_mail_becario();
			
			toba::notificacion()->info('Evaluación registrada con éxito!');
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió un error','No se pudo guardar la evaluación realizada',$e->get_mensaje());
		}
	}

	function evt__frm_evaluacion__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = FALSE)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	
	//-----------------------------------------------------------------------------------
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function servicio__ver_resumen()
	{
		
		$params = toba::memoria()->get_parametros();
		$comunicacion_id = $params['id'];
		if( ! $comunicacion_id) throw new toba_error('No se recibió como parámetro la comunicación que intenta imprimir');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/comunicaciones/formulario_comunicacion.php' , 'sap' );
		
		$comunicacion = toba::consulta_php('co_comunicaciones')->get_detalle_comunicacion($comunicacion_id);
		$reporte = new Formulario_comunicacion($comunicacion);
		
		$reporte->mostrar();
	}

	function servicio__ver_extendido()
	{
		$params = toba::memoria()->get_parametros();
		$url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');

		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Location: $url_base/comunicaciones/{$params['id']}/extendido.pdf?t=".time());
		
	}

	function enviar_mail_becario()
	{
		$com = $this->get_datos('comunicacion')->get();
		if( ! $com) return FALSE;
		$destinatario = toba::consulta_php('co_personas')->get_persona($com['usuario_id']);

		$cuerpo = $this->armar_template(__DIR__ . "/template_correo.php",array());
		$mail = new toba_mail($destinatario['mail'],'Su comunicación Científica fue evaluada',$cuerpo);
		$mail->set_configuracion_smtp('instalacion');
		$mail->set_remitente('rrhhcytunne@gmail.com');
		$mail->set_html(TRUE);
		
		// Mando el e-mail
		try {
			$mail->ejecutar();
		} catch (toba_error $e) {
			toba::notificacion()->error('Error al intentar enviar la clave por mail. Consulte con el administrador del sistema.' . $e->get_mensaje());
			
		}
	}


}
?>