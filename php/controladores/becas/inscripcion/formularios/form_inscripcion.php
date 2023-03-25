<?php
class form_inscripcion extends sap_ei_formulario
{
	
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		parent::extender_objeto_js();
		echo "

		if ({$this->objeto_js}.ef('id_tipo_beca').get_estado() != 11) {
			console.log('Ocultando todo en el primer if');
			// Estas aclaraciones se ocultan hasta que el ef 'subtipo_beca' toma el valor apropiado
			document.querySelectorAll('.aclaracion_subtipo_beca').forEach(function(element){
				element.style.display = 'none';
			});
			{$this->objeto_js}.ef('subtipo_beca').ocultar();
		}

		//---- Validacion de EFs -----------------------------------
		
		/* ========================================================================================== */
		//Se obtiene el promedio de los egresados de la carrera elegida
		{$this->objeto_js}.ef('id_carrera').cuando_cambia_valor('get_prom_hist_egresados({$this->objeto_js}.ef(\'id_carrera\').get_estado())');

		// Obtengo el promedio hist�rico de los graduados de los �ltimos a�os
		function get_prom_hist_egresados(id_carrera)
		{
			if(id_carrera == 'nopar'){
				{$this->objeto_js}.ef('prom_hist_egresados').resetear_estado();
				{$this->objeto_js}.ef('prom_hist_egresados').set_solo_lectura(false);
				return;
			}
			datos = {'id_carrera':id_carrera};
			{$this->controlador()->objeto_js}.ajax('get_prom_hist_egresados',datos,this,setear_prom_hist);
		}

		function setear_prom_hist(respuesta)
		{
			if( ! respuesta.error){
				{$this->objeto_js}.ef('prom_hist_egresados').set_estado(respuesta.prom_hist_egresados);
				{$this->objeto_js}.ef('prom_hist_egresados').set_solo_lectura(true);
			}else{
				//Si hubo error es porque no se encontr� el promedio de la carrera seleccionada
				{$this->objeto_js}.ef('prom_hist_egresados').resetear_estado();
				{$this->objeto_js}.ef('prom_hist_egresados').set_solo_lectura(false);
			}
		}

		/* ========================================================================================= */
		
		
		
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__id_area_conocimiento__procesar = function(es_inicial)
		{
			area = this.ef('id_area_conocimiento').get_estado();
			if(area != 'nopar'){
				{$this->controlador()->objeto_js}.ajax('get_disciplinas_incluidas',area,this,listar_disciplinas);	
			}
			
		}
		
		function listar_disciplinas(respuesta)
		{
			$('#disciplinas_incluidas').html('Disciplinas inclu�das: '+respuesta);	
		}
		
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__nro_documento__procesar = function(es_inicial)
		{
			if( ! es_inicial){
				if(this.ef('nro_documento').get_estado().length){
					var params = {nro_documento : this.ef('nro_documento').get_estado(),
								  id_tipo_beca  : this.ef('id_tipo_beca').get_estado()};
					{$this->controlador()->objeto_js}.ajax('validar_edad',params,this,alertar_error);
				}
			}
		}
		
		function alertar_edad(edad_valida){
			if(edad_valida === false){
				notificacion.agregar('La persona indicada como postulante supera la edad l�mite para el tipo de beca al que intenta inscribirse. Esto har� que la inscripci�n resulte inadmisible.','error');
				notificacion.ventana_modal();
				notificacion.limpiar();
			}
		}
		function alertar_error(respuesta){
			console.log(respuesta);
			if(respuesta.error){
				notificacion.agregar(respuesta.mensaje,'error');
				notificacion.ventana_modal();
				notificacion.limpiar();
				}else{
					console.log(respuesta.mensaje);
				}
		}

		{$this->objeto_js}.evt__materias_aprobadas__procesar = function(es_inicial)
		{
			if( ! es_inicial){
				if(this.ef('id_tipo_beca').get_estado() != 1){
					return;
				}
				if(this.ef('materias_aprobadas').get_estado() && this.ef('materias_plan').get_estado()){
					var params = {materias_aprobadas : this.ef('materias_aprobadas').get_estado(),
								  materias_plan      : this.ef('materias_plan').get_estado()};
					{$this->controlador()->objeto_js}.ajax('validar_minimo_materias_exigidas',params,this,alertar_error);
				}
			}
		}
		
		// ================== VALIDACIONES AL MOMENTO DE SUBMIT =======================================
		
		//---- Validacion general ----------------------------------
		
