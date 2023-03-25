<?php
class ci_grupos extends sap_ci
{
	protected $s__filtro;
	protected $s__convocatoria;
	protected $s__seleccion;
	protected $es_admin;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf()
	{
		unset($this->s__seleccion);
		$usuario = toba::usuario()->get_id();
		/* Valido que el usuario logueado tenga:
			- Al menos un cargo docente (mapuche) con mayor dedicacion (semi o exclusiva) o bien, ser investigador de conicet (lista de Damian)
			- Categoría I, II o III de incentivos
			- Un solo grupo (no puede dirigir mas de uno)
		*/
			/*var_dump(toba::consulta_php('co_personas')->es_docente($usuario));
			var_dump(toba::consulta_php('co_personas')->tiene_mayor_dedicacion($usuario));
			var_dump(in_array(toba::consulta_php('co_personas')->get_categoria_incentivos($usuario),array(1,2,3)));
			var_dump(toba::consulta_php('co_personas')->get_categoria_incentivos($usuario));*/
		$this->es_admin = in_array('admin',toba::usuario()->get_perfiles_funcionales());
		if( ! $this->es_admin){
			//Si no es administrador no puede ver el filtro
			//$this->pantalla('pant_seleccion')->eliminar_dep('filtro_grupos');
			if($this->pantalla('pant_seleccion')->existe_dependencia('filtro_grupos')){
				 $this->pantalla('pant_seleccion')->eliminar_dep('filtro_grupos');
			}

			if(! toba::consulta_php('co_grupos')->puede_coordinar($usuario)){
				$mensaje_error = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('mensaje_error_no_puede_cordinar_grupos');
				throw new toba_error('<span style="font-weight:bolder;">'.$mensaje_error.'</span>');
			}

		}
		
		//NO puede dirigir mas de un grupo
		if(toba::consulta_php('co_grupos')->es_coordinador($usuario)){
			$this->pantalla()->eliminar_evento('crear_grupo');
		}
		//Si no hay convocatoria abierta, no puede crear un grupo
		if(! toba::consulta_php('co_convocatorias')->get_convocatorias_vigentes_equipos()){
			$this->pantalla()->eliminar_evento('crear_grupo');	
		}
	}

	/**
	 * Determina si la ultima evaluacion realizada a un informe de grupo es "En proceso"
	 */
	function esta_en_proceso_evaluacion($id_grupo)
	{

		//obtengo la evaluaci? del ?ltimo informe presentado
		$ultima_eval = toba::consulta_php('co_grupos')->get_evaluacion_ultimo_informe($id_grupo);
		//si su ultima evaluacion no est?"en proceso", no puede modificar nada
		return ($ultima_eval == 'P');
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__crear_grupo()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_grupos --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_grupos(sap_ei_cuadro $cuadro)
	{
		if (!$this->soy_admin()) {
			$cuadro->eliminar_columnas(['id_grupo','coordinador']);
		}
		$cuadro->desactivar_modo_clave_segura();
		$filtro_admin = (isset($this->s__filtro)) ? $this->s__filtro : array();
		$filtro = ( ! $this->es_admin) ? array('nro_documento_coordinador'=>toba::usuario()->get_id()) : $filtro_admin;
		$datos = toba::consulta_php('co_grupos')->get_grupos($filtro);
		$cuadro->set_datos($datos) ;
	}

	function evt__cu_grupos__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	function evt__cu_grupos__ver_evaluaciones($seleccion)
	{
		$this->dep('ci_ver_evaluaciones')->set_seleccion($seleccion);
		$this->set_pantalla('pant_evaluaciones');
	}
	
	function conf_evt__cu_grupos__comprobante(toba_evento_usuario $evento, $fila)
	{
		//Si no existe una convocatoria de grupos se elimina el evento
		$conv = toba::consulta_php('co_convocatorias')->get_convocatorias_vigentes_equipos();
		$id_grupo = $evento->get_parametros();
		//conservo el ID de la convocatoria vigente
		if(count($conv)){
			$this->s__convocatoria = $conv[0]['id'];		
		}
		
		
		//Si el grupo no est?inscripto, debe ver boton de "inscribir"
		if(toba::consulta_php('co_grupos')->esta_inscripto($id_grupo)){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	function conf_evt__cu_grupos__ver_evaluaciones(toba_evento_usuario $evento, $fila)
	{
		$id_grupo = $evento->get_parametros();
		$evaluaciones = toba::consulta_php('co_grupos')->tiene_evaluaciones($id_grupo);
		
		if (!$evaluaciones) {
			$evento->ocultar();
		} else {
			if ($this->soy_admin()) {
				$evento->mostrar();
			} else {
				$evento->ocultar();	
			}
		}

	}

	//-----------------------------------------------------------------------------------
	//---- filtro_grupos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_grupos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_grupos__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_grupos__cancelar()
	{
		unset($this->s__filtro);
	}

	function servicio__generar_comprobante(){
		$params = toba::memoria()->get_parametros();
		//$clave = toba_ei_cuadro::recuperar_clave_fila('2948',$params['fila']);
		$this->generar_comprobante($params);
		//validar si existe el archivo, sino, hay que generarlo.
	}

	function servicio__ver_plan_trabajo()
	{
		$params                          = toba::memoria()->get_parametros();
		$datos                           = toba::consulta_php('co_grupos')->get_plan_trabajo($params['id_grupo'], $params['id_convocatoria']);
		$datos['direccion_mail_grupos']  = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('direccion_mail_grupos');
		
		//Carga de clase generadora del PDF
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/grupos/Plan_trabajo_grupo.php' , 'sap' );

		$reporte = new Plan_trabajo_grupo($datos);
		$reporte->mostrar();

	}

	function generar_comprobante($params)
	{
		if(!count($params) || !isset($params['id_grupo'])){
			return;
		}
		$datos                         = toba::consulta_php('co_grupos')->get_detalles_grupo($params['id_grupo']);
		$datos['integrantes']          = toba::consulta_php('co_grupos')->get_integrantes($params['id_grupo']);
		$datos['lineas_investigacion'] = toba::consulta_php('co_grupos')->get_lineas_investigacion($params['id_grupo']);
		$reporte = new Comprobante_insc_grupos($datos);
		$reporte->mostrar();

	}

	//-----------------------------------------------------------------------------------
	//---- Funciones Auxiliares ---------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

}
?>