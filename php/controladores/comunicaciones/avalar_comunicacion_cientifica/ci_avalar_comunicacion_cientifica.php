<?php
class ci_avalar_comunicacion_cientifica extends sap_ci
{
	//AGREGAR A PRODUCCION
/*ALTER TABLE public.sap_comunicacion
  DROP COLUMN e_mail;
ALTER TABLE public.sap_comunicacion
  DROP COLUMN telefono;
ALTER TABLE public.sap_comunicacion
  ADD COLUMN estado_aval boolean DEFAULT NULL;*/

	protected $filtro;

	function conf()
	{
		if(! $this->soy_admin()){
			$this->filtro['nro_documento_dir'] = toba::usuario()->get_id();
		}
		if( ! isset($this->filtro['id_convocatoria'])){
			$this->filtro['id_convocatoria'] = toba::consulta_php('co_convocatorias')->get_id_ultima_convocatoria('BECARIOS');
		}
	}
	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(sap_ei_formulario $form)
	{
		if(isset($this->filtro)){
			$form->set_datos($this->filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cu_avales --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_avales(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_comunicaciones_pendientes_aval($this->filtro));
	}

	function evt__cu_avales__avalar($seleccion)
	{
		try {
			toba::consulta_php('co_comunicaciones')->otorgar_aval($seleccion['id']);
			toba::notificacion()->info('Se ha registrado su aval para la comunicaci�n.');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurri� un error al otorgar el aval');
		}

	}

	function conf_evt__cu_avales__avalar(toba_evento_usuario $evento, $fila)
	{

		$params = explode('||',$evento->get_parametros());
		//params[1] es el campo "aval", que si fue avalado tiene una fecha (sino, es null)
		if(! (isset($params[1]) && $params[1])) {
			$evento->activar();
		}else{
			$evento->desactivar();
		}
	}

	function conf_evt__cu_avales__ver_extendido(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		
		$id = $params[0];
		$ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		if(file_exists($ruta_base.'/comunicaciones/'.$id.'/extendido.pdf')){
			$evento->activar();
		}else{
			$evento->desactivar();
		}
	}

	

	//-----------------------------------------------------------------------------------
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function servicio__imprimir()
	{
		$params = toba::memoria()->get_parametros();
		if( ! $params['id']) throw new toba_error('No se recibi� como par�metro la comunicaci�n que intenta imprimir');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/comunicaciones/formulario_comunicacion.php' , 'sap' );
		
		$comunicacion = toba::consulta_php('co_comunicaciones')->get_detalle_comunicacion($params['id']);
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

}
?>