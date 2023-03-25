
------------------------------------------------------------
-- apex_servicio_web
------------------------------------------------------------
INSERT INTO apex_servicio_web (proyecto, servicio_web, descripcion, tipo, param_to, param_wsa) VALUES (
	'sap', --proyecto
	'rest_arai_reportes', --servicio_web
	'Servicio Web para acceder a Arai-Reportes', --descripcion
	'rest', --tipo
	NULL, --param_to
	'0'  --param_wsa
);
INSERT INTO apex_servicio_web (proyecto, servicio_web, descripcion, tipo, param_to, param_wsa) VALUES (
	'sap', --proyecto
	'wa_unne', --servicio_web
	'Servicio web que ofrece datos de docentes, no-docentes y alumnos.', --descripcion
	'rest', --tipo
	NULL, --param_to
	'0'  --param_wsa
);
