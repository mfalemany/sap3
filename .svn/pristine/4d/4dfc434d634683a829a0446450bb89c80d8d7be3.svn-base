<?php
class ci_consultar_recibos extends sap_ci
{
	protected $s__filtro;
	
	function conf()
	{
		if( ! $this->soy_admin()){
			if($this->pantalla()->existe_dependencia('filtro')){
				$this->pantalla()->eliminar_dep('filtro');
			}
		}
	}
	//-----------------------------------------------------------------------------------
	//---- cu_recibos -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_recibos(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		if( ! $this->soy_admin()){
			$this->s__filtro['becario'] = toba::usuario()->get_id();
		}
		$filtro = (isset($this->s__filtro) && $this->s__filtro) ? $this->s__filtro : array();
		$cuadro->set_datos(toba::consulta_php('co_becas')->get_recibos_sueldo($filtro));
	}

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro) && $this->s__filtro){
			$form->set_datos($this->s__filtro);
		}
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
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function servicio__ver_recibo()
	{
		$params = toba::memoria()->get_parametros();
		$url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$fecha = new Datetime($params['fecha_emision']);
		$documento = sprintf('%s_%u_%s.pdf',$params['nro_documento'],$params['id_recibo'],$fecha->format('d-m-Y'));
		header('Location: ' . $url_base . "/recibos_sueldo/" . $documento );
	}
}

?>