<?php
class cu_becas extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Reporte de Becas.xls');
		$this->agregar_columnas(array(
			'mail_becario'         => array('clave' => 'mail_becario',         'titulo' => 'Mail Becario'),
			'mail_director'        => array('clave' => 'mail_director',        'titulo' => 'Mail Director'),
			'residencia'           => array('clave' => 'residencia',           'titulo' => 'Lugar Residencia'),
			'sexo_postulante_desc' => array('clave' => 'sexo_postulante_desc', 'titulo' => 'Género'),
								));
		parent::vista_excel($salida);
	}
}

?>