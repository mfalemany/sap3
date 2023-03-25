<?php
class cu_solicitudes extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Admisibilidad de Becas.xls');
		$this->agregar_columnas(array(
			'mail_becario'  => array('clave' => 'mail_becario', 'titulo' => 'Mail Becario'),
			'mail_director' => array('clave' => 'mail_director','titulo' => 'Mail Director')
								));
		parent::vista_excel($salida);
	}
}
?>