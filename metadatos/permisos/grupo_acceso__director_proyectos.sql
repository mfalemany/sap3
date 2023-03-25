
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	'Director de Proyectos', --nombre
	NULL, --nivel_acceso
	'Director de Proyectos', --descripcion
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
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3522'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3538'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3652'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3654'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3670'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3671'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3680'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3690'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3691'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3715'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'sap', --proyecto
	'director_proyectos', --usuario_grupo_acc
	NULL, --item_id
	'3718'  --item
);
--- FIN Grupo de desarrollo 0
