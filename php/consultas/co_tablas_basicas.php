<?php
class co_tablas_basicas
{
	function buscar_localidad($patron)
	{
		$sql = "SELECT id_localidad, 
						loc.localidad||' - '||prov.provincia||' - '||pai.pais AS localidad 
				FROM be_localidades as loc
				LEFT JOIN be_provincias as prov on prov.id_provincia = loc.id_provincia
				LEFT JOIN be_paises as pai ON pai.id_pais = prov.id_pais
				WHERE localidad ilike ".quote('%'.$patron.'%');
		return toba::db()->consultar($sql);
	}

	function carrera_descripcion($id)
	{
		$carrera = toba::db()->consultar_fila('SELECT carrera FROM be_carreras WHERE id_carrera = '.quote($id));
		return $carrera['carrera'];
	}

	static function eliminar_evaluadores()
	{
		$sql = "DELETE FROM sap_evaluadores";
		return toba::db()->ejecutar($sql);
	}

	//retorna todos los a?s en los que se registr?al menos una convocatoria
	function get_anios_convocatorias()
	{   
		$sql = "SELECT DISTINCT convocatoria FROM sap_cat_incentivos ORDER BY convocatoria DESC";
		return toba::db()->consultar($sql);
	}

	function get_areas_conocimiento()
	{
		$sql = "SELECT id, descripcion, nombre, aplicable, disciplinas_incluidas, prefijo_orden_poster FROM sap_area_conocimiento";
		return toba::db()->consultar($sql);
	}

	function get_areas_conocimiento_becarios(){
		$sql = "SELECT id, nombre, descripcion, aplicable FROM sap_area_conocimiento WHERE aplicable IN ('BECARIOS','AMBOS')
			ORDER BY nombre ASC;";
		return consultar_fuente($sql);
	}

		function get_areas_conocimiento_equipos(){
		$sql = "SELECT id, nombre, descripcion, aplicable FROM sap_area_conocimiento WHERE aplicable IN ('EQUIPOS')
			ORDER BY nombre ASC;";
		return consultar_fuente($sql);
	}

