<?php
class ci_personas extends sap_ci
{
	protected $s__filtro;
	protected $s__cat_incentivos_cargadas;
	protected $s__cat_conicet_cargada;
	/*function conf(){
		$entidades = array(
			array('carpeta' => 'activ_docente', 'tabla' => 'be_antec_activ_docentes'),
			array('carpeta' => 'conocimiento_idiomas', 'tabla' => 'be_antec_conoc_idiomas'),
			array('carpeta' => 'cursos_perfec_aprob', 'tabla' => 'be_antec_cursos_perfec_aprob'),
			array('carpeta' => 'becas_obtenidas', 'tabla' => 'be_antec_becas_obtenidas'),
			array('carpeta' => 'estudios_afines', 'tabla' => 'be_antec_estudios_afines'),
			array('carpeta' => 'part_dict_cursos', 'tabla' => 'be_antec_particip_dict_cursos'),
			array('carpeta' => 'trabajos_publicados', 'tabla' => 'be_antec_trabajos_publicados')
		);
		foreach ($entidades as $entidad) {
			$sql = "SELECT * FROM {$entidad['tabla']}";
			$registros = toba::db()->consultar($sql);
			if(count($registros)){
				foreach ($registros as $registro) {
					if( ! file_exists("/mnt/datos/cyt/doc_probatoria/{$registro['nro_documento']}/{$entidad['carpeta']}/{$registro['doc_probatoria']}")){
						$sql = "UPDATE {$entidad['tabla']} SET doc_probatoria = null WHERE nro_documento = '{$registro['nro_documento']}' AND doc_probatoria = '{$registro['doc_probatoria']}'";
						toba::db()->ejecutar($sql);
					}
				}
			}
		}
	}*/

	/**
	 * Se usa para poder usar la variable de sesión desde un controlador dependiente
	 * @param array
	 */
	public function set_cat_incentivos_cargadas($datos)
	{
		$this->s__cat_incentivos_cargadas = $datos;
	}

