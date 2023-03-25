<?php
class ci_cargar_resoluciones_becas extends sap_ci
{
	protected $s__filtro;
	protected $s__ruta_base;
	protected $s__url_base;

	public function conf()
	{
		if (!isset($this->s__ruta_base)) {
			$this->s__ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		}
		if (!isset($this->s__url_base)) {
			$this->s__url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		}
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__seleccion()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__guardar()
	{
		//Solo se redirige a otra pantalla. La logica de subida del archivo está en el form
		$this->set_pantalla('pant_seleccion');
	}

	function evt__cancelar()
	{
		//Esto hace lo mismo que guardar(), pero no maneja datos, por lo que el archivo no se sube
		$this->set_pantalla('pant_seleccion');
	}
	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__filtro__modificacion($datos)
	{
		$this->s__filtro = $datos;
	}

	//-----------------------------------------------------------------------------------
	//---- form_resolucion --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_resolucion(sap_ei_formulario $form)
	{
		//Sin estos valores, no se puede continuar
		if ( ! (isset($this->s__filtro['id_convocatoria']) && isset($this->s__filtro['id_tipo_beca']))) {
			$this->set_pantalla('pant_seleccion');
		}

		$id_convocatoria      = $this->s__filtro['id_convocatoria'];
		$id_tipo_beca         = $this->s__filtro['id_tipo_beca'];
		$ruta_pdf             = sprintf('%s/becas/doc_por_convocatoria/%s/%s/resolucion_otorgamiento.pdf', $this->s__ruta_base, $id_convocatoria, $id_tipo_beca);
		$datos['archivo_pdf'] = file_exists($ruta_pdf);

		$form->set_template($this->get_template($datos['archivo_pdf']));

		$form->set_datos($datos);
	}

	function evt__form_resolucion__modificacion($datos)
	{
		if ($datos['archivo_pdf']) {
			$id_convocatoria = $this->s__filtro['id_convocatoria'];
			$id_tipo_beca    = $this->s__filtro['id_tipo_beca'];
			$destino         = sprintf('becas/doc_por_convocatoria/%s/%s',$id_convocatoria, $id_tipo_beca);
			toba::consulta_php('helper_archivos')->subir_archivo($datos['archivo_pdf'], $destino, 'resolucion_otorgamiento.pdf', ['pdf']);
			unset($this->s__filtro);
			$this->set_pantalla('pant_seleccion');
		}
	}

	private function get_template($hay_archivo_cargado)
	{
		$id_convocatoria = $this->s__filtro['id_convocatoria'];
		$id_tipo_beca    = $this->s__filtro['id_tipo_beca'];
		$template = "<div><p>[ef id=archivo_pdf]</p></div>";
		
		if ($hay_archivo_cargado) {
			$template .= "<div><p style='text-align:center;'><a target=\"_blank\" href=\"{$this->s__url_base}/becas/doc_por_convocatoria/{$id_convocatoria}/{$id_tipo_beca}/resolucion_otorgamiento.pdf\"' style='font-size:1.5rem;'>Ver documento cargado (último guardado)</a></p></div>";
		} else {
			$template .= "<div><p style='text-align:center; font-size:1.5rem;'>No hay archivo cargado</p></div>";		
		}
		return $template;
	}



}
?>