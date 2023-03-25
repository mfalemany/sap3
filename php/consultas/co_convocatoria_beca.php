<?php
class co_convocatoria_beca
{

	function get_convocatorias($filtro=array(),$solo_vigentes=TRUE)
	{
		$where = array();
		if (isset($filtro['id_tipo_convocatoria'])) {
			$where[] = "conv.id_tipo_convocatoria = ".quote($filtro['id_tipo_convocatoria']);
		}
		if (isset($filtro['id_convocatoria'])) {
			$where[] = "conv.id_convocatoria = ".quote($filtro['id_convocatoria']);
		}
		if (isset($filtro['convocatoria'])) {
			$where[] = "conv.convocatoria ILIKE ".quote('%'.$filtro['convocatoria'].'%');
		}
		if (isset($filtro['id_tipo_convocatoria'])) {
			$where[] = "conv.id_convocatoria IN (SELECT id_convocatoria FROM be_convocatoria_beca WHERE id_tipo_convocatoria = " . quote($filtro['id_tipo_convocatoria']) . ")";
		}
		if (isset($filtro['anio'])) {
			$where[] = quote($filtro['anio'])." between extract(year from conv.fecha_desde) and extract(year from conv.fecha_hasta)";
		}
		
		$sql = "SELECT
			conv.id_convocatoria,
			tip.id_tipo_convocatoria,
			tip.tipo_convocatoria,
			conv.convocatoria,
			conv.fecha_desde,
			conv.fecha_hasta,
			conv.limite_movimientos
		FROM be_convocatoria_beca as conv
		LEFT JOIN be_tipos_convocatoria as tip on tip.id_tipo_convocatoria = conv.id_tipo_convocatoria
		WHERE 1=1";
		$sql .= $solo_vigentes ? " AND current_date BETWEEN conv.fecha_desde AND conv.fecha_hasta" : "";
		$sql .= " ORDER BY conv.fecha_desde DESC";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		// Si se recibió un ID de convocatoria, se retornan únicamente los datos de la misma
		if (isset($filtro['id_convocatoria'])) {
			return toba::db()->consultar_fila($sql);
		}

		return toba::db()->consultar($sql);
	}

	function get_anios_convocatorias()
	{
		$sql = "select distinct extract(year from fecha_desde) as anio 
				FROM be_convocatoria_beca
				ORDER BY anio DESC";
		
		return toba::db()->consultar($sql);
	}



	function existen_inscripciones($id_convocatoria,$id_tipo_beca = NULL)
	{
		$sql = "SELECT count(*) AS cantidad 
		FROM be_inscripcion_conv_beca 
		WHERE id_convocatoria = ".quote($id_convocatoria);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cantidad'] > 0);
	}

	function get_convocatorias_todas($filtro = array())
	{
		return $this->get_convocatorias($filtro,FALSE);
	}

	function existen_convocatorias_vigentes()
	{
		$sql = "select count(*) as cant from be_convocatoria_beca where current_date between fecha_desde and fecha_hasta";
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cant'] > 0);

	}

	function get_campo($campo, $id_convocatoria)
	{
		$sql = "SELECT $campo FROM be_convocatoria_beca WHERE id_convocatoria = ".quote($id_convocatoria)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		if( array_key_exists($campo,$resultado)){
			if($resultado[$campo]){
				return $resultado[$campo];
			}
		}
		return FALSE;
	}

	/**
	 * Retorna un listado de convocatorias para la cual no existan inscripciones realizadas.
	 * @return array Array de convocatorias (vigentes o no) que no hayan registrado inscripciones
	 */
	function get_convocatorias_sin_inscripciones()
	{
		
	}

	function get_id_ultima_convocatoria($solo_propias = FALSE)
	{
		$sql = "select max(id_convocatoria) as id_convocatoria from be_convocatoria_beca";
		$sql .= ($solo_propias) ? ' where id_tipo_convocatoria = 3' : '';
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['id_convocatoria']) ? $resultado['id_convocatoria'] : NULL;
	}

	function get_tipos_convocatoria()
	{
		$sql = "SELECT id_tipo_convocatoria, tipo_convocatoria FROM be_tipos_convocatoria ORDER BY tipo_convocatoria";
		return toba::db()->consultar($sql);
	}

	function permite_evaluacion_comisiones($id_convocatoria)
	{
		$sql = "SELECT permite_eval_comisiones 
				FROM be_convocatoria_beca 
				WHERE id_convocatoria = " . quote($id_convocatoria);
		$resultado = toba::db()->consultar_fila($sql);
		return (isset($resultado['permite_eval_comisiones']) && $resultado['permite_eval_comisiones']);
	}

	function permite_evaluacion_junta($id_convocatoria)
	{
		$sql = "SELECT permite_eval_junta
				FROM be_convocatoria_beca 
				WHERE id_convocatoria = " . quote($id_convocatoria);
		$resultado = toba::db()->consultar_fila($sql);
		return (isset($resultado['permite_eval_junta']) && $resultado['permite_eval_junta']);
	}
}
?>