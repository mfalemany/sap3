<?php
class cu_proyectos extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Reporte de proyectos.xls');
		$this->agregar_columnas(array(
			'mail_director' => array('clave' => 'mail_director','titulo' => 'Mail Director')
								));
		parent::vista_excel($salida);
	}
}
?>