		{$this->objeto_js}.evt__validar_datos = function()
		{
			
			
			if(this.ef('anio_ingreso').get_estado() > new Date().getFullYear()){
				notificacion.agregar('El a�o de ingreso no puede ser posterior al a�o actual');
				return false;
			}
			if(this.ef('materias_aprobadas').get_estado() > this.ef('materias_plan').get_estado()){
				notificacion.agregar('El postulante no puede tener mas materias aprobadas de las que tiene el Plan de Estudios');
				return false;
			}

			//Si se asigna un codirector, debe justificarse su inclusi�n (se hace obligatorio el campo)
			var dni_codirector    = String(this.ef('nro_documento_codir').get_estado());
			var justif_codirector = String(this.ef('justif_codirector').get_estado());
			
			if(dni_codirector.length > 0 && dni_codirector != 'nopar') {
				this.ef('justif_codirector').set_obligatorio(true);
				if (justif_codirector.length == 0) {
					return false;
				}
			} else {
				this.ef('justif_codirector').set_obligatorio(false);
				this.ef('justif_codirector').set_estado('');
			}


			//Si se asigna un sub-director, debe justificarse su inclusi�n (se hace obligatorio el campo)
			var dni_subdirector    = String(this.ef('nro_documento_subdir').get_estado());
			var justif_subdirector = String(this.ef('justif_subdirector').get_estado());

			if(dni_subdirector.length > 0 && dni_subdirector != 'nopar') {
				this.ef('justif_subdirector').set_obligatorio(true);
				if (justif_subdirector.length == 0) {
					return false;
				}
			} else {
				this.ef('justif_subdirector').set_obligatorio(false);
				this.ef('justif_subdirector').set_estado('');
			}
			
			return true;
		}

		// ---------- Cuando se elije el tipo de beca, se determina si ser� obligatorio o no la carga
		// de una inscripci�n/compromiso a carrera de posgrado
		{$this->objeto_js}.ef('id_tipo_beca').cuando_cambia_valor('verificar_cambio_tipo_beca({$this->objeto_js}.ef(\'id_tipo_beca\').get_estado())');

		function verificar_cambio_tipo_beca(id_tipo_beca){
			requiere_inscripcion_posgrado(id_tipo_beca);
			verificar_visibilidad_ef_subtipo_beca(id_tipo_beca);
		}

		function requiere_inscripcion_posgrado(id_tipo_beca){
			if(id_tipo_beca != 'nopar'){
				{$this->controlador()->objeto_js}.ajax('requiere_inscripcion_posgrado',id_tipo_beca,this,datos_posgrado);
			}else{
				this.datos_posgrado(true);
			}
			
		}

		function verificar_visibilidad_ef_subtipo_beca(){
			
			//Cuando el tipo de beca es BEI-T1
			if ({$this->objeto_js}.ef('id_tipo_beca').get_estado() == 11) {
				
				//Muesto el selector de subtipo de beca
				{$this->objeto_js}.ef('subtipo_beca').mostrar();
				//Establezco la visibilidad de la aclaracion
				var display = 'table-cell';
			} else {
				//Oculto el selector de subtipo de beca
				{$this->objeto_js}.ef('subtipo_beca').ocultar();
				//Establezco la visibilidad de la aclaracion
				var display = 'none';
			}

			// Establezco la visibiliadad a todos los elementos de aclaracion
			document.querySelectorAll('.aclaracion_subtipo_beca').forEach(function(element){
				element.style.display = display;
			});
			
		}


		function datos_posgrado(requerir){
			if(!requerir){
				{$this->objeto_js}.ef('archivo_insc_posgrado').set_estado('');
				{$this->objeto_js}.ef('archivo_insc_posgrado').desactivar();
				
				{$this->objeto_js}.ef('nombre_inst_posgrado').set_estado('');
				{$this->objeto_js}.ef('nombre_inst_posgrado').desactivar();
				
				{$this->objeto_js}.ef('carrera_posgrado').set_estado('');
				{$this->objeto_js}.ef('carrera_posgrado').desactivar();
				
				{$this->objeto_js}.ef('fecha_insc_posgrado').set_estado('');	
				{$this->objeto_js}.ef('fecha_insc_posgrado').desactivar();	
				
				{$this->objeto_js}.ef('titulo_carrera_posgrado').set_estado('');
				{$this->objeto_js}.ef('titulo_carrera_posgrado').desactivar();	
				
			}else{
				{$this->objeto_js}.ef('archivo_insc_posgrado').activar();
				{$this->objeto_js}.ef('nombre_inst_posgrado').activar();
				{$this->objeto_js}.ef('carrera_posgrado').activar();
				{$this->objeto_js}.ef('fecha_insc_posgrado').activar();	
				{$this->objeto_js}.ef('titulo_carrera_posgrado').activar();	
			}
			
		}
		";
	}







}
?>