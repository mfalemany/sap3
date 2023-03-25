<?php 
class co_apoyos{
	function adeuda_rendicion($codigo)
	{
		$sql = "SELECT * FROM tmp_adeudan_rendicion WHERE director = ".quote($codigo);
		return (count(toba::db()->consultar($sql)) > 0) ;
	}

	function get_apoyos($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "sol.id_proyecto in (select id_proyecto 
						from sap_proyecto_integrante as inte
						where inte.nro_documento = ".quote($filtro['nro_documento'])."
						and inte.id_funcion in (select id_funcion from sap_proyecto_integrante_funcion where identificador_perfil in ('D','C','S') )
						)";
		}
		if(isset($filtro['solicitante'])){
			$where[] = "idir.nro_documento in (SELECT nro_documento FROM sap_personas WHERE apellido ILIKE ".quote("%".$filtro['solicitante']."%")." OR nombres ILIKE ".quote("%".$filtro['solicitante']."%").")";
		}
		if(isset($filtro['responsable_fondos'])){
			$where[] = "sol.responsable_fondos in (SELECT nro_documento FROM sap_personas WHERE apellido ILIKE ".quote("%".$filtro['responsable_fondos']."%")." OR nombres ILIKE ".quote("%".$filtro['responsable_fondos']."%").")";
		}
		if(isset($filtro['id_apoyo'])){
			$where[] = "sol.id_apoyo = ".quote($filtro['id_apoyo']);
		}
		if(isset($filtro['id_dependencia'])){
			$where[] = "proy.sap_dependencia_id = ".quote($filtro['id_dependencia']);
		}
		if(isset($filtro['codigo_proyecto'])){
			$where[] = "proy.codigo ILIKE ".quote("%".$filtro['codigo_proyecto']."%");
		}
		if(isset($filtro['distintos']) && $filtro['distintos']){
			$where[] = "sol.responsable_fondos <> idir.nro_documento";
		}
		if(isset($filtro['estado'])){
			$where[] = "sol.estado = ".quote($filtro['estado']);
		}
		if(isset($filtro['anio'])){
			$where[] = "sol.anio = ".quote($filtro['anio']);
		}
		if(isset($filtro['otorgado'])){
			if($filtro['otorgado'] == 'S'){
				$where[] = "EXISTS (SELECT * FROM sap_apoyo_otorgado WHERE id_apoyo = sol.id_apoyo)";	
			}else{
				$where[] = "NOT EXISTS (SELECT * FROM sap_apoyo_otorgado WHERE id_apoyo = sol.id_apoyo)";
			}
		}



		$sql = "SELECT dir.apellido||', '||dir.nombres AS director,
						dir.nro_documento AS nro_documento_director,
						cor.apellido||', '||cor.nombres AS codirector,
						cor.nro_documento AS nro_documento_codirector,
						resp_fondos.apellido||', '||resp_fondos.nombres AS responsable_fondos_desc,
						resp_fondos.nro_documento AS nro_documento_resp_fondos,
						resp_fondos.cuil AS cuil_resp_fondos,
						(SELECT SUM(monto) FROM sap_apoyo_presupuesto WHERE id_apoyo = sol.id_apoyo) as monto_solicitado,
						(SELECT monto_otorgado FROM sap_apoyo_otorgado WHERE id_apoyo = sol.id_apoyo) as monto_otorgado,
						dep.id AS id_dependencia,
						dep.nombre AS dependencia,
						proy.descripcion AS proyecto_desc,
						proy.codigo AS proyecto_codigo,
						proy.fecha_desde AS proyecto_desde,
						proy.fecha_hasta AS proyecto_hasta,
						CASE WHEN sol.insumos_laboratorio THEN 'SI' ELSE 'NO' END AS insumos_laboratorio_desc,
						CASE WHEN sol.gastos_campania THEN 'SI' ELSE 'NO' END AS gastos_campania_desc,
						sol.*
			FROM sap_apoyo_solicitud AS sol
			LEFT JOIN sap_proyectos AS proy ON proy.id = sol.id_proyecto
			LEFT JOIN sap_proyecto_integrante AS idir ON idir.id_proyecto = proy.id AND current_date BETWEEN idir.fecha_desde AND idir.fecha_hasta   AND idir.id_funcion = (select id_funcion from sap_proyecto_integrante_funcion WHERE identificador_perfil = 'D')
			LEFT JOIN sap_personas AS dir ON dir.nro_documento = idir.nro_documento
			LEFT JOIN sap_proyecto_integrante AS icor ON icor.id_proyecto = proy.id AND current_date BETWEEN icor.fecha_desde AND icor.fecha_hasta   AND icor.id_funcion = (select id_funcion from sap_proyecto_integrante_funcion WHERE identificador_perfil = 'C')
			LEFT JOIN sap_personas AS cor ON cor.nro_documento = icor.nro_documento
			LEFT JOIN sap_personas AS resp_fondos ON resp_fondos.nro_documento = sol.responsable_fondos
			LEFT JOIN sap_dependencia AS dep ON dep.id = proy.sap_dependencia_id";
		if(isset($filtro['order_by'])){
			$sql .= " ORDER BY ".$filtro['order_by'];
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_apoyos_otorgados($filtro = array())
	{
		$where = array();
		if(isset($filtro['anio'])){
			$where[] = "sol.anio = ".quote($filtro['anio']);
		}
		if(isset($filtro['responsable_fondos'])){
			$where[] = "sol.responsable_fondos = ".quote($filtro['responsable_fondos']);
		}
		if(isset($filtro['solicitante'])){
			$where[] = "dir.nro_documento = ".quote($filtro['solicitante']);
		}
		if(isset($filtro['codigo_proyecto'])){
			$where[] = "proy.codigo ILIKE ".quote("%". $filtro['codigo_proyecto'] . "%");
		}



		$sql = "SELECT *,
					solic.apellido||', '||solic.nombres AS solicitante,
					solic.mail AS mail_solicitante,
					resp.apellido||', '||resp.nombres AS responsable_fondos_desc,
					resp.mail AS mail_responsable,
					dep.nombre AS dependencia,
					(SELECT SUM(monto) FROM sap_apoyo_presupuesto WHERE id_apoyo = sol.id_apoyo) as monto_solicitado,
					CASE WHEN sol.insumos_laboratorio THEN 'SI' ELSE 'NO' END AS insumos_laboratorio_desc,
					CASE WHEN sol.gastos_campania THEN 'SI' ELSE 'NO' END AS gastos_campania_desc
				FROM sap_apoyo_otorgado AS oto
				LEFT JOIN sap_apoyo_solicitud AS sol ON sol.id_apoyo = oto.id_apoyo
				LEFT JOIN sap_proyectos AS proy ON proy.id = sol.id_proyecto
				LEFT JOIN sap_personas AS solic ON solic.nro_documento = (
					SELECT inte.nro_documento 
					FROM sap_proyecto_integrante AS inte
					WHERE inte.id_proyecto = proy.id
					AND inte.id_funcion = (
						SELECT id_funcion 
						FROM sap_proyecto_integrante_funcion 
						WHERE identificador_perfil = 'D'
					)
					/* ESTA ?TIMA CONSULTA SE REALIZA POR SI EL DIRECTOR DEJ? EL PROYECTO Y ASUMI? OTRO. TRAE EL ?TIMO */
					AND inte.fecha_desde = (
						SELECT MAX(fecha_desde) 
						FROM sap_proyecto_integrante 
						WHERE id_proyecto = inte.id_proyecto 
						AND id_funcion = inte.id_funcion
					)
				)
				LEFT JOIN sap_personas AS resp ON resp.nro_documento = sol.responsable_fondos
				LEFT JOIN sap_dependencia AS dep ON dep.id = proy.sap_dependencia_id";
				

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);

	}

	

	function get_estado_apoyo($id_apoyo)
	{
		$sql = "SELECT estado FROM sap_apoyo_solicitud WHERE id_apoyo = ".quote($id_apoyo);
		$apoyo = toba::db()->consultar_fila($sql);
		return (isset($apoyo['estado'])) ? $apoyo['estado'] : NULL;
	}

	/**
	 * Retorna un numero que representa el total (en pesos) de apoyo economico recibido por parte de un determinado proyecto
	 * @param  integer $id_proyecto ID del proyecto 
	 * @return double              Monto total recibido por el proyecto
	 */
	function get_total_apoyo_a_proyecto($id_proyecto)
	{
		$sql = "SELECT SUM(monto_otorgado) AS monto
			FROM sap_apoyo_otorgado
			WHERE id_apoyo IN (SELECT id_apoyo FROM sap_apoyo_solicitud WHERE id_proyecto = " . quote($id_proyecto) .")";
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['monto']) ? $resultado['monto'] : 0;
	}

	function get_necesidades_presupuestarias($id_apoyo)
	{
		$sql = "SELECT rub.rubro, pres.*
				FROM sap_apoyo_solicitud AS sol
				LEFT JOIN sap_apoyo_presupuesto AS pres ON pres.id_apoyo = sol.id_apoyo
				LEFT JOIN sap_proy_presupuesto_rubro AS rub ON rub.id_rubro = pres.id_rubro
				WHERE sol.id_apoyo = ".quote($id_apoyo);
		return toba::db()->consultar($sql);
	}

	function get_posibles_responsables_economicos($id_proyecto)
	{
		$integrantes = toba::consulta_php('co_proyectos')->get_integrantes(array('id' => $id_proyecto, 'fecha' => date("Y-m-d")));
		$responsables_exceptuados = array_column($this->get_responsables_exceptuados(),'nro_documento');
		
		foreach ($integrantes as $indice => $integrante) {
			if(in_array($integrante['nro_documento'], $responsables_exceptuados)){
				continue;
			}
			if($integrante['id_funcion'] > 2){
				unset($integrantes[$indice]);
			}
			if( ! toba::consulta_php('co_personas')->no_es_docente_temporal($integrante['nro_documento'])) {
				unset($integrantes[$indice]);
			}
		}
		return $integrantes;
	}

	function get_responsables_exceptuados()
	{
		return toba::db()->consultar("SELECT * FROM sap_excepcion_dir WHERE aplicable = 'F'");
	}

	function otorgar_apoyo($id_apoyo,$monto = NULL)
	{
		$sql = "SELECT *,
				(SELECT SUM(monto) FROM sap_apoyo_presupuesto WHERE id_apoyo = ".quote($id_apoyo).") as monto_solicitado 
				FROM sap_apoyo_solicitud WHERE id_apoyo = ".quote($id_apoyo);
		$solicitud = toba::db()->consultar_fila($sql);
		$monto = ($monto) ? $monto : $solicitud['monto_solicitado'];
		$sql = "INSERT INTO sap_apoyo_otorgado (id_apoyo,monto_otorgado) VALUES (".quote($id_apoyo).",".quote($monto).")";
		return toba::db()->ejecutar($sql);
	}


}
?>