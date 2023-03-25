------------------------------------------------------------
--[4759]--  Inscripción - alumno 
------------------------------------------------------------

------------------------------------------------------------
-- apex_objeto
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto (proyecto, objeto, anterior, identificador, reflexivo, clase_proyecto, clase, punto_montaje, subclase, subclase_archivo, objeto_categoria_proyecto, objeto_categoria, nombre, titulo, colapsable, descripcion, fuente_datos_proyecto, fuente_datos, solicitud_registrar, solicitud_obj_obs_tipo, solicitud_obj_observacion, parametro_a, parametro_b, parametro_c, parametro_d, parametro_e, parametro_f, usuario, creacion, posicion_botonera) VALUES (
	'sap', --proyecto
	'4759', --objeto
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
	'Inscripción - alumno', --nombre
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
	'2021-10-14 13:56:46', --creacion
	NULL  --posicion_botonera
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_datos_rel
------------------------------------------------------------
INSERT INTO apex_objeto_datos_rel (proyecto, objeto, debug, clave, ap, punto_montaje, ap_clase, ap_archivo, sinc_susp_constraints, sinc_orden_automatico, sinc_lock_optimista) VALUES (
	'sap', --proyecto
	'4759', --objeto
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
	'3564', --dep_id
	'4759', --objeto_consumidor
	'4750', --objeto_proveedor
	'antec_activ_docentes', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'2'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3565', --dep_id
	'4759', --objeto_consumidor
	'4751', --objeto_proveedor
	'antec_becas_obtenidas', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'3'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3566', --dep_id
	'4759', --objeto_consumidor
	'4752', --objeto_proveedor
	'antec_conoc_idiomas', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'4'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3567', --dep_id
	'4759', --objeto_consumidor
	'4753', --objeto_proveedor
	'antec_cursos_perfec_aprob', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'5'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3568', --dep_id
	'4759', --objeto_consumidor
	'4754', --objeto_proveedor
	'antec_estudios_afines', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'6'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3569', --dep_id
	'4759', --objeto_consumidor
	'4755', --objeto_proveedor
	'antec_otras_actividades', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'7'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3570', --dep_id
	'4759', --objeto_consumidor
	'4756', --objeto_proveedor
	'antec_particip_dict_cursos', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'8'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3571', --dep_id
	'4759', --objeto_consumidor
	'4757', --objeto_proveedor
	'antec_present_reuniones', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'9'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3572', --dep_id
	'4759', --objeto_consumidor
	'4758', --objeto_proveedor
	'antec_trabajos_publicados', --identificador
	NULL, --parametros_a
	NULL, --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'10'  --orden
);
INSERT INTO apex_objeto_dependencias (proyecto, dep_id, objeto_consumidor, objeto_proveedor, identificador, parametros_a, parametros_b, parametros_c, inicializar, orden) VALUES (
	'sap', --proyecto
	'3563', --dep_id
	'4759', --objeto_consumidor
	'4210', --objeto_proveedor
	'persona', --identificador
	NULL, --parametros_a
	'1', --parametros_b
	NULL, --parametros_c
	NULL, --inicializar
	'1'  --orden
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_datos_rel_asoc
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'287', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4750', --hijo_objeto
	'antec_activ_docentes', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'1'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'288', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4751', --hijo_objeto
	'antec_becas_obtenidas', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'2'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'289', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4752', --hijo_objeto
	'antec_conoc_idiomas', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'3'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'290', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4753', --hijo_objeto
	'antec_cursos_perfec_aprob', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'4'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'291', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4754', --hijo_objeto
	'antec_estudios_afines', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'5'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'292', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4755', --hijo_objeto
	'antec_otras_actividades', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'6'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'293', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4756', --hijo_objeto
	'antec_particip_dict_cursos', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'7'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'294', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4757', --hijo_objeto
	'antec_present_reuniones', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'8'  --orden
);
INSERT INTO apex_objeto_datos_rel_asoc (proyecto, objeto, asoc_id, identificador, padre_proyecto, padre_objeto, padre_id, padre_clave, hijo_proyecto, hijo_objeto, hijo_id, hijo_clave, cascada, orden) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'295', --asoc_id
	NULL, --identificador
	'sap', --padre_proyecto
	'4210', --padre_objeto
	'persona', --padre_id
	NULL, --padre_clave
	'sap', --hijo_proyecto
	'4758', --hijo_objeto
	'antec_trabajos_publicados', --hijo_id
	NULL, --hijo_clave
	NULL, --cascada
	'9'  --orden
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_rel_columnas_asoc
------------------------------------------------------------
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'287', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4750', --hijo_objeto
	'2975'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'288', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4751', --hijo_objeto
	'2980'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'289', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4752', --hijo_objeto
	'2990'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'290', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4753', --hijo_objeto
	'2997'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'291', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4754', --hijo_objeto
	'3002'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'292', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4755', --hijo_objeto
	'3010'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'293', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4756', --hijo_objeto
	'3016'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'294', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4757', --hijo_objeto
	'3022'  --hijo_clave
);
INSERT INTO apex_objeto_rel_columnas_asoc (proyecto, objeto, asoc_id, padre_objeto, padre_clave, hijo_objeto, hijo_clave) VALUES (
	'sap', --proyecto
	'4759', --objeto
	'295', --asoc_id
	'4210', --padre_objeto
	'1771', --padre_clave
	'4758', --hijo_objeto
	'3029'  --hijo_clave
);
