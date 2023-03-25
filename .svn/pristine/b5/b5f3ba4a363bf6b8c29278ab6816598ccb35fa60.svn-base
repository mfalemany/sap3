<?php
class form_apoyo_solicitud extends sap_ei_formulario
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		parent::extender_objeto_js();
		echo "
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__responsable_fondos__procesar = function(es_inicial)
		{
			if(es_inicial){
				//return;
			}
			dni  = this.efs().responsable_fondos.get_estado();
			{$this->controlador()->objeto_js}.ajax('tiene_archivo_cbu',{'nro_documento':dni},this,function(respuesta){
				
				if( ! respuesta.existe_cbu && this.efs().constancia_cbu.tiene_estado()){
					notificacion.mostrar_ventana_modal('Responsable de fondos: Constancia de CBU','El responsable de fondos que seleccionó no tiene cargada una constancia de CBU. Por favor, haga click en \'Cambiar archivo\' para cargar una nueva. ','600px');
				}
			});
		}
		";
	}

}



?>