	function get_campo($tabla, $campo, $filtro = array())
	{
		$where = array();
		$sql = "SELECT $campo FROM $tabla LIMIT 1";
		if(isset($filtro['id_tipo_beca'])){
			$where[] = 'id_tipo_beca = ' . quote($filtro['id_tipo_beca']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}

		$resultado = toba::db()->consultar_fila($sql);
		if( array_key_exists($campo,$resultado)){
			if($resultado[$campo]){
				return $resultado[$campo];
			}
		}
		return FALSE;
	}

	function get_campos_aplicacion($filtro = array())
	{
		$where = array();
		$sql = "SELECT * FROM sap_campos_aplicacion";
		if(isset($filtro['id_campo_aplicacion'])){
			$where[] = "id_campo_aplicion = ".quote($filtro['id_campo_aplicacion']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_campo_de_subcampo($id_subcampo)
	{
		$sql = "SELECT id_campo_aplicacion FROM sap_subcampos_aplicacion WHERE id_subcampo_aplicacion = ".quote($id_subcampo)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['id_campo_aplicacion'];
	}

	function get_campos_tabla($campos,$tabla,$where=NULL,$limit=NULL)
	{   
		$campos = (is_array($campos)) ? implode(',',$campos) : $campos;
		$sql = "SELECT $campos FROM $tabla";
		$sql = ($where) ? $sql." where ".$where : $sql;
		$sql = ($limit) ? $sql." LIMIT ".$limit : $sql;
		return toba::db()->consultar($sql);
	}



	function get_cargos()
	{
		$sql = "select cargo, descripcion from sap_cargos_descripcion where activo = 'S'";
		return toba::db()->consultar($sql);
	}


	function get_cargos_dependencia()
	{
		return toba::db()->consultar("SELECT * FROM sap_dependencia_cargos");
	}

	function get_cargos_filtro($filtro = array())
	{

		$where = array();
		if(isset($filtro['sigla_mapuche'])){
			$where[] = 'dep.sigla_mapuche = '.quote($filtro['sigla_mapuche']);
		}
		if(isset($filtro['solo_externos'])){
			$where[] = 'car.nro_cargo_mapuche is null';
		}
		$sql = "SELECT * 
				FROM sap_cargos_persona AS car
				LEFT JOIN sap_dependencia AS dep ON dep.sigla_mapuche = car.dependencia";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_carreras($filtro=array())
	{
		$where = array();
		if (isset($filtro['carrera'])) {
			$where[] = "car.carrera ILIKE ".quote("%{$filtro['carrera']}%");
		}
		if (isset($filtro['id_dependencia'])) {
			$where[] = "dep.id_dependencia = ".$filtro['id_dependencia'];
		}
		if (isset($filtro['id_carrera'])) {
			$where[] = "car.id_carrera = ".$filtro['id_carrera'];
		}

		$sql = "SELECT DISTINCT
			car.id_carrera,
			car.carrera,
			car.cod_araucano,
			prom_hist_egre,
			desv_estandar
		FROM be_carreras as car
		ORDER BY car.carrera";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	function get_carreras_editable($patron = "")
	{
		return $this->get_carreras(array('carrera'=>$patron));
	}

	function get_carreras_por_dependencia($id_dep = NULL) 
	{
		if( ! $id_dep){
			return; 
		}
		
		$sql = "SELECT
			car.id_carrera,
			car.carrera,
			car.cod_araucano
		FROM be_carrera_dependencia as cd 
		LEFT JOIN be_carreras as car on cd.id_carrera = car.id_carrera
		LEFT JOIN sap_dependencia as dep on dep.id = cd.id_dependencia
		WHERE dep.id = $id_dep
		ORDER BY dep.nombre, car.carrera";
		return toba::db()->consultar($sql);
	}

	function get_categorias_conicet()
	{
		$sql = "SELECT * FROM be_cat_conicet";
		return toba::db()->consultar($sql);
	}

	function get_criterios_evaluacion()
	{
		$sql = "SELECT cri.id_convocatoria, 
					cri.id_tipo_beca, 
					cri.id_criterio_evaluacion, 
					cri.puntaje_maximo, 
					cri.criterio_evaluacion,
					conv.convocatoria,
					tip.tipo_beca
			FROM be_tipo_beca_criterio_eval AS cri
			LEFT JOIN be_tipos_beca AS tip ON tip.id_tipo_beca = cri.id_tipo_beca
			LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = cri.id_convocatoria";
		return toba::db()->consultar($sql);
	}

	function get_dependencias($filtro=array())
	{
		$where = array();
		if(isset($filtro['nombre'])) {
			$where[] = "nombre ILIKE ".quote("%{$filtro['nombre']}%");
		}
		if(isset($filtro['id_universidad'])) {
			$where[] = "dep.id_universidad = ".quote($filtro['id_universidad']);
		}
		if(isset($filtro['con_letra_codigo_proyectos'])){
			$where[] = 'letra_codigo_proyectos is not null';
		}		
		$sql = "SELECT
			dep.id,
			dep.nombre,
			dep.descripcion,
			dep.sigla_mapuche,
			letra_codigo_proyectos,
			uni.universidad as id_universidad,
			uni.universidad
		FROM sap_dependencia as dep	
		LEFT JOIN be_universidades AS uni ON dep.id_universidad = uni.id_universidad
		ORDER BY dep.nombre";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	static function get_dependencias_cargos(){
		$sql = "SELECT
				dep.id,
				uni.sigla||' - '||dep.nombre as nombre,
				dep.descripcion,
				COALESCE(dep.sigla_mapuche,'----') as sigla_mapuche
			FROM sap_dependencia as dep
			LEFT JOIN be_universidades AS uni ON uni.id_universidad = dep.id_universidad
			WHERE (uni.sigla||' - '||dep.nombre) is not null;";
		return consultar_fuente($sql);
	}

	/**
	 * Retorna un listado de Unidades Acad?icas con sus respectivas letras para la generaci? de c?igos para proyectos y programas.
	 * @return array Array con listados de unidades academicas con sus letras.
	 */
	function get_dependencias_letras_proyectos()
	{
		$sql = "SELECT letra_codigo_proyectos, nombre FROM sap_dependencia WHERE letra_codigo_proyectos IS NOT NULL";
		return toba::db()->consultar($sql);
	}

	static function get_dependencia_nombre($id){
		$sql = "SELECT nombre FROM sap_dependencia WHERE id = {$id};";
		return toba::db()->consultar_fila($sql);
	}

	function get_dependencias_subsidios(){
		return $this->get_dependencias(array('con_letra_codigo_proyectos'));
	}

	static function get_dependencias_unne()
	{
		return toba::db()->consultar('SELECT id,nombre FROM sap_dependencia WHERE id_universidad = 1 ORDER BY nombre');
	}

	function get_disciplinas($filtro = array())
	{
		$sql = "SELECT * FROM sap_disciplinas";
        return toba::db()->consultar($sql);
	}

	function get_disciplinas_incluidas($id_area_conocimiento)
	{
		$sql = "SELECT disciplinas_incluidas FROM sap_area_conocimiento WHERE id = ".quote($id_area_conocimiento);
		$resultado = toba::db()->consultar_fila($sql);
		return count($resultado) ? $resultado['disciplinas_incluidas'] : '';
	}

	function get_estados_civiles(){
		return toba::db()->consultar("SELECT * FROM sap_estado_civil ORDER BY estado_civil");
	}

	static function get_evaluaciones(){
		$sql = "SELECT id, nombre FROM sap_evaluacion;";
		return toba::db()->consultar($sql);
	}

	function get_evaluadores($area_conocimiento = false)
	{
		$sql = "SELECT eva.evaluador, eva.id_area_conocimiento, a_con.descripcion
			FROM sap_evaluadores AS eva
			JOIN sap_area_conocimiento as a_con on a_con.id = eva.id_area_conocimiento";
		if($area_conocimiento !== false && is_numeric($area_conocimiento) ){
			$sql .= " WHERE eva.id_area_conocimiento = $area_conocimiento";
		}
		$sql .= " ORDER BY eva.evaluador";		
		return toba::db()->consultar($sql);

	}

	function get_letra_dependencia($id_dependencia)
	{	
		$sql = "SELECT letra_codigo_proyectos FROM sap_dependencia WHERE id = ".quote($id_dependencia);
		$res = toba::db()->consultar_fila($sql);
		return $res['letra_codigo_proyectos'];
	}

	function get_localidad_provincia_pais($id_localidad)
	{
		$sql = "SELECT loc.localidad||' - '||prov.provincia||' - '||pai.pais as localidad
				FROM be_localidades as loc
				LEFT JOIN be_provincias as prov on prov.id_provincia = loc.id_provincia
				LEFT JOIN be_paises as pai ON pai.id_pais = prov.id_pais
				WHERE loc.id_localidad = ".quote($id_localidad);
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['localidad'];
	}

	function get_niveles_academicos()
	{
		return toba::db()->consultar("select * from be_niveles_academicos order by orden ASC");
	}

	function get_pais_descripcion($id_pais)
	{
		$pais = toba::db()->consultar_fila("SELECT pais FROM be_paises WHERE id_pais = ".quote($id_pais));
		return (isset($pais['pais']) && $pais['pais']) ? $pais['pais'] : FALSE;
	}
	function get_paises_ef_editable($patron = '')
	{
		return toba::db()->consultar("SELECT id_pais, pais FROM be_paises WHERE pais ILIKE " . quote("%".$patron."%") . " LIMIT 10");
	}


	function get_parametro_conf($parametro)
	{
		$sql = "SELECT valor AS parametro FROM sap_configuraciones WHERE parametro = ".quote($parametro);
		$resultado = toba::db()->consultar_fila($sql);
		return (count($resultado)) ? $resultado['parametro'] : FALSE;
	}


	function get_parametros_sistema()
	{
		$sql = "SELECT * FROM sap_configuraciones";
		return toba::db()->consultar($sql);
	}

	function get_prom_hist_egresados($id_carrera)
	{
		$sql = "SELECT prom_hist_egre FROM be_carreras WHERE id_carrera = ".quote($id_carrera);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['prom_hist_egre']) ? $resultado['prom_hist_egre'] : FALSE;
	}  

	function get_subcampos_aplicacion($id_campo = NULL)
	{
		$where = ($id_campo) ? " WHERE id_campo_aplicacion = ".quote($id_campo) : "";
		$sql = "SELECT * FROM sap_subcampos_aplicacion";

		return toba::db()->consultar($sql.$where);
	}

	static function get_tipos_beca_comunicaciones($solo_activas = FALSE){
		$sql = "SELECT id, descripcion
				FROM sap_tipo_beca
				ORDER BY descripcion";
			if($solo_activas){
				$sql = sql_concatenar_where($sql,array("activo = true"));
			}
			return consultar_fuente($sql);
	}

	static function get_tipos_beca_activas(){
		return SELF::get_tipos_beca_comunicaciones(TRUE);
	}

	

	function get_tipos_beca_por_convocatoria($id_convocatoria)
	{
		$sql = "SELECT tb.id_tipo_beca, tb.tipo_beca
				FROM be_convocatoria_beca as cb
				LEFT JOIN be_tipos_convocatoria as tc on tc.id_tipo_convocatoria = cb.id_tipo_convocatoria
				LEFT JOIN be_tipos_beca as tb on tb.id_tipo_convocatoria = cb.id_tipo_convocatoria
				WHERE cb.id_convocatoria = ".quote($id_convocatoria)."
				AND estado = 'A'";
		return toba::db()->consultar($sql);
	}

	function get_tipos_beca_por_tipo_convocatoria($id_tipo_convocatoria)
	{
		$sql = "SELECT tb.id_tipo_beca, tb.tipo_beca
				FROM be_convocatoria_beca as cb
				LEFT JOIN be_tipos_convocatoria as tc on tc.id_tipo_convocatoria = cb.id_tipo_convocatoria
				LEFT JOIN be_tipos_beca as tb on tb.id_tipo_convocatoria = cb.id_tipo_convocatoria
				WHERE cb.id_tipo_convocatoria = ".quote($id_tipo_convocatoria)."
				AND tb.estado = 'A'";
		return toba::db()->consultar($sql);
	}

	function get_tipos_documento()
	{
		$sql = "SELECT td.id_tipo_doc, td.tipo_doc FROM be_tipo_documento as td ORDER BY tipo_doc";
		return toba::db()->consultar($sql);
	}

	function get_universidades($filtro = array())
	{
		$where = array();
		$sql = "SELECT * FROM be_universidades ORDER BY universidad";

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);

	}

	function get_univ_dependencias($id_universidad = NULL)
	{
		$sql = "SELECT dep.id,
					(coalesce(dep.sigla_mapuche,'')) as sigla_mapuche,
					(coalesce(uni.sigla,'Otra'))||' - '||dep.nombre as nombre
				FROM sap_dependencia AS dep
				LEFT JOIN be_universidades AS uni ON uni.id_universidad = dep.id_universidad";
		if($id_universidad){
			$sql .= " WHERE dep.id_universidad = $id_universidad";
		}
		$sql .=	" ORDER BY nombre";
		return toba::db()->consultar($sql);

	}

	function tipo_beca_requiere_posgrado($id_tipo_beca)
	{
		//si no se recibi ningun tipo de beca, se asume la respuesta "SI"
		if(!$id_tipo_beca){
			return TRUE;
		}
		$resultado = toba::db()->consultar_fila("SELECT requiere_insc_posgrado FROM be_tipos_beca WHERE id_tipo_beca = ".quote($id_tipo_beca));
		if(count($resultado) > 0){
			return ($resultado['requiere_insc_posgrado'] == 'S') ? TRUE : FALSE;
		}else{
			return TRUE;
		}
	}

	function tipo_beca_suma_puntaje_academico($id_tipo_beca)
	{
		$sql = "SELECT suma_puntaje_academico FROM be_tipos_beca WHERE id_tipo_beca = ".quote($id_tipo_beca);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['suma_puntaje_academico'] == 'S');
	}



	

	

}
?>