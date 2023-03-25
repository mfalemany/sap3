<?php 
	// TODO: Sacar esto... es para "automatizar" el cambio de nombre de los cuils
	$ruta = '/mnt/datos/cyt/docum_personal/'.toba::usuario()->get_id();
	if (file_exists($ruta.'/CUIL.pdf')) {
		rename($ruta.'/CUIL.pdf', $ruta.'/cuil.pdf');
	}
	
	// Si existe notificación pendiente, se muestra en pantallaensa
	$mensaje = toba::memoria()->get_dato("mensaje");
	if (isset($mensaje)) {
		toba::notificacion()->agregar($mensaje,'info');
		toba::memoria()->eliminar_dato("mensaje");
	}
	echo '<div class="logo">';
	echo toba_recurso::imagen_proyecto('logo_grande.gif', true);
	echo '</div>';

	$nro_documento = toba::usuario()->get_id();
	
	/* ================================================================ */
	if( strlen($nro_documento) <= 15){
		$nro_documento = str_replace(['.',' '], [''], $nro_documento);
		if (is_numeric($nro_documento)){
			//Se intenta asegurar la existencia del usuario, buscandolo en local y en WS
			toba::consulta_php('co_personas')->existe_persona($nro_documento);
			
			//Se hace la consulta para ver si se lo encontró
			$persona = toba::consulta_php('co_personas')->get_personas(array('nro_documento'=>$nro_documento));
			
			//Si no se lo encontró, se lo da de alta con los datos proporcionados en el registro
			if(count($persona) == 0){
				$datos_usuario = toba::instancia()->get_info_usuario($nro_documento);
				$partes = explode(",",$datos_usuario['nombre']);
				$apellido = trim($partes[0]);
				$nombres = isset($partes[1]) ? trim($partes[1]) : "";
				toba::consulta_php('co_personas')->nueva_persona(
					array('nro_documento'=> $nro_documento,
						  'apellido'     => $apellido,
						  'nombres'      => $nombres,
						  'mail'         => $datos_usuario['email'],
						  'cuil'         => 'XX-'.$nro_documento."-X")
					);
			}
		} 
		/* ================================================================ */
	
	}
?>
