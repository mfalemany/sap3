
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	'Integrante de Junta Coordinadora', --nombre
	NULL, --nivel_acceso
	'Miembro de la Junta Coordinadora de Becas', --descripcion
	NULL, --vencimiento
	NULL, --dias
	NULL, --hora_entrada
	NULL, --hora_salida
	NULL, --listar
	'1', --permite_edicion
	NULL  --menu_usuario
);

------------------------------------------------------------
-- apex_usuario_grupo_acc_item
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3538'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3696'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3741'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3742'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3743'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3751'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'integrante_junta_coordinadora', --usuario_grupo_acc
	NULL, --item_id
	'3755'  --item
);
--- FIN Grupo de desarrollo 0
