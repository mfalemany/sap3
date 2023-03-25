<?php
class ci_comunicacion extends sap_ci
{
      public $s__convocatoria;      
      public $s__comunicacion;
      public $params_convocatorias;
      protected $path;
      protected $ruta_base;
      protected $url_base;
   
    function conf()
    {
		$path = toba::proyecto()->get_www();
        $this->path = $path['path']."img/plantillas_certificados/";

        $this->ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
        $this->url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
    }


	function ini()
	{
        $this->dep('cuadro')->eliminar_evento('eliminar');

	}
	
	//-----------------------------------------------------------------------------------
	//---- cuadro ------------------------------------------------- ----------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(sap_ei_cuadro $cuadro)
	{
		$datos = toba::consulta_php('co_convocatorias')->get_convocatoriasVigentes('BECARIOS');
		$cuadro->set_datos($datos);
	}

	function evt__cuadro__seleccion($seleccion)
	{
		$this->s__convocatoria = $seleccion['id'];
		$this->set_pantalla('p_comunicaciones');
	}


	
	//-----------------------------------------------------------------------------------
	//---- cuadro_historial -------------------------------------- ----------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_certificados(sap_ei_cuadro $cuadro)
	{
		$cuadro->set_titulo('Historial de comunicaciones presentadas');
		$cuadro->agregar_columnas(array(array('titulo'=>'Convocatoria','clave'=>'nombre_convocatoria')));
		$cuadro->eliminar_columnas(array('area'));
		$cuadro->desactivar_modo_clave_segura();
		$filtro = array('nro_documento'=>toba::usuario()->get_id());
		$cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_reporte_certificados($filtro));
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	function evt__agregar()
	{
		$this->set_pantalla('p_comunic_edicion'); 
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_comunicaciones --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_comunicaciones(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$filtro = array('id_convocatoria'=>$this->s__convocatoria,'usuario_id'=>toba::usuario()->get_id());
		$cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_comunicaciones($filtro));
	}

	function evt__cuadro_comunicaciones__seleccion($seleccion)
	{
		$this->s__comunicacion = $seleccion;
		$this->dep('ci_comunicacion_edicion')->dep('datos')->cargar($seleccion);
		$this->set_pantalla('p_comunic_edicion');
            
	}

