<?php
class ci_control_documentacion extends sap_ci
{
	function conf()
	{
		$datos['inscripcion']  = $this->controlador()->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$datos['ruta_base']    = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$datos['url_base']     = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$datos['antecedentes'] = toba::consulta_php('co_inscripcion_conv_beca')->get_antecedentes_postulante($datos['inscripcion']['nro_documento']);
		$template = $this->armar_template(__DIR__ . "/templates/template_control_docum.php",$datos);
		$this->pantalla()->set_template($template);
	}
}

?>