	/**
	 * Se usa para poder usar la variable de sesión desde un controlador dependiente
	 * @param string
	 */
	public function set_cat_conicet_cargada($cat_conicet)
	{
		$this->s__cat_conicet_cargada = $cat_conicet;
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_personas --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_personas(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_personas__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_personas__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_personas --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_personas(sap_ei_cuadro $cuadro)
	{
		if(isset($this->s__filtro)){
			$cuadro->set_datos(toba::consulta_php('co_personas')->get_personas($this->s__filtro));	
		}else{
			$cuadro->set_eof_mensaje('Debe establecer algún criterio de filtro para ver resultados.');
		}
	}

	function evt__cuadro_personas__seleccion($seleccion)
	{
		$this->dep('personas')->cargar($seleccion);

		//Guardo en memoria la persona seleccionada (para la generación del cvar)
		$datos = $this->dep('personas')->tabla('personas')->get();
		toba::memoria()->set_dato('cuil_seleccionado', $datos['cuil']);
		
		$this->set_pantalla('pant_edicion');
	}

	
	
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__guardar()
	{
		try{
			$this->dep('personas')->sincronizar();
			
			$this->log_cambios_categoria_incentivos();
			$this->log_cambios_categoria_conicet();

			$this->dep('personas')->resetear();
			toba::memoria()->eliminar_dato('cuil_seleccionado');
			$this->set_pantalla('pant_seleccion');	
		}catch(toba_error_db $e){
			switch ($e->get_sqlstate()) {
				case 'db_23505':
					toba::notificacion()->agregar('Ocurrió un error al intentar guardar. Posiblemente la persona ingresada ya se encuentra registrada en el sistema');
					break;
				case 'db_23503':
					toba::notificacion()->agregar($e->get_mensaje_motor());
					break;
				default:
					toba::notificacion()->agregar($e->get_sqlstate());
					break;
			}
		}
	}

	function evt__cancelar()
	{
		$this->dep('personas')->resetear();
		toba::memoria()->eliminar_dato('cuil_seleccionado');
		$this->set_pantalla('pant_seleccion');
	}

	function evt__agregar()
	{
		$this->dep('personas')->resetear();
		toba::memoria()->eliminar_dato('cuil_seleccionado');
		$this->set_pantalla('pant_edicion');
	}

	function evt__eliminar()
	{
		$this->dep('personas')->eliminar_todo();
		$this->dep('personas')->sincronizar();
		$this->dep('personas')->resetear();
		toba::memoria()->eliminar_dato('cuil_seleccionado');
		$this->set_pantalla('pant_seleccion');
	}

	private function log_cambios_categoria_incentivos()
	{
		$persona = $this->dep('personas')->tabla('personas')->get();
		$persona = sprintf('(%s) %s, %s', $persona['nro_documento'], $persona['apellido'], $persona['nombres']);

		$categorias_cargadas = count($this->s__cat_incentivos_cargadas) ? $this->s__cat_incentivos_cargadas : [];

		$categorias_guardadas = $this->dep('personas')->tabla('cat_incentivos')->get_filas();
		if (count($categorias_guardadas)) {
			$categorias_guardadas = array_column($categorias_guardadas, 'categoria', 'convocatoria');
		} else {
			$categorias_guardadas = [];
		}


		if (count($categorias_cargadas) > 0 && count($categorias_cargadas) > count($categorias_guardadas)) {
			// Entonces, se borró algo
			$diferencias = array_diff($categorias_cargadas, $categorias_guardadas);
			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha hecho las siguientes modificaciones a ' . $persona . ': Eliminó ' .json_encode($diferencias),'cambios_cat_incentivos');
			return;
		}

		$diferencias = array_diff($categorias_guardadas, $categorias_cargadas);

		if (count($diferencias)) {
			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha hecho las siguientes modificaciones a ' . $persona . ': Agregó ' .json_encode($diferencias),'cambios_cat_incentivos');
		}
	}

	private function log_cambios_categoria_conicet()
	{
		$persona = $this->dep('personas')->tabla('personas')->get();
		$persona = sprintf('(%s) %s, %s', $persona['nro_documento'], $persona['apellido'], $persona['nombres']);

		$categoria_conicet_cargada  = $this->s__cat_conicet_cargada; 
		$categoria_conicet_guardada = $this->dep('personas')->tabla('cat_conicet_persona')->get();

		if (!$categoria_conicet_cargada && !$categoria_conicet_guardada) {
			return;
		}

		if ($categoria_conicet_cargada && !$categoria_conicet_guardada) {
			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha hecho las siguientes modificaciones a ' . $persona . ': Eliminó la categoría conicet ' .json_encode($categoria_conicet_cargada),'cambios_cat_conicet');
		}

		if (!$categoria_conicet_cargada && $categoria_conicet_guardada) {
			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha hecho las siguientes modificaciones a ' . $persona . ': Agregó la categoría conicet ' .json_encode($categoria_conicet_cargada),'cambios_cat_conicet');
		}

		$diferencia = array_diff($categoria_conicet_cargada, $categoria_conicet_guardada);
		if (count($diferencia)) {
			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha hecho las siguientes modificaciones a ' . $persona . ': Modificó la categoría conicet de esto: --> ' . json_encode($categoria_conicet_cargada) . ' a esto --> ' . json_encode($categoria_conicet_guardada),'cambios_cat_conicet');
		}


	}

	public function ajax__set_clave_temporal($datos, toba_ajax_respuesta $respuesta)
	{
		$datos = $this->dep('personas')->tabla('personas')->get();
		if (!$datos['nro_documento']) {
			toba::notificacion()->warning('No hay un usuario seleccionado');
		}
		$respuesta->set(toba::consulta_php('co_usuarios')->set_clave_temporal($datos['nro_documento']));
	}

	public function ajax__reset_clave_temporal($datos, toba_ajax_respuesta $respuesta)
	{
		$datos = $this->dep('personas')->tabla('personas')->get();
		if (!$datos['nro_documento']) {
			toba::notificacion()->warning('No hay un usuario seleccionado');
		}
		$respuesta->set(toba::consulta_php('co_usuarios')->reset_clave_temporal($datos['nro_documento']));
	}

	/**
	 * Servicio encargado de llamar al generador de PDF del CVAr
	 */
	function servicio__generar_cvar()
	{
		if (!toba::memoria()->existe_dato('cuil_seleccionado') || strlen(toba::memoria()->get_dato('cuil_seleccionado')) == 0) {
			die('No se encontró un CUIL para obtener datos de CVAr');
		}
		$cuil = str_replace(array(' ','-'), '', toba::memoria()->get_dato('cuil_seleccionado'));
		$cvar = new Cvar_getter($cuil);
		$datos = $cvar->get_datos('array');
		
		//$datos = json_decode(file_get_contents(toba::proyecto()->get_www()['path'].'dl.json'),true);
		//$datos = $datos['curriculum'];
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/cvar/CVFromCvar.php' , 'sap' );

		$reporte = new CVFromCvar($datos);
		$reporte->mostrar();
	}

	
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		$clave_temporal = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('clave_temporal');
		echo "
		

		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__resetear_clave_temporal = function()
		{
			{$this->objeto_js}.ajax('set_clave_temporal',null, this, setClaveTemporal);
			return false;

			// llamo al servidor para resetear la clave
			{$this->objeto_js}.ajax('set_clave_temporal',null, this, setClaveTemporal);

			// Este return previene el comportamiento por defecto (la recarga)
			return false;
		}

		function setClaveTemporal(){
			alert('A partir de este momento puede acceder con la clave temporal: " . $clave_temporal . "')
			{$this->objeto_js}.desactivar_boton('resetear_clave_temporal');
			notificacion.mostrar_ventana_modal('<h1 style=\'color:red;\'>Muy Importante!</h1>', '<h2>No cierre esta ventana hasta que este cartel desaparezca.</h3>', '600px', 'overlay(true)');
			setTimeout(function(){
				{$this->objeto_js}.ajax('reset_clave_temporal',null, this, notificarFinCambioClave);	
			}, 20000);

		}

		function notificarFinCambioClave(){
			toba.fin_aguardar();
			notificacion.limpiar();
			overlay(false);
			{$this->objeto_js}.activar_boton('resetear_clave_temporal');
		}
		";
	}

}
?>