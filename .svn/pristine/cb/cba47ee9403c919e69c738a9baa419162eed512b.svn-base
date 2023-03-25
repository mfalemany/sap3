<?php
class cu_apoyos extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Solicitudes de apoyo.xls');
		$this->agregar_columnas(array(
			'insumos_laboratorio'  => array('clave' => 'insumos_laboratorio_desc', 'titulo' => 'Insumos Lab.'),
			'gastos_campania'      => array('clave' => 'gastos_campania_desc',     'titulo' => 'Gastos Campania')
								));
		parent::vista_excel($salida);
	}
}
?>