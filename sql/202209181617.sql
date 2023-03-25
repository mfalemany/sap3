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

INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 a�os de antig�edad m�nima', 'Fotocopia/Imagen de la resoluci�n de designaci�n',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener T�tulo de posgrado de Doctor', 'Fotocopia/Imagen del t�tulo (ambas caras)',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido un m�nimo de dos tesis Doctorales y/� de Maestr�a terminadas, al menos una en calidad de director, pudiendo haber actuado en otra/s en calidad de codirector', 'Fotocopia/Imagen de las actas de las tesis',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director de al menos un proyecto de investigaci�n vigente, y haber dirigido al menos 2 proyectos anteriores. En todos los casos los PI deben ser acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resoluci�n de aprobaci�n de los proyectos',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor de un m�nimo de 3 publicaciones en revistas indexadas de impacto en los �ltimos 5 a�os.', 'Fotocopia/Imagen de la primera p�gina del articulo o link del art�culo.',1,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 a�os de antig�edad m�nima.', 'Fotocopia/Imagen de la resoluci�n de designaci�n',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener T�tulo de posgrado de Magister o Doctor', 'Fotocopia/Imagen del t�tulo (ambas caras)',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido o codirigido una tesis de Maestr�a o Doctorado terminada y aprobada o en su defecto 8 a�os de actividad de formaci�n de RRHH', 'Fotocopia/Imagen del acta de la tesis o lista cronol�gica de publicaciones conjuntas.',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director o codirector de al menos un proyecto de investigaci�n vigente, y haber dirigido al menos 1 proyectos anteriores. En todos los casos los PI deben ser acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resoluci�n de aprobaci�n de los proyectos',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor o coautor de un m�nimo de 3 publicaciones en revistas indexadas de impacto en los �ltimos 5 a�os', 'Fotocopia/Imagen de la primera p�gina del articulo',2,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 a�os de antig�edad m�nima', 'Fotocopia/Imagen de la resoluci�n de designaci�n',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Tener T�tulo de posgrado de Magister o Doctor', 'Fotocopia/Imagen del t�tulo (ambas caras)',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Haber dirigido o codirigido una tesis de Maestr�a o Doctorado terminada y aprobada o en su defecto 5 a�os de actividad de formaci�n de RRHH', 'Fotocopia/Imagen del acta de la tesis o lista cronol�gica de publicaciones conjuntas',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser director o codirector de al menos un proyecto de investigaci�n vigente o demostrar actividad continua en investigaci�n en m�s de 5 a�os en PI acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc).', 'Fotocopia/Imagen de Resoluci�n de aprobaci�n de los proyectos',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Ser autor o coautor de un m�nimo de 3 publicaciones en revistas indexadas de impacto en los �ltimos 5 a�os', 'Fotocopia/Imagen de la primera p�gina del articulo',3,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Revistar como docente en la UNNE en un cargo regular u ordinario. Si es interino debe acreditar 3 a�os de antig�edad m�nima.', 'Fotocopia/Imagen de la resoluci�n de designaci�n',4,1);
INSERT INTO sap_cat_inc_trans_documentacion VALUES (DEFAULT, 'Demostrar actividad continua en investigaci�n en m�s de 3 a�os en PI acreditados por entidad de CyT competente (SGCyT UNNE, Agencia, etc) o tener T�tulo de posgrado de Magister o Doctor', 'Fotocopia/Imagen de Resoluci�n de aprobaci�n de los proyectos o Fotocopia/Imagen del t�tulo (ambas caras) seg�n corresponda',4,1);



