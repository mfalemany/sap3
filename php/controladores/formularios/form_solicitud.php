<?php
class form_solicitud extends sap_ei_formulario
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__tipo_subsidio__procesar = function(es_inicial)
		{
			this.toggle_tabs(this.ef('tipo_subsidio').get_estado());
			this.mostrar_docum_requerida(this.ef('tipo_subsidio').get_estado());
			
		}
	
		{$this->objeto_js}.toggle_tabs = function(tipo_subsidio)
		{
			switch(tipo_subsidio) {
				case 'A':
					{$this->controlador()->objeto_js}.ocultar_tab('pant_estadia');
					{$this->controlador()->objeto_js}.mostrar_tab('pant_congreso');
					break;
				case 'B':
					{$this->controlador()->objeto_js}.ocultar_tab('pant_congreso');
					{$this->controlador()->objeto_js}.mostrar_tab('pant_estadia');
					break;
				default:
					{$this->controlador()->objeto_js}.ocultar_tab('pant_estadia');
					{$this->controlador()->objeto_js}.ocultar_tab('pant_congreso');
			}
			
		}

		{$this->objeto_js}.mostrar_docum_requerida = function(tipo_subsidio)
		{
			switch(tipo_subsidio) {
				case 'A':
					notif = 'Documentaci�n que debe adjuntar: <ul><li>Copia del trabajo presentado o a presentar</li><li>Nota de aceptaci�n del trabajo</li></ul>Puede adjuntar adem�s cualquier otra informaci�n que considere pertinente.';
					break;
				case 'B':
					notif = 'Documentaci�n que debe adjuntar: <ul><li>Invitaci�n del grupo receptor</li><li>Aval de la instituci�n receptora</li><li>Copia del convenio/acuerdo si lo hubiera</li></ul>Puede adjuntar adem�s cualquier otra informaci�n que considere pertinente.';
					break;
				default:
					notif = null;
			}
			{$this->controlador()->dep('ml_documentacion')->objeto_js}.limpiar_notificaciones();
			if(notif){
				{$this->controlador()->dep('ml_documentacion')->objeto_js}.agregar_notificacion(notif,'info');	
			}
			
		}
		";
	}
}
?>