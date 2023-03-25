------------------------------------------------------------
--[4709]--  Informes - ci_edicion_informes_pdts - ci_trab_transferido - datos_transf 
------------------------------------------------------------

------------------------------------------------------------
-- apex_objeto
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto (proyecto, objeto, anterior, identificador, reflexivo, clase_proyecto, clase, punto_montaje, subclase, subclase_archivo, objeto_categoria_proyecto, objeto_categoria, nombre, titulo, colapsable, descripcion, fuente_datos_proyecto, fuente_datos, solicitud_registrar, solicitud_obj_obs_tipo, solicitud_obj_observacion, parametro_a, parametro_b, parametro_c, parametro_d, parametro_e, parametro_f, usuario, creacion, posicion_botonera) VALUES (
	'sap', --proyecto
	'4709', --objeto
	NULL, --anterior
	NULL, --identificador
	NULL, --reflexivo
	'toba', --clase_proyecto
	'toba_datos_relacion', --clase
	'20', --punto_montaje
	NULL, --subclase
	NULL, --subclase_archivo
	NULL, --objeto_categoria_proyecto
	NULL, --objeto_categoria
	'Informes - ci_edicion_informes_pdts - ci_trab_transferido - datos_transf', --nombre
	NULL, --titulo
	NULL, --colapsable
	NULL, --descripcion
	'sap', --fuente_datos_proyecto
	'sap', --fuente_datos
	NULL, --solicitud_registrar
	NULL, --solicitud_obj_obs_tipo
	NULL, --solicitud_obj_observacion
	NULL, --parametro_a
	NULL, --parametro_b
	NULL, --parametro_c
	NULL, --parametro_d
	NULL, --parametro_e
	NULL, --parametro_f
	NULL, --usuario
	'2021-06-26 17:52:40', --creacion
	NULL  --posicion_botonera
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_datos_rel
------------------------------------------------------------
INSERT INTO apex_objeto_datos_rel (proyecto, objeto, debug, clave, ap, punto_montaje, ap_clase, ap_archivo, sinc_susp_constraints, sinc_orden_automatico, sinc_lock_optimista) VALUES (
	'sap', --proyecto
	'4709', --objeto
	'0', --debug
	NULL, --clave
	'2', --ap
	'20', --punto_montaje
	NULL, --ap_clase
	NULL, --ap_archivo
	'0', --sinc_susp_constraints
	'1', --sinc_orden_automatico
	'1'  --sinc_lock_optimista
);

------------------------------------------------------------
-- apex_objeto_dependencias
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3516', --dep_id
	'4709', --objeto_consumidor
	'4660', --objeto_proveedor
	'pdts_inf_trab_transf', --identificador
	NULL, --parametros_a
	'1', --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'2'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3515', --dep_id
	'4709', --objeto_consumidor
	'4648', --objeto_proveedor
	'trab_transferido', --identificador
	NULL, --parametros_a
	'1', --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'1'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3517', --dep_id
	'4709', --objeto_consumidor
	'4667', --objeto_proveedor
	'trab_transferido_autor', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'3'  --orden
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_datos_rel_asoc
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4709', --objeto
	'281', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4648', --padre_objeto
	'trab_transferido', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4660', --hijo_objeto
	'pdts_inf_trab_transf', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'1'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4709', --objeto
	'282', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4648', --padre_objeto
	'trab_transferido', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4667', --hijo_objeto
	'trab_transferido_autor', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'2'  --orden
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_rel_columnas_asoc
------------------------------------------------------------
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4709', --objeto
	'281', --asoc_id
	'4648', --padre_objeto
	'2909', --padre_clave
	'4660', --hijo_objeto
	'2849'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4709', --objeto
	'282', --asoc_id
	'4648', --padre_objeto
	'2909', --padre_clave
	'4667', --hijo_objeto
	'2864'  --hijo_clave
);
