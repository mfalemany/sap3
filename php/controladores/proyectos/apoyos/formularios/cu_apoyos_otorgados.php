<?php
class cu_apoyos_otorgados extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Apoyos economicos otorgados.xls');
		$this->agregar_columnas(array(
			'mail_solicitante'     => array('clave' => 'mail_solicitante',         'titulo' => 'Mail Solicitante'),
			'mail_responsable'     => array('clave' => 'mail_responsable',         'titulo' => 'Mail Resp. Fondos'),
			'insumos_laboratorio'  => array('clave' => 'insumos_laboratorio_desc', 'titulo' => 'Insumos Lab.'),
			'gastos_campania'      => array('clave' => 'gastos_campania_desc',     'titulo' => 'Gastos Campania')
								));
		parent::vista_excel($salida);
	}
}

?>