<?php
class ci_comunicacion_filtro extends sap_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_filtro(sap_ei_formulario $filtro)
	{
		if ( isset($this->s__filtro ) ) {
			$filtro->set_datos($this->s__filtro);
		}

	}

	function evt__form_filtro__filtrar($datos)
	{
			$this->s__filtro = $datos;
	}

	function evt__form_filtro__cancelar()
	{
			unset($this->s__filtro);

	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__cuadro(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		if ( isset( $this->s__filtro ) ) {
			$datos = toba::consulta_php('co_comunicaciones')->get_comunicaciones($this->s__filtro);
			$cuadro->set_datos($datos);
		}
	}

	function evt__cuadro__seleccion($seleccion)
	{
			toba::memoria()->set_dato('comunicacion',$seleccion);
			toba::vinculador()->navegar_a(toba::proyecto()->get_id(), 3514);
	}

	function evt__cuadro__abrir($seleccion)
	{
		if(toba::db()->ejecutar("UPDATE sap_comunicacion SET estado = 'A' WHERE id = ".quote($seleccion['id']))){
			toba::notificacion()->agregar('Se ha abierto la comunicación correctamente.','info');
		}else{
			toba::notificacion()->agregar('Ocurrió un error al intentar abrir la comunicación');
		}
	}

	function conf_evt__cuadro__ver_extendido(toba_evento_usuario $evento,$fila){
		$params = explode('||', $evento->get_parametros());
		$id_comunicacion = $params[0];
		$ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		if( ! file_exists("{$ruta_base}/comunicaciones/$id_comunicacion/extendido.pdf")  ){
			$evento->ocultar();
		}else{
			$evento->mostrar();
		}
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



	
}
?>