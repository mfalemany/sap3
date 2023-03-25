<?php
class co_proyectos
{
	const CAMPOS = 'p.id,p.codigo,p.descripcion,p.fecha_desde,p.fecha_hasta,p.entidad_financiadora,p.director,p.nro_documento_dir,p.co_director,p.sub_director';

	function adeuda_informe($id, $tipos = '', $anio = 0)
	{
		if($anio == 0){
			$anio = date('Y');
		}
		$tipos = (is_array($tipos)) ? implode('\',\'', $tipos) : $tipos;
		$sql = "SELECT codigo, tipo, fecha_hasta ,fecha_desde FROM sap_proyectos WHERE id = ".$id;
		$proyecto = toba::db()->consultar_fila($sql);
		if($proyecto['fecha_hasta'] > quote($anio).'-12-30')# && $proyecto['fecha_desde'] < quote($anio).'-01-01')
			{
			if($proyecto['tipo'] == '0'){
				$tabla = 'sap_proyectos_pi_informe';
			}elseif ($proyecto['tipo'] == 'D') {
				$tabla = 'sap_proyectos_pdts_informe';
			}else{
				return FALSE;
			}
			$sql = "SELECT * FROM ".$tabla." AS inf WHERE inf.id_proyecto = ".$id." AND inf.estado = 'C' AND extract(year from inf.fecha_presentacion) = ".$anio;
			if($tipos != ''){
				$sql .= " AND inf.id_tipo_informe in (
							SELECT t.id_tipo_informe from sap_proy_tipo_informe AS t WHERE tipo_informe in ('".$tipos."')
						)";
			}
			$resultados = count(toba::db()->consultar($sql));
			return ($resultados == 0);
		}else{
			return FALSE;
		}
	}

	function tiene_informe_desaprobado($id, $tipos = '', $anio = 0)
	{
		if($anio == 0){
			$anio = date('Y');
		}
		$tipos = (is_array($tipos)) ? implode('\',\'', $tipos) : $tipos;
		$sql = "SELECT codigo, tipo, fecha_hasta FROM sap_proyectos WHERE id = ".$id;
		$proyecto = toba::db()->consultar_fila($sql);
		if($proyecto['fecha_hasta'] > quote($anio).'-12-30'){
			if($proyecto['tipo'] == '0'){
				$tabla = 'sap_proyectos_pi_informe';
			}elseif ($proyecto['tipo'] == 'D') {
				$tabla = 'sap_proyectos_pdts_informe';
			}else{
				return FALSE;
			}
			$sql = "SELECT * FROM sap_proy_pi_informe_eval AS eval 
					LEFT JOIN ".$tabla." AS inf on inf.id_informe = eval.id_informe
					WHERE inf.id_proyecto = ".$id." 
						AND inf.estado = 'C' 
						AND extract(year from inf.fecha_presentacion) = ".quote($anio)."
						AND eval.estado = 'C' 
						AND eval.satisfactorio <> 'S'";
			if($tipos != ''){
				$sql .= " AND inf.id_tipo_informe in (
							SELECT t.id_tipo_informe from sap_proy_tipo_informe AS t WHERE tipo_informe in ('".$tipos."')
						)";
			}
			$resultados = count(toba::db()->consultar($sql));
			return ($resultados >= 2);

		}else{
			return FALSE;
		}
	}

	function detalle_evaluaciones_realizadas($nro_documento){
		$filtro = array();
		$detalle = '';
		$nro_documento = (!isset($nro_documento)) ?
			toba::usuario()->get_id() : $nro_documento;
		$filtro['nro_documento_evaluador'] = $nro_documento;
		$resultados = $this->get_evaluaciones_realizadas($filtro);

		if (count($resultados)) {
			foreach ($resultados as $res) {
				switch ($res['evaluacion']) {
					case 'Satisfactorio':
						$r = '+';
						break;
					case 'No satisfactorio':
						$r = '-';
						break;
					case 'No aprobado':
						$r = '-';
						break;
					case 'Bueno':
						$r = '+';
						break;
					case 'Muy bueno':
						$r = '+';
						break;
					case 'Excelente':
						$r = '+';
						break;
					default:
						$r = '';
						break;
				}
				$detalle .= $res['codigo'].'('.$r.');';
			}
			unset($res);
		}
		return $detalle;

	}

	/**
	 * Retorna true si el evaluador ya cerro todas las evaluaciones que tenia asignadas.
	 */
	function ha_finalizado_evaluaciones($nro_documento_evaluador)
	{
		$eval_realizadas = $this->detalle_evaluaciones_realizadas($nro_documento_evaluador);
		$eval_asignadas = $this->get_cantidad_evaluaciones_asignadas($nro_documento_evaluador);
		return substr_count($eval_realizadas,';') == $eval_asignadas;
	}

	private function determinar_instancia_evaluacion($presentacion)
	{
		$dif = $presentacion['anio_presentacion'] - $presentacion['anio_desde'];
		$duracion = $presentacion['anio_hasta'] - $presentacion['anio_desde'];
		switch ($dif) {
			case 0:
				return 'Inicial';
				break;
			case 1:
				return 'Primer Seguimiento';
				break;
			case 2:
				return 'Primer avance';
				break;
			case 3:
				return 'Segundo Seguimiento';
				break;
			case 4:
				return ($presentacion['anio_presentacion'] == ($presentacion['anio_hasta']+1) ) ? 'Informe final' : 'Segundo Avance';
				break;
			case ($dif > 4):
				if($presentacion['anio_presentacion'] == ($presentacion['anio_hasta']+1) ){
					return 'Informe final';
				}else{
					if( ($dif % 2) > 0){
						return 'Seguimiento';
					}else{
						return 'Avance';
					}
				}
				break;
			default:
				return 'No determinada';
				break;
		}
	}

	function dirige_proyectos($nro_documento, $solo_vigentes = FALSE)
	{
		$sql = "SELECT COUNT(*) AS cantidad 
				FROM sap_proyectos as pr
				LEFT JOIN sap_proyecto_integrante as inte ON inte.id_proyecto = pr.id
				WHERE inte.nro_documento = ".quote($nro_documento)."
				AND inte.id_funcion IN (
					SELECT id_funcion 
					FROM sap_proyecto_integrante_funcion 
					WHERE identificador_perfil in ('D','C','S')
				)";
		if($solo_vigentes){
			$sql .= " AND current_date BETWEEN inte.fecha_desde AND inte.fecha_hasta";
		}
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cantidad'] > 0);
	}

	function eliminar_auxiliares($id_proyecto)
	{
		toba::db()->ejecutar("DELETE FROM sap_proyecto_tesista WHERE id_proyecto = ".quote($id_proyecto));
		toba::db()->ejecutar("DELETE FROM sap_proyecto_becario WHERE id_proyecto = ".quote($id_proyecto));
		toba::db()->ejecutar("DELETE FROM sap_proyecto_alumno WHERE id_proyecto = ".quote($id_proyecto));
		toba::db()->ejecutar("DELETE FROM sap_proyecto_inv_externo WHERE id_proyecto = ".quote($id_proyecto));
		toba::db()->ejecutar("DELETE FROM sap_proyecto_apoyo WHERE id_proyecto = ".quote($id_proyecto));
	}


	function es_vigente($id_proyecto)
	{
		$resultado = $this->get_campo(array('fecha_hasta'),array('id'=>$id_proyecto));
		if(count($resultado)){
			return ($resultado[0]['fecha_hasta'] >= date('Y-m-d'));
		}
	}

	function esta_exceptuado($nro_documento)
	{
		$sql = "SELECT * FROM sap_excepcion_dir WHERE nro_documento = ".quote($nro_documento)." AND aplicable = 'P'";
		return count(toba::db()->consultar($sql));
	}

	function externa_proyecto_descripcion($id_proyecto)
	{
	   
		$id = quote($id_proyecto);
		$sql = "SELECT codigo || ' - ' || descripcion AS v_proyecto_descripcion
						FROM sap_proyectos
						WHERE id = $id";
		$resultado = toba::db()->consultar_fila($sql);

		if (! empty($resultado))
		{
			return $resultado;
		}
	}
	//Retorno todos los a�os en los que hubo presentaci�n de proyectos.
	function get_anios_proyecto()
	{
		return toba::db()->consultar("SELECT DISTINCT convocatoria_anio AS anio 
			FROM sap_proyectos 
			WHERE convocatoria_anio IS NOT NULL 
			ORDER BY 1 DESC");
	}

	function get_area($filtro)
	{
		$where = array();
		$sql = "SELECT id_area
				FROM sap_proyecto_subarea";
		if(isset($filtro['id_proyecto'])){
			$where[] = "id_subarea = (SELECT id_subarea 
									  FROM sap_proyectos 
									  WHERE id = ".quote($filtro['id_proyecto']).")";
		}
		if(isset($filtro['id_subarea'])){
			$where[] = 'id_subarea = '.quote($filtro['id_subarea']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
				
		$resultado = toba::db()->consultar_fila($sql);	
		return $resultado['id_area'];
	}

	function get_areas_proyectos()
	{
		return toba::db()->consultar("SELECT id_area, area FROM sap_proyecto_area");
	}

	function get_campo($campos,$filtro = array())
	{
		foreach($filtro as $campo => $valor){
			$where[] = $campo." = ".quote($valor);
		}
		$sql = "SELECT ".implode(',',$campos)." FROM sap_proyectos";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna el numero total de proyectos que tiene asignado un evaluador
	 */
	function get_cantidad_evaluaciones_asignadas($nro_documento_evaluador)
	{
		$sql = "SELECT proyectos_codigos FROM sap_proyecto_evaluador WHERE nro_documento = ".quote($nro_documento_evaluador);
		$resultado = toba::db()->consultar_fila($sql);
		return (count($resultado)) ? count(explode(';',$resultado['proyectos_codigos'])) : 0;
	}

	//Retorna una consulta SQL que se usa (o se puede usar) como filtro en consulta de proyectos donde solo se requiere obtener los "solo_vigentes", es decir, en los que la fecha actual est� comprendida entre "fecha_desde" y "fecha_hasta", y tuvieron al menos una evaluacion positiva
	function get_consulta_vigentes($alias_tabla_proyectos){
		return "current_date between $alias_tabla_proyectos.fecha_desde and $alias_tabla_proyectos.fecha_hasta
				and (
				    select sum(eval_positivas) from (
				    select count(*) as eval_positivas from sap_proy_pi_eval where estado = 'C' and result_final_evaluacion <> 'N' and id_proyecto = $alias_tabla_proyectos.id
				    union 
				    select count(*) as eval_positivas from sap_proy_pdts_eval where estado = 'C' and result_final_evaluacion <> 'N' and id_proyecto = $alias_tabla_proyectos.id
				    ) as tmp
				) >= 2";
	}

	function get_contenido($id_proyecto)
	{
		$sql = "SELECT pr.*,pi.*,pdts.*,gr.denominacion AS denominacion_grupo
				FROM sap_proyectos AS pr
				LEFT JOIN sap_proyectos_pi AS pi ON pi.id_proyecto = pr.id
				LEFT JOIN sap_proyectos_pdts AS pdts ON pdts.id_proyecto = pr.id
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = pr.id_grupo
				WHERE pr.id = ".quote($id_proyecto);
		return toba::db()->consultar_fila($sql);
	}

	function get_descripcion($id_proyecto){
		$sql = "SELECT descripcion FROM sap_proyectos WHERE id = ".quote($id_proyecto)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['descripcion'];
	}

	function get_descripcion_by_codigo($codigo_proyecto)
	{
		$resultado = toba::db()->consultar_fila("SELECT '('||codigo||') '||descripcion as descripcion FROM sap_proyectos WHERE codigo = ".quote($codigo_proyecto));
		return (count($resultado)) ? $resultado['descripcion'] : FALSE;
	}

	function get_detalles_director($dni)
	{
		$sql = "SELECT per.apellido||', '||per.nombres AS ayn,
					per.nro_documento AS dni,
					CASE cat.categoria 
						WHEN 1 THEN 'Categor�a I'
						WHEN 2 THEN 'Categor�a II'
						WHEN 3 THEN 'Categor�a III'
						WHEN 4 THEN 'Categor�a IV'
						WHEN 5 THEN 'Categor�a V'
						ELSE 'No Categorizado'
					END AS cat,
					per.archivo_cvar
				FROM sap_personas AS per
				LEFT JOIN sap_cat_incentivos AS cat ON cat.nro_documento = per.nro_documento
					AND cat.convocatoria = (SELECT MAX(convocatoria) FROM sap_cat_incentivos WHERE nro_documento = per.nro_documento)
				WHERE per.nro_documento = ".quote($dni);
		return toba::db()->consultar_fila($sql);
	}

	function get_detalle_evaluaciones_realizadas($filtro)
	{
		if(!isset($filtro['id_proyecto'])){
			return array();
		}
		//obtengo los detalles del proyecto
		$proyecto = toba::consulta_php('co_proyectos')->get_proyectosByFiltros('id = '.$filtro['id_proyecto']);
		$proyecto = $proyecto[0];
		if($proyecto['tipo'] == '9'){
			return array(); //No se evaluan proyectos externos
		}
		$tipos = array('0'=>'pi','D'=>'pdts'); 
		//Si es la presentacion inicial tengo que buscar en una tabla, si es un informe en otra
		if($filtro['fecha_presentacion'] == $proyecto['fecha_desde']){
			$sql = "SELECT eval.*, proy.tipo, 'inicial' AS instancia, per.nombres||' '||per.apellido AS evaluador 
					FROM sap_proy_{$tipos[$proyecto['tipo']]}_eval AS eval
					LEFT JOIN sap_proyectos AS proy ON proy.id = eval.id_proyecto
					LEFT JOIN sap_personas AS per ON per.nro_documento = eval.nro_documento_evaluador
					WHERE id_proyecto = ".quote($filtro['id_proyecto'])."
					AND eval.estado = 'C'";
		}else{
			$sql = "SELECT eval.*,proy.tipo, 'informe' AS instancia, per.nombres||' '||per.apellido AS evaluador
					FROM sap_proyectos_{$tipos[$proyecto['tipo']]}_informe AS inf
					LEFT JOIN sap_proy_{$tipos[$proyecto['tipo']]}_informe_eval AS eval USING (id_informe)
					LEFT JOIN sap_proyectos AS proy ON proy.id = inf.id_proyecto
					LEFT JOIN sap_personas AS per ON per.nro_documento = eval.nro_documento_evaluador
					WHERE inf.id_informe = ".quote($filtro['id_informe'])." AND inf.fecha_presentacion = ".quote($filtro['fecha_presentacion'])."
					AND eval.estado = 'C'";

		}
		return toba::db()->consultar($sql);
	}

	function get_detalles_evaluador($dni)
	{
		$sql = "SELECT  per.apellido,
						per.nombres,
						CASE eva.tipo_evaluador WHEN 'T' THEN 'Todos' WHEN 'P' THEN 'Proyectos' WHEN 'I' THEN 'Informes' WHEN 'L' THEN 'Locales' ELSE 'No asignado' END AS tipo_evaluador_desc,
						dis.disciplina,
						eva.*
				FROM sap_proyecto_evaluador AS eva
				LEFT JOIN sap_personas AS per ON per.nro_documento = eva.nro_documento
				LEFT JOIN sap_disciplinas AS dis ON dis.id_disciplina = per.id_disciplina
				WHERE eva.nro_documento = " . quote($dni);
		return toba::db()->consultar_fila($sql);
	}

	function get_detalles_proyecto($id)
	{
		$sql = "SELECT pr.id,
					pr.codigo,
					pr.descripcion,
					pr.fecha_desde,
					pr.fecha_hasta,
					CASE pr.tipo WHEN '0' THEN 'PI' WHEN 'D' THEN 'PDTS' WHEN '9' THEN 'Externo' END as tipo_desc,
					pr.tipo,
					ar.area,
					sa.subarea,
					dep.nombre AS dependencia,
					pr.convocatoria_anio,
					gr.denominacion AS grupo_desc
				FROM sap_proyectos AS pr
				LEFT JOIN sap_proyecto_subarea AS sa ON pr.id_subarea = sa.id_subarea
				LEFT JOIN sap_proyecto_area AS  ar ON ar.id_area = sa.id_area
				LEFT JOIN sap_dependencia AS dep ON dep.id = pr.sap_dependencia_id
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = pr.id_grupo
				WHERE pr.id = ".quote($id);
		$resultado = toba::db()->consultar_fila($sql);

		//Obtengo los integrantes
		$sql = "SELECT 
					per.nro_documento,
					COALESCE(per.cuil,per.nro_documento) AS cuil, 
					per.apellido||', '||per.nombres AS ayn,
					pif.funcion,
					pi.fecha_desde,
					pi.fecha_hasta
				FROM sap_proyecto_integrante AS pi
				LEFT JOIN sap_personas AS per ON per.nro_documento = pi.nro_documento
				LEFT JOIN sap_proyecto_integrante_funcion AS pif ON pif.id_funcion = pi.id_funcion
				WHERE pi.id_proyecto = ".quote($id)." 
				ORDER BY pif.id_funcion ASC";
		$integrantes = toba::db()->consultar($sql);
		$resultado['integrantes'] = $integrantes;

		return $resultado;
	}

	function get_director_proyecto($id_proyecto)
	{
		$sql = "SELECT per.nro_documento , per.apellido||', '||per.nombres AS ayn, inte.fecha_desde, inte.fecha_hasta
				FROM sap_proyecto_integrante AS inte
				LEFT JOIN sap_personas AS per ON per.nro_documento = inte.nro_documento
				WHERE inte.id_funcion IN (
				    SELECT id_funcion 
				    FROM sap_proyecto_integrante_funcion 
				    WHERE identificador_perfil in ('D')
				)
				AND id_proyecto = ".quote($id_proyecto)."
				ORDER BY fecha_desde DESC
				LIMIT 1";
		return toba::db()->consultar_fila($sql);
	}

	function get_directores_proyectos()
	{
		$sql = "SELECT DISTINCT per.nro_documento , per.apellido||', '||per.nombres AS ayn
				FROM sap_proyecto_integrante AS inte
				LEFT JOIN sap_personas AS per ON per.nro_documento = inte.nro_documento
				WHERE inte.id_funcion IN (
					SELECT id_funcion 
					FROM sap_proyecto_integrante_funcion 
					WHERE identificador_perfil in ('D','C','S')
				)";
		return toba::db()->consultar($sql);
	}

	function get_estado_proyecto($id_proyecto)
	{	
		$resultado = toba::db()->consultar_fila("SELECT estado FROM sap_proyectos WHERE id = ".quote($id_proyecto));
		return $resultado['estado'];
	}

		// ========================= EVALUADORES DE PROYECTOS ========================
	function get_evaluaciones_asignadas($filtro)
	{
		$where = array();
		if(isset($filtro['proyectos_codigos'])){
			$where[] = "proyectos_codigos ilike ('%".
			$filtro['proyectos_codigos']."%')";
		}

		if(isset($filtro['evaluador'])){
			$where[] = "(per.nro_documento ILIKE ".quote('%'.$filtro['evaluador'].'%')." OR
						per.apellido ILIKE  ".quote('%'.$filtro['evaluador'].'%')." OR
						per.nombres  ILIKE  ".quote('%'.$filtro['evaluador'].'%') . ")";
		}

		if(isset($filtro['tipo_evaluador'])){
			$where[] = "tipo_evaluador = ".quote($filtro['tipo_evaluador']);
		}

		if(isset($filtro['puede_descargar_certificado'])){
			$where[] = "puede_descargar_certificado = ".quote($filtro['puede_descargar_certificado']);
		}

		if(isset($filtro['proyectos_asignados'])){
			if ($filtro['proyectos_asignados']) {
				$where[] = "proyectos_codigos <> ''";
			}else{
				$where[] = "proyectos_codigos = ''";
			}
		}

		// si estamos en el mes de junio o despues, se consideran las evaluaciones solo de este a�o
		if(date('m') >= 5){
			$cond_fecha = " extract(year from fecha_eval) = extract(year from current_date)";
		}else{
			//sino, se consideran las evaluaciones posteriores al primero de mayo del a�o pasado
			$cond_fecha = " fecha_eval > '".(date('Y')-1)."-05-01'";
		}
		$cte = "
				-- ===================================================================================================================================
				-- TODO ESTE BLOQUE OBTIENE UN LISTADO DE EVALUACIONES, PARA JUNTARLO A LA CONSULTA DE EVALUACIONES ASIGNADAS (PARA MOSTRAR EL ESTADO)
				-- ===================================================================================================================================
				WITH evaluaciones AS (
				SELECT 
					nro_documento_evaluador, 
					--Esto trae en un string, los codigos con sus resultados (+) o (-)
					ARRAY_TO_STRING(ARRAY_AGG(eval),', ') AS evaluaciones, 
					--Esto verifica si la cantidad asignada es igual a la cantidad evaluada
					(count(*) = (SELECT array_length( string_to_array(proyectos_codigos,';'),1)  FROM sap_proyecto_evaluador WHERE nro_documento = nro_documento_evaluador))::boolean as finalizado
				FROM (
				    SELECT nro_documento_evaluador, id_proyecto::varchar, proy.codigo||resultado as eval
				    FROM (
				        select nro_documento_evaluador, id_proyecto, CASE(result_final_evaluacion) WHEN 'N' THEN '(-)' ELSE '(+)' END AS resultado from sap_proy_pi_eval WHERE estado = 'C' AND ".$cond_fecha."
				        UNION
				        select nro_documento_evaluador, id_proyecto, CASE(result_final_evaluacion) WHEN 'N' THEN '(-)' ELSE '(+)' END AS resultado from sap_proy_pdts_eval WHERE estado = 'C' AND ".$cond_fecha."
				        UNION
				        select nro_documento_evaluador, id_proyecto, CASE(satisfactorio) WHEN 'S' THEN '(+)' ELSE '(-)' END AS resultado from sap_proy_pi_informe_eval as ev left join sap_proyectos_pi_informe as inf using (id_informe) WHERE ev.estado = 'C' AND ".$cond_fecha."
				        UNION
				        select nro_documento_evaluador, id_proyecto, CASE(satisfactorio) WHEN 'S' THEN '(+)' ELSE '(-)' END AS resultado from sap_proy_pdts_informe_eval as ev left join sap_proyectos_pdts_informe as inf using (id_informe) where ev.estado = 'C' AND ".$cond_fecha."
				    ) AS evaluaciones
                    LEFT JOIN sap_proyectos AS proy ON proy.id = evaluaciones.id_proyecto
                    WHERE proy.codigo in (SELECT unnest(string_to_array(proyectos_codigos,';')) FROM sap_proyecto_evaluador WHERE nro_documento = nro_documento_evaluador)
                    UNION
				    SELECT nro_documento_evaluador, id_programa, prog.codigo||resultado as eval
				    FROM (
				        select nro_documento_evaluador, id_programa, CASE(result_final_evaluacion) WHEN 'N' THEN '(-)' ELSE '(+)' END AS resultado FROM sap_programa_eval WHERE estado = 'C' AND ".$cond_fecha."
				        UNION
				        select nro_documento_evaluador, inf.id_programa, CASE(satisfactorio) WHEN 'S' THEN '(+)' ELSE '(-)' END AS resultado from sap_programa_informe_eval as ev left join sap_programa_informe AS inf using (id_informe) WHERE ev.estado = 'C' AND ".$cond_fecha."
				    ) as evaluaciones_prog
				    LEFT JOIN sap_programas AS prog ON prog.codigo = evaluaciones_prog.id_programa
                   
					) as final
				group by nro_documento_evaluador
				)
				-- ===================================================================================================================================
				";
		$sql = "SELECT pe.*, eva.*, per.apellido || ', ' || per.nombres as ayn,
				CASE pe.tipo_evaluador WHEN 'T' THEN 'Todos' WHEN 'P' THEN 'Proyectos' WHEN 'I' THEN 'Informes' WHEN 'L' THEN 'Locales' ELSE 'No asignado' END AS tipo_evaluador_desc
				FROM sap_proyecto_evaluador AS pe
				LEFT JOIN sap_personas AS per ON pe.nro_documento = per.nro_documento
				LEFT JOIN evaluaciones AS eva ON eva.nro_documento_evaluador = pe.nro_documento";

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		
		//Se escriben por separado, y se unen acá porque la función sql_concatenar_where tiene un bug acá y concatena mal los where
		return toba::db()->consultar($cte . $sql);

	}

	/* SE UTILIZA PARA EL REPORTE DE EVALUACIONES REALIZADAS (PDF) */
	function get_evaluaciones_realizadas($filtro = array())
	{
		if(!isset($filtro['nro_documento_evaluador'])){
			$filtro['nro_documento_evaluador'] = toba::usuario()->get_id();
		}

		// si estamos en el mes de junio o despues, se consideran las evaluaciones solo de este a�o
		if(date('m') >= 5){
			$cond_fecha = " extract(year from fecha_eval) = extract(year from current_date)";
		}else{
			//sino, se consideran las evaluaciones de este a�o, mas las posteriores al primero de mayo del a�o pasado
			$cond_fecha = " (extract(year from fecha_eval) = extract(year from current_date) OR 
							fecha_eval > '".(date('Y')-1)."-05-01')";
		}
		

		$where = array();
		//Esta consulta es muy larga, pero es una union de seis consultas cortas
		$sql[] = "
					/* EVALUACION PI*/
					select 
				    pr.codigo as codigo,
				    case when (length(pr.descripcion) > 75) then substr(pr.descripcion,1,75)||'(...)' else pr.descripcion end as descripcion, 
				    case(result_final_evaluacion) 
				    	when 'N' then 'No aprobado' 
				    	when 'B' then 'Bueno' 
				    	when 'M' then 'Muy bueno' 
				    	when 'E' then 'Excelente'  
				    	else result_final_evaluacion end as evaluacion,
				    fecha_eval,
				    'PI' as tipo
				from sap_proy_pi_eval as ev
				left join sap_proyectos as pr on pr.id = ev.id_proyecto
				where nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				--se obtienen todas las evaluaciones del usuario actual, de la ultima convocatoria
				and $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(pr.codigo,';')

				AND ev.estado = 'C'";
		$sql[] = "
				/* EVALUACION PI INFORME */
				select 
				    pr.codigo as codigo,
				    case when (length(pr.descripcion) > 75) then substr(pr.descripcion,1,75)||'(...)' else pr.descripcion end as descripcion, 
				    case(satisfactorio) when 'S' then 'Satisfactorio' else 'No satisfactorio' end as evaluacion,
				    fecha_eval,
				    'Informe de PI' as tipo
				from sap_proy_pi_informe_eval ev
				left join sap_proyectos_pi_informe as inf on inf.id_informe = ev.id_informe
				left join sap_proyectos as pr on pr.id = inf.id_proyecto
				where nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				AND $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(pr.codigo,';')
				AND ev.estado = 'C'";
		
		$sql[] = "
				/* EVALUACION PDTS */
				select 
				    pr.codigo as codigo,
				    case when (length(pr.descripcion) > 75) then substr(pr.descripcion,1,75)||'(...)' else pr.descripcion end as descripcion, 
				    case(result_final_evaluacion) 
				        when 'E' then 'Excelente'
				        when 'M' then 'Muy bueno'
				        when 'B' then 'Bueno'
				        when 'N' then 'No aprobado'
				        else result_final_evaluacion
				    end as evaluacion,
				    fecha_eval,
				    'PDTS' as tipo
				from sap_proy_pdts_eval as ev
				left join sap_proyectos as pr on pr.id = ev.id_proyecto
				where nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				--se obtienen todas las evaluaciones del usuario actual, de la ultima convocatoria
				and $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(pr.codigo,';')
				AND ev.estado = 'C'";

		$sql[] = "
				/* EVALUACION INFORME PDTS */
				select 
				    pr.codigo as codigo,
				    case when (length(pr.descripcion) > 75) then substr(pr.descripcion,1,75)||'(...)' else pr.descripcion end as descripcion, 
				    case(satisfactorio) when 'S' then 'Satisfactorio' else 'No satisfactorio' end as evaluacion,
				    fecha_eval,
				    'Informe de PDTS' as tipo
				from sap_proy_pdts_informe_eval AS ev
				left join sap_proyectos_pdts_informe as inf on inf.id_informe = ev.id_informe
				left join sap_proyectos as pr on pr.id = inf.id_proyecto
				where nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				AND $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(pr.codigo,';')
				AND ev.estado = 'C'";

		$sql[] = "
				/* EVALUACION PROGRAMA */
				select 
				    prog.codigo as codigo,
				    case when (length(prog.denominacion) > 75) 
				        then substr(prog.denominacion,1,75)||'(...)' 
				        else prog.denominacion end as descripcion, 
				    case(result_final_evaluacion) 
				        when 'E' then 'Excelente'
				        when 'M' then 'Muy bueno'
				        when 'B' then 'Bueno'
				        when 'N' then 'No aprobado'
				        else result_final_evaluacion
				    end as evaluacion,
				    fecha_eval,
				    'Programa' as tipo
				from sap_programa_eval as ev
				left join sap_programas as prog on prog.codigo = ev.id_programa
				where ev.nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				AND $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(prog.codigo,';')
				AND ev.estado = 'C'";

		$sql[] = "
				/* EVALUACION INFORME PROGRAMA */
				select 
				    prog.codigo as codigo,
				    case when (length(prog.denominacion) > 75) 
				        then substr(prog.denominacion,1,75)||'(...)' 
				        else prog.denominacion end as descripcion, 
				    case(satisfactorio) when 'S' then 'Satisfactorio' else 'No satisfactorio' end as evaluacion,
				    fecha_eval,
				    'Informe de Programa' as tipo
				from sap_programa_informe_eval as ev
				left join sap_programa_informe as inf on inf.id_informe = ev.id_informe
				left join sap_programas as prog on prog.codigo = inf.id_programa
				where ev.nro_documento_evaluador = ".quote($filtro['nro_documento_evaluador'])."
				AND $cond_fecha
				--El operador @> indica si un array contiene a otro
				AND (select string_to_array(proyectos_codigos,';') from sap_proyecto_evaluador where nro_documento = ".quote($filtro['nro_documento_evaluador']).") @> string_to_array(prog.codigo,';')
				AND ev.estado = 'C'";
				
		
		$sql_completo = implode(' union ',$sql);

		return toba::db()->consultar($sql_completo);
	}

	/**
	 * Retorna un array con un listado de todos los proyectos (PI, PDTS y PROGRAMAS), con sus evaluaciones y con sus informes (tambien con sus evaluaciones). Esta consulta es ideal para obtener un conjunto global de datos al cual aplicarle filtros
	 * @param  array  $filtro 
	 * @return array        
	 */
	function get_evaluaciones_todas($filtro = array())
	{
		$sql = "/* =============== PI, INFORMES y EVALUACIONES ====================== */
			select 'pi_informe' as tipo, pr.convocatoria_anio, pr.id::varchar as id_proyecto, inf.fecha_presentacion, inf.id_informe, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_proyectos_pi as pi
			left join sap_proyectos as pr ON pr.id = pi.id_proyecto
			left join sap_proyectos_pi_informe as inf on inf.id_proyecto = pi.id_proyecto
			left join sap_proy_pi_informe_eval as eval on eval.id_informe = inf.id_informe
			UNION
			/* =============== PI y EVALUACIONES ====================== */
			select 'pi' as tipo, pr.convocatoria_anio, pr.id::varchar as id_proyecto, null, null, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_proyectos_pi as pi
			left join sap_proyectos as pr ON pr.id = pi.id_proyecto
			left join sap_proy_pi_eval as eval on eval.id_proyecto = pi.id_proyecto
			UNION
			/* =============== PDTS, INFORMES y EVALUACIONES ====================== */
			select 'pdts_informe' as tipo, pr.convocatoria_anio, pr.id::varchar as id_proyecto, inf.fecha_presentacion, inf.id_informe, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_proyectos_pdts as pdts
			left join sap_proyectos as pr ON pr.id = pdts.id_proyecto
			left join sap_proyectos_pdts_informe as inf on inf.id_proyecto = pdts.id_proyecto
			left join sap_proy_pdts_informe_eval as eval on eval.id_informe = inf.id_informe
			UNION
			/* =============== PDTS y EVALUACIONES ====================== */
			select 'pdts' as tipo, pr.convocatoria_anio, pr.id::varchar as id_proyecto, null, null, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_proyectos_pdts as pdts
			left join sap_proyectos as pr ON pr.id = pdts.id_proyecto
			left join sap_proy_pdts_eval as eval on eval.id_proyecto = pdts.id_proyecto
			UNION
			/* =============== PROGRAMAS, INFORMES y EVALUACIONES ====================== */
			select 'programa_informe' as tipo, pr.convocatoria_anio, pr.codigo as id_proyecto, inf.fecha_presentacion, inf.id_informe, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_programa_informe as inf
			left join sap_programas as pr on pr.codigo = inf.id_programa
			left join sap_programa_informe_eval as eval on eval.id_informe = inf.id_informe
			UNION
			/* =============== PROGRAMAS y EVALUACIONES ====================== */
			select 'programa' as tipo, pr.convocatoria_anio, pr.codigo as id_proyecto, null, null, eval.nro_documento_evaluador, eval.fecha_eval, eval.estado
			from sap_programas as pr
			left join sap_programa_eval as eval on eval.id_programa = pr.codigo";



			/* ACA HAY QUE CONSULTAR Y RETORNAR (y hacer todo el tema de los filtros */
	}

	function get_evaluadores_ef_editable($patron){
		$sql = "WITH evaluadores AS (
				    SELECT pe.*, 
				        (SELECT apellido || ', ' || nombres 
				        FROM sap_personas
				        WHERE nro_documento = pe.nro_documento) AS ayn
				    FROM sap_proyecto_evaluador AS pe)
				SELECT * FROM evaluadores 
				WHERE 
					ayn ILIKE ".quote('%'.$patron.'%')."
					OR nro_documento ILIKE  ".quote('%'.$patron.'%');
		return toba::db()->consultar($sql);
	}


	function get_excepciones_dir($aplicable = 'P')
	{
		$aplicable = (is_array($aplicable)) ? implode(',', array_map('quote', $aplicable)) : quote($aplicable);
		$sql = "SELECT per.apellido||', '||per.nombres AS ayn, 
				CASE aplicable WHEN 'P' THEN 'Proyectos' WHEN 'F' then 'Responsable Fondos' END AS aplicable_desc,
				ex.*
				FROM sap_excepcion_dir AS ex
				LEFT JOIN sap_personas AS per ON per.nro_documento = ex.nro_documento
				WHERE aplicable IN ($aplicable)";
		return toba::db()->consultar($sql);
	}

	function get_funcion($identificador_perfil)
	{
		return toba::db()->consultar_fila("SELECT id_funcion,funcion FROM sap_proyecto_integrante_funcion WHERE identificador_perfil = ".quote($identificador_perfil));
	}

	function get_funciones_integrantes()
	{
		return toba::db()->consultar('SELECT * FROM sap_proyecto_integrante_funcion WHERE activo = \'1\' ORDER BY funcion ');
	}

	function get_funciones_sin_auxiliares()
	{
		return toba::db()->consultar("SELECT id_funcion FROM sap_proyecto_integrante_funcion WHERE identificador_perfil NOT IN ('D','S','C','I')");
	}

	function get_identificador_perfil($id_funcion)
	{
		$resultado = toba::db()->consultar_fila("SELECT identificador_perfil FROM sap_proyecto_integrante_funcion WHERE id_funcion = ".quote($id_funcion));
		return $resultado['identificador_perfil'];
	}

	//Retorna el ID del �ltimo informe presentado por un proyecto
	function get_id_ultimo_informe($id,$tipo){
		$id = quote($id);
		switch ($tipo) {
			case '0':
				$tabla = 'sap_proyectos_pi_informe';
				$campo = 'id_proyecto';
				break;
			case 'D':
				$tabla = 'sap_proyectos_pdts_informe';
				$campo = 'id_proyecto';
				break;
			case 'C':
				$tabla = 'sap_programa_informe';
				$campo = 'id_programa';
				break;
			default:
				throw new Exception('El tipo de informe buscado, no existe');
				break;
		}
		$sql = "SELECT id_informe
				FROM $tabla 
				WHERE $campo = $id
				AND fecha_presentacion IS NOT NULL
				ORDER BY fecha_presentacion DESC 
				LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		
		return $resultado['id_informe'];
	}

	function get_informes_proyecto($filtro = array())
	{
		$where = array();
		if(isset($filtro['tipo']) && isset($filtro['id'])){
			switch ($filtro['tipo']) {
				case '0':
					//ARMAR LAS CONSULTAS QUE LLENEN EL CUADRO DE SELECCION DE INFORME.
					$sql = "SELECT inf.id_informe, 
									pr.id, pr.codigo,
									pr.tipo,
									inf.estado,
									'PI' AS tipo_desc,
									pr.descripcion,
									inf.fecha_presentacion
							FROM sap_proyectos_pi AS pri
							LEFT JOIN sap_proyectos AS pr ON pr.id = pri.id_proyecto
							LEFT JOIN sap_proyectos_pi_informe AS inf ON inf.id_proyecto = pri.id_proyecto";
					$where[] = 'pr.id = '.quote($filtro['id']);
					break;
				case 'D':
					$sql = "SELECT inf.id_informe, 
									pr.id, pr.codigo,
									pr.tipo,
									inf.estado,
									'PDTS' AS tipo_desc,
									pr.descripcion,
									inf.fecha_presentacion
							FROM sap_proyectos_pdts AS prd
							LEFT JOIN sap_proyectos AS pr ON pr.id = prd.id_proyecto
							LEFT JOIN sap_proyectos_pdts_informe AS inf ON inf.id_proyecto = prd.id_proyecto";
					$where[] = 'pr.id = '.quote($filtro['id']);
					break;
				case 'C':
					$sql = "SELECT inf.id_informe, 
									0 as id, 
									pr.codigo,
									inf.estado, 
									'C' AS tipo,
									'Programa' AS tipo_desc, 
									pr.denominacion AS descripcion,
									inf.fecha_presentacion
							FROM sap_programa_informe AS inf
							LEFT JOIN sap_programas AS pr ON inf.id_programa = pr.codigo";
							
					$where[] = 'pr.codigo = '.quote($filtro['id']);
					break;
			}
		}
		if($sql){
			$sql .= " ORDER BY inf.fecha_presentacion DESC"; 
			return toba::db()->consultar(sql_concatenar_where($sql,$where));	
		}
	}

	function get_integrantes($filtro = array())
	{
		$where = array();
		$sql = "SELECT '(DNI: '||per.nro_documento||') - '||per.apellido||', '||per.nombres as ayn, 
					i.*,
					per.*,
					fun.*,
					cat.categoria,
					CASE horas_dedicacion 
					WHEN 1 THEN '1 - 4 horas semanales'
					WHEN 2 THEN '5 - 29 horas semanales'
					WHEN 3 THEN '30 o mas horas semanales'
					ELSE 'No declarado' END as horas_dedicacion_desc
				FROM sap_proyecto_integrante AS i
				LEFT JOIN sap_personas AS per USING (nro_documento)
				LEFT JOIN sap_proyecto_integrante_funcion AS fun ON fun.id_funcion = i.id_funcion
				LEFT JOIN sap_cat_incentivos AS cat 
					ON cat.nro_documento = i.nro_documento 
					AND convocatoria = (SELECT MAX(convocatoria) FROM sap_cat_incentivos WHERE nro_documento = i.nro_documento)
				ORDER BY i.id_funcion";

		if(isset($filtro['id'])){
			$where[] = 'i.id_proyecto = '.quote($filtro['id']);
		}
		if(isset($filtro['fecha'])){
			$where[] = quote($filtro['fecha']).' between i.fecha_desde and i.fecha_hasta';
		}
		
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_miembros($filtro,$tabla)
	{
		$where = array();
		if(isset($filtro['id_proyecto'])){
			$where[] = "id_proyecto = ".quote($filtro['id_proyecto']);
		}
		$sql = "SELECT * FROM $tabla";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		$resultado = toba::db()->consultar($sql);
		foreach($resultado as $miembro){
			$r[$miembro['nro_documento']] = $miembro; 
		}
		return isset($r) ? $r : array();
	}

	function get_motivos_recusacioon()
	{
		return toba::db()->consultar("SELECT * FROM sap_proy_motivo_recusacion");
	}

	function get_objetivos_ods()
	{
		return toba::db()->consultar("SELECT * FROM sap_objetivos_ods ORDER BY objetivo_ods");
	}

	function get_obj_especificos($id_proyecto,$desc_reducida=FALSE)
	{
		$campos = "id_obj_especifico, ";
		$campos .= ($desc_reducida) ? "SUBSTRING(obj_especifico,0,70)||('...') as obj_especifico" : "obj_especifico"; 
		$sql = "SELECT $campos 
				FROM sap_proyecto_obj_especifico 
				WHERE id_proyecto = ".quote($id_proyecto)." 
				ORDER BY id_obj_especifico ASC";
		return toba::db()->consultar($sql);
	}

	function get_objetivos_socioeconomicos()
	{
		return toba::db()->consultar("SELECT * FROM sap_objetivo_socioeconomico");
	}

	function get_objetivos_tareas($id_proyecto)
	{
		$sql = "SELECT tar.id_obj_especifico_tarea, tar.tarea, obj.id_obj_especifico, obj.obj_especifico
				FROM sap_obj_especifico_tarea AS tar
				LEFT JOIN sap_proyecto_obj_especifico AS obj ON tar.id_obj_especifico = obj.id_obj_especifico
				WHERE id_proyecto = ".quote($id_proyecto)."
				ORDER BY obj";
		return toba::db()->consultar($sql);
	}

	function get_objetivos_tiempos($id_proyecto)
	{
		$sql = "SELECT ti.id_obj_especifico, ti.semestre, ti.anio, obj.obj_especifico
				FROM sap_obj_especifico_tiempo AS ti
				LEFT JOIN sap_proyecto_obj_especifico AS obj USING (id_obj_especifico)
				WHERE id_proyecto = ".quote($id_proyecto);
				//echo nl2br($sql);
		return toba::db()->consultar($sql);
	}

	function get_participaciones_equipos($nro_documento)
	{
		$sql = "select distinct conv.nombre as convocatoria,
						e.codigo||': '||e.denominacion as equipo, 
						ei.condicion, 
						(select '(DNI: '||nro_documento||') '||nombres||' '||apellido from sap_personas where nro_documento = e.usuario_id) as cargado_por
				from sap_equipos_convocatorias as ec
				left join sap_equipo as e on e.id = ec.id_equipo
				left join sap_equipo_integrante as ei on ei.equipo_id = e.id
				left join sap_equipo_proyecto ep on ep.equipo_id = e.id
				left join sap_convocatoria as conv on conv.id = ec.id_convocatoria
				where replace(ei.dni,'.','') = ".quote($nro_documento);
		return toba::db()->consultar($sql);
	}

	function get_presupuesto_rubros()
	{
		return toba::db()->consultar("SELECT * FROM sap_proy_presupuesto_rubro");
	}

	function get_proyecto($id_proyecto = NULL)
	{
		
		$sql = "SELECT id, codigo || ' - ' || descripcion as descripcion
						FROM sap_proyectos
						WHERE id = ".quote($id_proyecto);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado) ? $resultado['descripcion'] : 'El proyecto no existe';
	}

	function get_proyectos($filtro = NULL,$solo_vigentes = FALSE)
	{
		$sql = "SELECT proy.id, 
						proy.codigo,
						proy.codigo || ' - ' || proy.descripcion AS cod_desc, 
					case when length(proy.descripcion) > 100 
						then '('||proy.codigo||') - '||substring(proy.descripcion,0,100)||'...' 
						else '('||proy.codigo||') - '||proy.descripcion end as descripcion_corta,
						proy.codigo || ' - ' || proy.descripcion AS cod_desc, 
						proy.descripcion, 
						proy.tipo, 
						proy.fecha_desde, 
						proy.fecha_hasta,
						dep.nombre AS dependencia,
						dir.apellido||', '||dir.nombres AS director,
        				dir.mail AS mail_director	
				FROM sap_proyectos AS proy
				LEFT JOIN sap_proyecto_integrante AS inte ON inte.id_proyecto = proy.id
					AND inte.id_funcion = (SELECT id_funcion FROM sap_proyecto_integrante_funcion WHERE identificador_perfil = 'D')
					AND inte.fecha_hasta = (SELECT max(fecha_hasta) from sap_proyecto_integrante WHERE id_proyecto = proy.id and id_funcion = inte.id_funcion)
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = inte.nro_documento 
				LEFT JOIN sap_dependencia AS dep ON dep.id = proy.sap_dependencia_id
				ORDER BY proy.codigo DESC";
		
		$where = array();
		
		if(is_array($filtro)){
			if(isset($filtro['descripcion'])){
				$where[] = "proy.descripcion ilike ".quote("%".$filtro['descripcion']."%");
			}
			if(isset($filtro['dirigido_por'])){
				//Se mantiene la columna nro_documento_dir y nro_documento_codir por compatibilidad
				
				/* Hay que generar todos los registros en la tabla sap_proyecto_integrante con todas las personas que figuran en 
				* los campos nro_documento_dir, nro_documento_codir y nro_documento_subdir. Despues, identificar todos los 
				* m�ulos y consultas que hacen referencia a esos campos, y luego recien eliminarlas, para dar paso al nuevo 
				* esquema de dos tablas separadas 
				*/
				$where[] = "(nro_documento_dir = ".quote($filtro['dirigido_por'])." 
							OR nro_documento_codir = ".quote($filtro['dirigido_por'])." 
							OR (proy.id IN (SELECT id_proyecto 
										FROM sap_proyecto_integrante 
										WHERE nro_documento = ".quote($filtro['dirigido_por'])."
										AND id_funcion IN (SELECT id_funcion 
											FROM sap_proyecto_integrante_funcion 
											WHERE identificador_perfil IN ('D','C','S')
										)
									)
								))";
			}
			if(isset($filtro['integrante'])){
				$subconsulta = "proy.id in (SELECT id_proyecto 
										FROM sap_proyecto_integrante 
										WHERE nro_documento IN 
											(
												SELECT nro_documento
												FROM sap_personas
												WHERE apellido ILIKE ".quote('%'.$filtro['integrante'].'%')."
												OR nombres ILIKE ".quote('%'.$filtro['integrante'].'%')."
												OR nro_documento = ".quote($filtro['integrante'])."
											)";
				if(isset($filtro['solo_integrantes_vigentes']) && $filtro['solo_integrantes_vigentes']){
					$subconsulta .= " AND current_date between fecha_desde and fecha_hasta";
				}
				$subconsulta .= ")";
				$where[] = $subconsulta;
			}
			if(isset($filtro['codigo'])){
				$where[] = "proy.codigo ILIKE '%{$filtro['codigo']}%'";
			}
			if(isset($filtro['convocatoria_anio'])){
				$where[] = "proy.convocatoria_anio = '{$filtro['convocatoria_anio']}'";
			}
			if(isset($filtro['solo_vigentes']) && $filtro['solo_vigentes']){
				$where[] = "current_date between proy.fecha_desde and proy.fecha_hasta";
			}
			if(isset($filtro['solo_aprobados']) && $filtro['solo_aprobados']){
				$where[] = $this->get_consulta_vigentes('proy');
			}

			if(isset($filtro['solo_propios']) && $filtro['solo_propios']){
				$where[] = "lower(proy.entidad_financiadora) ilike '%ec%ral%cnic%' AND LENGTH(proy.codigo) = 6";
			}
			if(isset($filtro['estado'])){
				$where[] = "proy.estado = ".quote($filtro['estado']);
			}
			if(isset($filtro['vigente_hasta'])){
				$where[] = "proy.fecha_hasta > ".quote($filtro['vigente_hasta']);
			}
			if(isset($filtro['id_dependencia'])){
				$where[] = "proy.sap_dependencia_id = ".quote($filtro['id_dependencia']);
			}
			if(isset($filtro['id_area_conocimiento'])){
				$where[] = "proy.sap_area_conocimiento_id = ".quote($filtro['id_area_conocimiento']);
			}
			
			if(count($where)){
				$sql = sql_concatenar_where($sql,$where);
			}
		}else{
			if(strlen(trim($filtro)) > 0 || $filtro != NULL){
				$filtro = quote("%{$filtro}%");
				$sql .= " WHERE proy.descripcion ILIKE {$filtro} OR proy.codigo ILIKE {$filtro} ;";	
			}
		}
		return toba::db()->consultar($sql);
	}

	function get_proyectos_busqueda($criterio)
	{
		$sql = "SELECT id,codigo, '('||codigo||') '||descripcion as descripcion
				FROM sap_proyectos 
				WHERE descripcion ILIKE ".quote("%".$criterio."%")." 
				AND '".date('Y-m-d')."' BETWEEN fecha_desde AND fecha_hasta 
				AND entidad_financiadora ILIKE '%Sec. Gral.%'";
		return toba::db()->consultar($sql);
	}

	function get_proyectos_busqueda_todos($criterio)
	{
		$sql = "SELECT id,codigo, '('||codigo||') '||descripcion as descripcion
				FROM sap_proyectos 
				WHERE descripcion ILIKE ".quote("%".$criterio."%") . "
				OR codigo ILIKE ".quote("%".$criterio."%");
		return toba::db()->consultar($sql);
	}

	function get_proyectosByFiltros($where = ' 1 = 1 ')
	{
		$sql = "SELECT
						p.id,   
						p.codigo,
						p.descripcion,
						case 
							when char_length(p.descripcion) > 65 then substring(p.descripcion,0,65)||'(...)'
							else p.descripcion 
							end AS descripcion_corta,
						codigo || ' - ' || descripcion as codigo_descripcion,
						p.fecha_desde,
						p.fecha_hasta,
						case 
							when char_length(p.entidad_financiadora) > 40 then substring(p.entidad_financiadora,0,40)||'(...)'
							else p.entidad_financiadora 
							end AS entidad_financiadora,
						p.director,
						p.co_director,
						p.sub_director,
						p.archivo_proyecto,
						p.tipo
					FROM
						sap_proyectos p
					WHERE {$where};";
							 
			return consultar_fuente($sql);
	}

	static function get_ProyectosEquipos($filtro = array()){
		$where = array();
		
		if(isset($filtro['codigo_proyecto'])){
			$where[] = "p.codigo = ".quote($filtro['codigo_proyecto']); 
		}
		if(isset($filtro['descripcion'])){
			$where[] = "p.descripcion ILIKE ".quote("%".$filtro['descripcion']."%"); 
		}
		if(isset($filtro['director'])){
			$where[] = "p.director ILIKE ".quote("%".$filtro['director']."%"); 
		}
			$sql = "SELECT " . self::CAMPOS . 
							",e.denominacion AS equipo_denominacion
								,e.coordinador,
								e.codigo AS codigo_equipo
			FROM sap_proyectos p 
			LEFT JOIN sap_equipo_proyecto ep ON ep.proyecto_id=p.id
			LEFT JOIN sap_equipo e ON e.id=ep.equipo_id
			WHERE 1=1
			ORDER BY p.codigo ASC;";//" GROUP BY e.id,d.nombre,a.nombre,e.usuario_id;";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}	
		return consultar_fuente($sql);
	}

	function get_proyectos_evaluar($filtro = array()) 
	{
		$usuario_actual = quote(toba::usuario()->get_id());
		$where = array();
		if(isset($filtro['tiempo_inicio'])){
			
			if($filtro['tiempo_inicio'] == 'nuevos'){
				$from = "SELECT 
						proy.id, 
						proy.descripcion, 
						proy.codigo, 
						proy.fecha_desde, 
						proy.fecha_hasta, 
						(select i.nro_documento 
							from sap_proyecto_integrante as i
							where i.id_proyecto = proy.id 
							and i.id_funcion = (select id_funcion from sap_proyecto_integrante_funcion where identificador_perfil = 'D')
							and i.fecha_desde = (select max(fecha_desde) from sap_proyecto_integrante where id_funcion = i.id_funcion and id_proyecto = proy.id)) as nro_documento_dir,
						proy.tipo, 
						--Esto se debe cambiar, es temporal
						proy.id_subarea,
						proy.sap_area_conocimiento_id
				FROM sap_proyectos AS proy
			    --La fecha actual tiene que estar entre la fecha de presentaci�n y el 30 de abril del a�o siguiente
			    WHERE ( 
			    		convocatoria_anio = extract(year from current_date) OR 
			    		current_date <= date(concat( (convocatoria_anio+1),'-04-30'))
			    	   )
			    /*WHERE current_date 
			       	between proy.fecha_desde and date(concat(extract(year from proy.fecha_desde),'-04-30'))*/
			    AND convocatoria_anio IS NOT NULL
			    AND entidad_financiadora ILIKE '%Sec. Gral.%'
			    AND estado = 'C'
			    
			    UNION
			    SELECT 0 AS id, 
					    prog.denominacion AS descripcion, 
					    prog.codigo, 
					    prog.fecha_desde, 
					    prog.fecha_hasta, 
					    prog.nro_documento_dir, 
					    'C' AS tipo, 
					    null as id_subarea,
					    null as sap_area_conocimiento_id
			    FROM sap_programas AS prog
			    --La fecha actual tiene que estar entre la fecha de presentaci�n y el 30 de abril del a�o siguiente
			    WHERE ( 
			    		convocatoria_anio = extract(year from current_date) OR 
			    		current_date <= date(concat( (convocatoria_anio+1),'-04-30'))
			    	   )
			   	AND convocatoria_anio IS NOT NULL";

				$evaluado_sql = "SELECT count(*) AS evaluaciones
						FROM sap_proy_pi_eval
						WHERE id_proyecto = proy.id
						AND estado = 'C'
						AND nro_documento_evaluador = $usuario_actual
						UNION
						SELECT count(*) AS evaluaciones
						FROM sap_proy_pdts_eval
						WHERE id_proyecto = proy.id
						AND estado = 'C'
						AND nro_documento_evaluador = $usuario_actual
						UNION
				        SELECT count(*) AS evaluaciones
						FROM sap_programa_eval AS eval
						LEFT JOIN sap_programas AS prog ON prog.codigo = eval.id_programa
						WHERE eval.id_programa = proy.codigo
						AND estado = 'C'
						and nro_documento_evaluador = $usuario_actual
						) AS tmp";
			}
			if($filtro['tiempo_inicio'] == 'anteriores'){
				$from = "
					SELECT proy.id, 
							proy.descripcion, 
							proy.codigo, 
							proy.fecha_desde, 
							proy.fecha_hasta, 
							(select i.nro_documento 
								from sap_proyecto_integrante as i
								where i.id_proyecto = proy.id 
								and i.id_funcion = (select id_funcion from sap_proyecto_integrante_funcion where identificador_perfil = 'D')
								and i.fecha_desde = (select max(fecha_desde) from sap_proyecto_integrante where id_funcion = i.id_funcion and id_proyecto = proy.id)) as nro_documento_dir,
							proy.tipo, 
							--Esto se debe cambiar, es temporal
							proy.id_subarea,
							proy.sap_area_conocimiento_id
				    FROM sap_proyectos_pi_informe AS inf
				    LEFT JOIN sap_proyectos_pi AS pi ON pi.id_proyecto = inf.id_proyecto
				    LEFT JOIN sap_proyectos AS proy ON proy.id = pi.id_proyecto 
					--La fecha actual tiene que estar entre la fecha de presentaci�n y el 30 de abril del a�o siguiente
				    WHERE current_date 
				    	between inf.fecha_presentacion and date(concat(extract(year from inf.fecha_presentacion)+1,'-04-30'))
				    UNION
				    SELECT proy.id, 
				    		proy.descripcion, 
				    		proy.codigo, 
				    		proy.fecha_desde, 
				    		proy.fecha_hasta, 
				    		(select i.nro_documento 
								from sap_proyecto_integrante as i
								where i.id_proyecto = proy.id 
								and i.id_funcion = (select id_funcion from sap_proyecto_integrante_funcion where identificador_perfil = 'D')
								and i.fecha_desde = (select max(fecha_desde) from sap_proyecto_integrante where id_funcion = i.id_funcion and id_proyecto = proy.id)) as nro_documento_dir,
				    		proy.tipo, 
				    		proy.id_subarea,
							proy.sap_area_conocimiento_id
				    FROM sap_proyectos_pdts_informe AS inf
				    LEFT JOIN sap_proyectos_pdts AS pdts ON pdts.id_proyecto = inf.id_proyecto
				    LEFT JOIN sap_proyectos AS proy ON proy.id = pdts.id_proyecto 
					--La fecha actual tiene que estar entre la fecha de presentaci�n y el 30 de abril del a�o siguiente
				    WHERE current_date 
				    	between inf.fecha_presentacion and date(concat(extract(year from inf.fecha_presentacion)+1,'-04-30'))
				    UNION
				    SELECT 0 AS id, 
				    		prog.denominacion AS descripcion, 
				    		prog.codigo, 
				    		prog.fecha_desde, 
				    		prog.fecha_hasta, 
				    		prog.nro_documento_dir, 
				    		'C' AS tipo, 
				    		null as id_subarea,
					    	null as sap_area_conocimiento_id 
				    FROM sap_programa_informe AS inf
				    LEFT JOIN sap_programas AS prog ON prog.codigo = inf.id_programa
				    --La fecha actual tiene que estar entre la fecha de presentaci�n y el 30 de abril del a�o siguiente
				    WHERE current_date 
				    	between inf.fecha_presentacion and date(concat(extract(year from inf.fecha_presentacion)+1,'-04-30'))";

				$evaluado_sql = "SELECT count(*) AS evaluaciones
						FROM sap_proy_pi_informe_eval as eval
						LEFT JOIN sap_proyectos_pi_informe as inf ON inf.id_informe = eval.id_informe
						WHERE inf.id_proyecto = proy.id
						AND inf.estado = 'C'
						AND eval.estado = 'C'
						AND nro_documento_evaluador = $usuario_actual
						AND inf.id_informe = (select MAX(id_informe) FROM sap_proyectos_pi_informe WHERE id_proyecto = proy.id)
						UNION
						SELECT count(*) AS evaluaciones
						FROM sap_proy_pdts_informe_eval as eval
						LEFT JOIN sap_proyectos_pdts_informe as inf ON inf.id_informe = eval.id_informe
						WHERE inf.id_proyecto = proy.id
						AND inf.estado = 'C'
						AND eval.estado = 'C'
						AND nro_documento_evaluador = $usuario_actual
						
						--Este ultimo AND verifica si ya evalu� el �ltimo informe presentado (y no alguno anterior)
						AND inf.id_informe = (select MAX(id_informe) FROM sap_proyectos_pdts_informe WHERE id_proyecto = proy.id)
				        UNION
				        SELECT count(*) AS evaluaciones
						FROM sap_programa_informe_eval as eval
						LEFT JOIN sap_programa_informe as inf ON inf.id_informe = eval.id_informe
						WHERE inf.id_programa = proy.codigo
						AND inf.estado = 'C'
						AND eval.estado = 'C'
						and nro_documento_evaluador = $usuario_actual
						
						--Este ultimo AND verifica si ya evalu� el �ltimo informe presentado (y no alguno anterior)
						AND inf.id_informe = (select MAX(id_informe) FROM sap_programa_informe WHERE id_programa = proy.codigo)
						) AS tmp";
			}
		}

		$sql = "SELECT proy.id, 
					codigo, 
					substr(proy.descripcion,1,100)||'(...)' AS descripcion, 
					nro_documento_dir,
					fecha_desde, 
					fecha_hasta,
					tipo,
					coalesce(ac.nombre,sub.subarea) AS area_conocimiento,
					--ac.nombre AS area_conocimiento,
					CASE proy.tipo WHEN '0' THEN 'PI' WHEN 'D' THEN 'PDTS' WHEN 'C' THEN 'Programa' END AS tipo_descripcion,
					(SELECT nombre FROM sap_dependencia WHERE letra_codigo_proyectos = substr(codigo,3,1) limit 1) AS unidad_academica,
					(SELECT CASE WHEN sum(evaluaciones) > 0 THEN 'S' ELSE 'N' END FROM ($evaluado_sql) AS evaluado,
					(SELECT apellido||', '||nombres FROM sap_personas WHERE nro_documento = proy.nro_documento_dir) AS director
				FROM ($from) AS proy
				LEFT JOIN sap_area_conocimiento AS ac ON proy.sap_area_conocimiento_id = ac.id
				LEFT JOIN sap_proyecto_subarea AS sub ON proy.id_subarea = sub.id_subarea
				WHERE tipo <> '9'
				ORDER BY codigo ASC";
		
		if(isset($filtro['tipo'])){
			$where[] = "tipo = ".quote($filtro['tipo']);
		}
		if(isset($filtro['codigo'])){
			$where[] = "codigo ilike ".quote($filtro['codigo']);
		}

		if(isset($filtro['usuario'])){
			$consulta = "SELECT proyectos_codigos FROM sap_proyecto_evaluador WHERE nro_documento = ".quote($filtro['usuario']);
			$codigos = toba::db()->consultar_fila($consulta);
			
			if(count($codigos)){
				//No se hace un str_replace porque vienen apellidos y nombres con comas
				$codigos = explode(';',$codigos['proyectos_codigos']);
				$codigos = implode("','",$codigos);
				$where[] = "codigo IN ('".$codigos."')";
			}
		}
		if(count($where)){

			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_proyectos_ef_editable($patron)
	{
		$filtro = quote("%".$patron."%");
		$sql = "SELECT id, codigo||' - '||descripcion as descripcion
				FROM sap_proyectos 
				WHERE descripcion ILIKE $filtro
				OR codigo ILIKE $filtro
				ORDER BY codigo DESC
				LIMIT 20";
		return toba::db()->consultar($sql);
	}

	function get_proyectos_ef_editable_insc_becas($patron)
	{
		$filtro = quote("%".$patron."%");
		$sql = "SELECT id, codigo||' - '||descripcion as descripcion
				FROM sap_proyectos 
				WHERE (quitar_acentos(descripcion) ILIKE quitar_acentos($filtro)
				OR codigo ILIKE $filtro)
				--Este AND valida que el proyecto esté vigente cuando inicie la beca (en marzo del año siguiente)
				AND fecha_hasta > '" . (date('Y') + 1) . "-03-01'
				AND estado = 'C'
				ORDER BY codigo DESC
				LIMIT 10";
		return toba::db()->consultar($sql);
	}


	function get_proyectos_que_integra($nro_documento,$solo_vigentes=TRUE)
	{
		$sql = "SELECT distinct pr.codigo
				FROM sap_proyecto_integrante AS pi
				LEFT JOIN sap_proyectos AS pr ON pr.id = pi.id_proyecto
				WHERE nro_documento = ".quote($nro_documento)."
				AND pr.estado = 'C'";
				if($solo_vigentes){
					$sql .= " AND ((pi.fecha_hasta > current_date) OR (pi.fecha_hasta IS null))";	
				}
		return toba::db()->consultar($sql);
				
	}

	function get_proyectos_que_integra_fecha($nro_documento,$fecha_referencia=NULL)
	{
		$fecha_referencia = ($fecha_referencia) ? $fecha_referencia : date('Y-m-d');
		$sql = "SELECT distinct pr.codigo
				FROM sap_proyecto_integrante AS pi
				LEFT JOIN sap_proyectos AS pr ON pr.id = pi.id_proyecto
				WHERE nro_documento = ".quote($nro_documento)." 
				AND '$fecha_referencia' between pi.fecha_desde and pi.fecha_hasta
				AND pr.estado = 'C'";	
		return toba::db()->consultar($sql);
				
	}


	function get_proyectoVigentes($id_proyecto = NULL)
	{
		if (! isset($id_proyecto))
		{
			return array();
		}
		
		$id = quote($id_proyecto);
		$sql = "SELECT id
					   ,codigo || ' - ' || descripcion as descripcion
						FROM sap_proyectos
						WHERE id = $id AND fecha_hasta <=current_date()";
		$resultado = toba::db()->consultar_fila($sql);

		if (! empty($resultado))
		{
			return $resultado['descripcion'];
		}
	}


	/**
	 * Retorna todos los datos del proyecto (COMPLETO)
	 * @param  integer $id_proyecto ID del proyecto a buscar
	 * @return array              
	 */
	function get_reporte_proyecto($id_proyecto)
	{
		$sql = "SELECT proy.id AS id_proyecto,
						proy.codigo,
						proy.descripcion as descripcion,
						proy.tipo,
						proy.fecha_desde,
						proy.fecha_hasta,
						gr.denominacion AS denominacion_grupo,
						proy.palabras_clave,
						proy.objetivo_general,
						proy.resumen, 
						proy.descripcion_metodologica,
						proy.estado_conocimiento,
						proy.trabajos_previos,
						proy.bibliografia,
						proy.justif_subdirector,
						proy.justif_futuros_integrantes,
						proy.recursos_comprometidos,
						dep.nombre AS dependencia_desc
				FROM sap_proyectos AS proy
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = proy.id_grupo
				LEFT JOIN sap_dependencia AS dep ON dep.id = proy.sap_dependencia_id
				WHERE proy.id = ".quote($id_proyecto);
		$datos['general'] = toba::db()->consultar_fila($sql);

		if($datos['general']['tipo'] == '0'){
			$sql = "SELECT pi.tipo_investigacion,
						pi.resultados_esperados,
						pi.aplicacion_resultados,
						pi.efectos_sistema_cient,
						pi.efectos_actividad_univ
					FROM sap_proyectos_pi AS pi
					WHERE pi.id_proyecto = ".quote($id_proyecto);
			$datos['especifico'] = toba::db()->consultar_fila($sql);
		}

		if($datos['general']['tipo'] == 'D'){
			$sql = "SELECT pdts.producto,
						pdts.originalidad,
						pdts.relevancia,
						pdts.pertinencia,
						pdts.demanda
					FROM sap_proyectos_pdts AS pdts
					WHERE pdts.id_proyecto = ".quote($id_proyecto);
			$datos['especifico'] = toba::db()->consultar_fila($sql);
		}

		$sql = "SELECT pres.anio,rub.rubro,pres.descripcion,pres.justificacion,pres.monto
				FROM sap_proy_presupuesto AS pres
				LEFT JOIN sap_proy_presupuesto_rubro AS rub ON rub.id_rubro = pres.id_rubro
				WHERE pres.id_proyecto = ".quote($id_proyecto)."
				ORDER BY pres.anio ASC, rub.rubro";
		$datos['presupuesto'] = toba::db()->consultar($sql);

		$sql = "SELECT obj_especifico, ARRAY_TO_STRING(ARRAY_AGG(ta.tarea),'||') AS tareas
				FROM sap_proyecto_obj_especifico AS obj
				LEFT JOIN sap_obj_especifico_tarea AS ta ON ta.id_obj_especifico = obj.id_obj_especifico
				WHERE id_proyecto = ".quote($id_proyecto)."
				group by obj_especifico";
		$datos['objetivos'] = toba::db()->consultar($sql);

		$datos['integrantes'] = $this->get_integrantes(array('id'=>$id_proyecto));


		return $datos;
	}

	function get_subareas_proyecto($id_area = NULL)
	{

		$sql = "SELECT id_area, id_subarea, subarea FROM sap_proyecto_subarea";
		$sql .= ($id_area) ? " WHERE id_area = ".quote($id_area) : "";
		return toba::db()->consultar($sql);
	}


	function get_tareas_objetivo($id_obj_especifico)
	{
		return toba::db()->consultar('SELECT id_obj_especifico_tarea, tarea FROM sap_obj_especifico_tarea WHERE id_obj_especifico = '.quote($id_obj_especifico));
	}

	function get_tareas_pendientes_pdts($id_obj_especifico)
	{
		$sql = 'SELECT tar.id_obj_especifico_tarea, tar.tarea 
				FROM sap_obj_especifico_tarea AS tar 
				WHERE tar.id_obj_especifico = '.quote($id_obj_especifico)."
				AND NOT EXISTS (SELECT * FROM sap_pdts_inf_meta_alc WHERE id_obj_especifico_tarea = tar.id_obj_especifico_tarea)";
		return toba::db()->consultar($sql);	
	}

	function get_tipos_apoyo()
	{
		return toba::db()->consultar("SELECT * FROM sap_proyecto_apoyo_tipo");
	}

	function get_tipo_proyecto($id_proyecto)
	{
		$sql = "SELECT tipo FROM sap_proyectos WHERE id = ".quote($id_proyecto);
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['tipo'];

	}

	function get_valor_campo($campo,$filtro)
	{
		$campos = $this->get_campo(array($campo),$filtro);
		return isset($campos[0]) ? $campos[0][$campo] : FALSE;

	}

	function hay_convocatoria_abierta()
	{
		return count(toba::db()->consultar("SELECT * FROM sap_convocatoria WHERE aplicable = 'PROYECTOS' and current_date between fecha_desde AND fecha_hasta"));
	}

	/**
	 * Retorna todas las instancias de evaluaci�n que se presentaron para un proyecto especifico. Retorna tanto la instancia de presentaci�n inicial, como las distintas instancias de avance.
	 * @return array Array con las instancias de avances presentados por el proyecto
	 */
	function presentaciones_evaluacion_proyecto($id_proyecto){
		$sql = "SELECT *, extract(year from fecha_desde) as anio_desde, 
						  extract(year from fecha_presentacion) as anio_presentacion,
						  extract(year from fecha_hasta) as anio_hasta
				FROM (
					SELECT fecha_desde AS fecha_presentacion, id as id_proyecto,0 as id_informe
					FROM sap_proyectos
					WHERE id = ".quote($id_proyecto)."
					UNION
					SELECT fecha_presentacion, id_proyecto,id_informe
					FROM sap_proyectos_pi_informe
					WHERE id_proyecto = ".quote($id_proyecto)."
					UNION
					SELECT fecha_presentacion, id_proyecto,id_informe
					FROM sap_proyectos_pdts_informe
					WHERE id_proyecto = ".quote($id_proyecto)."
					) AS instancias
				LEFT JOIN sap_proyectos AS proy ON proy.id = instancias.id_proyecto";
		
		$presentaciones = toba::db()->consultar($sql);
		foreach ($presentaciones as &$presentacion) {
			$presentacion['instancia'] = $this->determinar_instancia_evaluacion($presentacion);	
		}
		return $presentaciones;
	}

	/**
	 * Retorna true si el evaluador ya cerro todas las evaluaciones que tenia asignadas.
	 */
	function puede_descargar_certificado($nro_documento_evaluador)
	{
		$sql = "SELECT puede_descargar_certificado FROM sap_proyecto_evaluador WHERE nro_documento = ".quote($nro_documento_evaluador);
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['puede_descargar_certificado'];

	}

	//Valida que el integrante, no integre dos proyectos o mas, al a�o siguiente (que es cuando empezar� el siguiente)
	function puede_integrar_nuevo_proyecto($nro_documento,$excluir = array())
	{
		if(count($excluir)){
			$codigos = '';
			foreach ($excluir as $codigo) {
				$codigos .= "'".$codigo."',";
			}
			$codigos = substr($codigos, 0,strlen($codigos) -1);
		}
		$sql = "SELECT * 
				FROM sap_proyecto_integrante AS pi
				LEFT JOIN sap_proyectos AS pr ON pr.id = pi.id_proyecto
				WHERE nro_documento = ".quote($nro_documento)." 
				AND pr.estado = 'C'
				--Se consideran todos los proyectos vigentes que va a tener el a�o entrante
				AND (extract(year from current_date)+1) between extract(year from pi.fecha_desde) and extract(year from pi.fecha_hasta)";
		if(count($excluir)){
			$sql .= " AND pr.id not in (".$codigos.")";
		}
		return (count(toba::db()->consultar($sql)) < 2);

	}

		/**
	 * VALIDA QUE TODOS LOS INTEGRANTES DECLARADOS, TENGAN SUS REGISTROS AUXILIARES EN TABLAS SATELITE
	 */
	function tiene_registro_auxiliar($integrante)
	{
		$filtro = 'nro_documento      = '.quote($integrante['nro_documento']);
		$filtro .= ' AND id_funcion   = '.quote($integrante['id_funcion']);
		$filtro .= ' AND id_proyecto  = '.quote($integrante['id_proyecto']);
		
		//Si la funcion del integrante no debe tener auxiliar, se retorna TRUE
		$sin_auxiliares = $this->get_funciones_sin_auxiliares();
		$sin_auxiliares = array_column($sin_auxiliares, 'id_funcion');
		if( ! in_array($integrante['id_funcion'], $sin_auxiliares)){
			return TRUE;
		}

		$sql = "SELECT nro_documento, id_proyecto FROM sap_proyecto_alumno WHERE $filtro
		UNION SELECT nro_documento, id_proyecto   FROM sap_proyecto_becario WHERE $filtro
		UNION SELECT nro_documento, id_proyecto   FROM sap_proyecto_tesista WHERE $filtro
		UNION SELECT nro_documento, id_proyecto   FROM sap_proyecto_inv_externo WHERE $filtro
		UNION SELECT nro_documento, id_proyecto   FROM sap_proyecto_apoyo WHERE $filtro";
		return count(toba::db()->consultar($sql));

	}
}
?>