	function conf_evt__cuadro_comunicaciones__ver_extendido(toba_evento_usuario $evento,$fila){
		$params = explode('||', $evento->get_parametros());
		$id_comunicacion = $params[0];
		if( ! file_exists("{$this->ruta_base}/comunicaciones/$id_comunicacion/extendido.pdf")  ){
			$evento->ocultar();
		}else{
			$evento->mostrar();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function servicio__imprimir()
	{
		$comunicacion_id = toba::memoria()->get_dato('comunicacion_id');
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
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Location: {$this->url_base}/comunicaciones/{$params['id']}/extendido.pdf?t=".time());
		
	}

	function servicio__ver_certificado_participacion()
	{
		//obtengo los parametros del evento
		$params = toba::memoria()->get_parametros();
		$plantilla = $params['id_convocatoria']."_participacion";
		$this->mostrar_certificado($params,$plantilla);
	}

	function servicio__ver_certificado_seleccionado()
	{
		//obtengo los parametros del evento
		$params = toba::memoria()->get_parametros();
		$plantilla = $params['id_convocatoria']."_seleccionado";
		$this->mostrar_certificado($params,$plantilla);
	}

	function servicio__ver_certificado_mejor_trabajo()
	{
		//obtengo los parametros del evento
		$params = toba::memoria()->get_parametros();
		$plantilla = $params['id_convocatoria']."_mejor_trabajo";
		$this->mostrar_certificado($params,$plantilla);
	}

	//-----------------------------------------------------------------------------------
	//---- conf del CI --------- --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function ini__operacion()
	{
		$this->s__comunicacion = toba::memoria()->get_dato('comunicacion');

		if (isset($this->s__comunicacion) && ($this->s__comunicacion['id']!=0)){
		$this->dep('ci_comunicacion_edicion')->dep('datos')->cargar($this->s__comunicacion);
		$datos=$this->dep('ci_comunicacion_edicion')->dep('datos')->tabla('comunicacion')->get();
		$this->s__convocatoria=$datos['sap_convocatoria_id'];
		$this->set_pantalla('p_comunic_edicion');
		} 
	}

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__p_comunicaciones(toba_ei_pantalla $pantalla)
	{   
            $en_evaluacion=  toba::consulta_php('co_convocatorias')->esta_en_evaluacion($this->s__convocatoria);
            if ($en_evaluacion){
                $pantalla->eliminar_evento('agregar');
            }
	}


	function evt__p_comunicaciones__entrada()
	{
		/* Se eval?a si la convocatoria seleccionada est?cerrada. De ser as?
		   el usuario solo podr?modificar comunicaciones anteriores en estado 
		   de evaluacion, pero no podr?agregar nuevas comunicaciones. 
		*/
		$detalles = toba::consulta_php('co_convocatorias')->get_detalles_convocatoria($this->s__convocatoria);
		if(isset($detalles['fecha_hasta']) ) {
			if(date($detalles['fecha_hasta']) < date('Y-m-d')){
				$this->pantalla()->eliminar_evento('agregar');
			}
		}
		
		
	}



	/**
	 * Verifica si existe una plantilla para certificados que corresponda a la comunicaci? seleccionada. En caso de no existir la plantila (no se puede generar el certificado), oculta el evento.
	 * @param  toba_evento_usuario $evt  Evento producido
	 * @param  integer              $fila Fila del cuadro sobre la cual ocurri?el evento
	 * @return void                    
	 */
	function conf_evt__cu_certificados__ver_certificado_participacion(toba_evento_usuario $evt, $fila)
	{
		$this->configurar_eventos($evt,$fila,'_participacion');
	}

	

	function conf_evt__cu_certificados__ver_certificado_seleccionado(toba_evento_usuario $evt, $fila)
	{
		$parametros = explode('||',$evt->get_parametros());
		//si la evaluacion es "seleccionada"
		if($parametros[2] == 7){
			$this->configurar_eventos($evt,$fila,'_seleccionado');	
		}else{
			$evt->ocultar();
		}
		
	}

	

	function conf_evt__cu_certificados__ver_certificado_mejor_trabajo(toba_evento_usuario $evt, $fila)
	{
		$parametros = explode('||',$evt->get_parametros());
		//Si la comunicacion es "Mejor Trabajo"
		if($parametros[3] == 'S'){
			$this->configurar_eventos($evt,$fila,'_mejor_trabajo');	
		}else{
			$evt->ocultar();
		}
	}

	

	protected function configurar_eventos(&$evt,$fila,$tipo_certificado)
	{
		$params = explode('||',$evt->get_parametros());

		//obtengo el ID de la comunicacion seleccionada
		$id_comunicacion = $params[0];
		
		//Guardo en una variable de sesión para evitar sucesivas llamadas a la BD
		if (!isset($this->params_convocatorias[$params[1]])) {
			$this->params_convocatorias[$params[1]] = toba::consulta_php('co_comunicaciones')->get_convocatoria($params[1]);
		}

		//si no existe el archivo de plantilla para esta convocatoria, se elimina el evento
		//La plantilla se nombra as? [id_convocatoria]_[tipo_certificado].png
		$archivo_certificado = $this->path.$params['1'].$tipo_certificado.'.png';
		$custom_params       = json_decode($this->params_convocatorias[$params[1]]['custom_params'], true); 

		if (file_exists($archivo_certificado) && $custom_params['becario_ve_certificados'] == 'S' && $params[4] == 'S') {
			$evt->mostrar();
		} else {
			$evt->ocultar();
		}
	}

	protected function mostrar_certificado($params,$plantilla)
	{
		//busco los detalles de la comunicaci? elegida
		$datos = toba::consulta_php('co_comunicaciones')->get_reporte_certificados(array('id_comunicacion'=>$params['id_comunicacion']));
		$datos = array_shift($datos);

		//genero el PDF y lo muestro
		$pdf = new certificado_comunicaciones($datos,$plantilla);
		$pdf->mostrar();
	}

}
?>