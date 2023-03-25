-- Table: public.sap_grupo_estado

-- DROP TABLE IF EXISTS public.sap_grupo_estado;

CREATE TABLE public.sap_grupo_estado
(
    id_estado serial,
    estado character varying NOT NULL,
    CONSTRAINT pk_sap_grupo_estado PRIMARY KEY (id_estado),
    CONSTRAINT "uq_sap_grupo_estado-estado" UNIQUE (estado)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE IF EXISTS public.sap_grupo_estado
    OWNER to sap;

INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Inscripto');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Guardado');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Finalizado');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'A cambiar');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'En proceso de evaluación');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Modificado');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Activo');
INSERT INTO sap_grupo_estado VALUES (DEFAULT, 'Desaprobado');


ALTER TABLE sap_grupo ADD COLUMN id_estado smallint NOT NULL DEFAULT 1;

ALTER TABLE sap_grupo ADD CONSTRAINT "fk_grupo-id_estado" FOREIGN KEY (id_estado)
REFERENCES public.sap_grupo_estado (id_estado) MATCH SIMPLE
ON UPDATE CASCADE
ON DELETE CASCADE;

UPDATE sap_grupo 
SET id_estado = (SELECT id_estado FROM sap_grupo_estado WHERE estado = 'En proceso de evaluación')
WHERE id_grupo IN (
    SELECT id_grupo FROM (
        SELECT max(id_convocatoria), resultado, id_grupo
        FROM sap_grupo_informe_evaluacion sgie
        WHERE resultado = 'P'
        GROUP BY 2,3
    ) AS tmp
);

UPDATE sap_grupo 
SET id_estado = (SELECT id_estado FROM sap_grupo_estado WHERE estado = 'Activo')
WHERE id_grupo IN (
    SELECT id_grupo FROM (
        SELECT max(id_convocatoria), resultado, id_grupo
        FROM sap_grupo_informe_evaluacion sgie
        WHERE resultado = 'A'
        GROUP BY 2,3
    ) AS tmp
);

UPDATE sap_grupo 
SET id_estado = (SELECT id_estado FROM sap_grupo_estado WHERE estado = 'Desaprobado')
WHERE id_grupo IN (
    SELECT id_grupo FROM (
        SELECT max(id_convocatoria), resultado, id_grupo
        FROM sap_grupo_informe_evaluacion sgie
        WHERE resultado = 'D'
        GROUP BY 2,3
    ) AS tmp
);

UPDATE sap_grupo 
SET id_estado = (SELECT id_estado FROM sap_grupo_estado WHERE estado = 'Inscripto')
WHERE fecha_inscripcion IS null;

