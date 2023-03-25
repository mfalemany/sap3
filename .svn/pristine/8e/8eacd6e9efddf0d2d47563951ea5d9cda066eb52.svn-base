CREATE TABLE IF NOT EXISTS public.sap_cat_inc_trans_documentacion
(
    id serial NOT NULL,
    requisito character varying NOT NULL,
    documental character varying NOT NULL,
    aplica_a_categoria numeric(1) NOT NULL,
    activo numeric(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

CREATE TABLE public.sap_cat_inc_transitorio
(
    nro_documento character varying(15) NOT NULL,
    id_convocatoria smallint NOT NULL,
    categoria numeric(1) NOT NULL,
    fecha_solicitud date NOT NULL DEFAULT current_date,
    CONSTRAINT pk_sap_cat_inc_transitorio PRIMARY KEY (nro_documento, id_convocatoria),
    CONSTRAINT "fk_sap_cat_inc_transitorio-nro_documento" FOREIGN KEY (nro_documento)
        REFERENCES public.sap_personas (nro_documento) MATCH SIMPLE
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT "fk_sap_cat_inc_transitorio-id_convocatoria" FOREIGN KEY (id_convocatoria)
        REFERENCES public.be_convocatoria_beca (id_convocatoria) MATCH SIMPLE
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 años de antigüedad mínima', 'Fotocopia/Imagen de la resolución de designación',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener Título de posgrado de Doctor', 'Fotocopia/Imagen del título (ambas caras)',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido un mínimo de dos tesis Doctorales y/ó de Maestría terminadas, al menos una en calidad de director, pudiendo haber actuado en otra/s en calidad de codirector', 'Fotocopia/Imagen de las actas de las tesis',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director de al menos un proyecto de investigación vigente, y haber dirigido al menos 2 proyectos anteriores. En todos los casos los PI deben ser acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resolución de aprobación de los proyectos',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor de un mínimo de 3 publicaciones en revistas indexadas de impacto en los últimos 5 años.', 'Fotocopia/Imagen de la primera página del articulo o link del artículo.',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 años de antigüedad mínima.', 'Fotocopia/Imagen de la resolución de designación',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener Título de posgrado de Magister o Doctor', 'Fotocopia/Imagen del título (ambas caras)',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido o codirigido una tesis de Maestría o Doctorado terminada y aprobada o en su defecto 8 años de actividad de formación de RRHH', 'Fotocopia/Imagen del acta de la tesis o lista cronológica de publicaciones conjuntas.',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director o codirector de al menos un proyecto de investigación vigente, y haber dirigido al menos 1 proyectos anteriores. En todos los casos los PI deben ser acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resolución de aprobación de los proyectos',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor o coautor de un mínimo de 3 publicaciones en revistas indexadas de impacto en los últimos 5 años', 'Fotocopia/Imagen de la primera página del articulo',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 años de antigüedad mínima', 'Fotocopia/Imagen de la resolución de designación',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener Título de posgrado de Magister o Doctor', 'Fotocopia/Imagen del título (ambas caras)',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido o codirigido una tesis de Maestría o Doctorado terminada y aprobada o en su defecto 5 años de actividad de formación de RRHH', 'Fotocopia/Imagen del acta de la tesis o lista cronológica de publicaciones conjuntas',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director o codirector de al menos un proyecto de investigación vigente o demostrar actividad continua en investigación en más de 5 años en PI acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resolución de aprobación de los proyectos',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor o coautor de un mínimo de 3 publicaciones en revistas indexadas de impacto en los últimos 5 años', 'Fotocopia/Imagen de la primera página del articulo',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 años de antigüedad mínima.', 'Fotocopia/Imagen de la resolución de designación',4,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Demostrar actividad continua en investigación en más de 3 años en PI acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc) o tener Título de posgrado de Magister o Doctor', 'Fotocopia/Imagen de Resolución de aprobación de los proyectos o Fotocopia/Imagen del título (ambas caras) según corresponda',4,1);



