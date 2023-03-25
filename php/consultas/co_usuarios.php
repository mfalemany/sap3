<?php 
class co_usuarios{
	
	protected $s__clave_original_usuario;

	function actualizar_mail($usuario,$mail)
	{
		if(empty($usuario) || empty($mail)){
			return false;
		}
		$sql = "UPDATE apex_usuario SET email = ".quote($mail)." WHERE usuario = ".quote($usuario);
		return toba::instancia()->get_db()->ejecutar($sql);
	}

	function get_datos_usuario($id_usuario)
	{
		return toba::instancia()->get_info_usuario($id_usuario);
	}

	function get_perfiles_usuario_proyecto($usuario,$proyecto = 'sap',$tipo_retorno = 'lista')
	{
		$sql = "SELECT * 
				FROM apex_usuario_proyecto 
				WHERE usuario = ".quote($usuario)." 
				AND proyecto = ".quote($proyecto);
		$perfiles = toba::instancia()->get_db()->consultar($sql);
		if($perfiles){
			foreach ($perfiles as $perfil) {
				$lista[] = $perfil['usuario_grupo_acc'];
			}
			return ($tipo_retorno == 'lista') ? implode('/',$lista) : $lista;
		}
		return ($tipo_retorno == 'lista') ? '' : array();
		
		
	}
	function get_perfiles_proyecto($proyecto = 'sap')
	{
		$sql = "SELECT * 
				FROM apex_usuario_proyecto 
				WHERE proyecto = ".quote($proyecto);
		return toba::instancia()->get_db()->consultar($sql);
	}

	public function reset_clave_temporal($usuario)
	{
		if (!$usuario || !is_string($usuario)) {
			return ['status' => false, 'message' => 'No se recibió un usuario o el usuario recibido no es un string'];
		}
		
		// Armo una key para guardar en memoria
		$key_memoria = 'clave_original_usuario_'.$usuario;
		
		//Si no está seteado en memoria una clave original, no hay nada que resetear
		if (!toba::memoria()->existe_dato($key_memoria)) {
			return ['status' => false, 'message' => 'No hay una clave guardada para resetear'];
		}

		$clave_original = toba::memoria()->get_dato($key_memoria);
		$db             = toba::instancia()->get_db();

		try {
			// Seteo la clave original
			$db->ejecutar("UPDATE apex_usuario SET clave = '$clave_original' WHERE usuario = '$usuario';");
			toba::memoria()->eliminar_dato($key_memoria);
			return ['status' => true];
		} catch (toba_error_db $e) {
			return ['status' => false, 'message' => 'Ocurrió el siguiente error: ' . $e->get_mensaje() . $e->get_mensaje_motor()];
		} catch (Exception $e) {
			return ['status' => false, 'message' => 'Ocurrió el siguiente error: ' . $e->getMessage()];
		}
	}


	public function set_clave_temporal($usuario) 
	{
		if (!$usuario || !is_string($usuario)) {
			return ['status' => false, 'message' => 'No se recibió un usuario o el usuario recibido no es un string'];
		}
		
		// Armo una key para guardar en memoria
		$key_memoria = 'clave_original_usuario_'.$usuario;

		//Si ya está seteado en memoria una clave original, no se puede establecer una nueva
		if (toba::memoria()->existe_dato($key_memoria)) {
			return ['status' => false, 'message' => 'No se puede establecer una clave temporal... Hay una clave temporal en curso'];
		}

		// Obtengo la clave temporal configurada en parámetros del sistema
		$clave_temporal = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('clave_temporal');
		$clave_temporal = encriptar_con_sal($clave_temporal, apex_pa_algoritmo_hash);
		
		// Obtengo la clave actual del usuario
		$sql       = "SELECT clave FROM apex_usuario WHERE usuario = " . quote($usuario);
		$db        = toba::instancia()->get_db();
		$resultado = $db->consultar_fila($sql);

		// Si se encontró un registro
		if (!empty($resultado['clave'])) {
			// Armo una key para guardar en memoria
			$key_memoria = 'clave_original_usuario_'.$usuario;
			toba::memoria()->set_dato($key_memoria, $resultado['clave']);

			try {
				// Seteo la clave temporal
				$db->ejecutar("UPDATE apex_usuario SET clave = '$clave_temporal' WHERE usuario = '$usuario';");
				return ['status' => true];
			} catch (toba_error_db $e) {
				return ['status' => false, 'message' => 'Ocurrió el siguiente error: ' . $e->get_mensaje() . $e->get_mensaje_motor()];
			} catch (Exception $e) {
				return ['status' => false, 'message' => 'Ocurrió el siguiente error: ' . $e->getMessage()];
			}
		} else {
			return ['status' => false, 'message' => 'No se encontró el usuario buscado'];
		}
	}

	function set_perfiles_usuario($usuario, $perfiles, $proyecto = 'sap')
	{
		$instancia = toba::instancia()->get_db();

		//Elimino todos los perfiles funcionales asignados al usuario
		//$sql = "DELETE FROM apex_usuario_proyecto WHERE usuario = ".quote($usuario)." AND proyecto = ".quote($proyecto);
		//$instancia->ejecutar($sql);




	}
}

	
?>