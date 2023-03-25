ALTER TABLE IF EXISTS public.be_tipo_beca_criterio_eval
    ADD CONSTRAINT uq_id_criterio_evaluacion UNIQUE (id_criterio_evaluacion);


CREATE TABLE public.be_subcriterio_evaluacion
(
    id serial,
    id_criterio_evaluacion smallint NOT NULL,
    descripcion character varying NOT NULL,
    referencia character varying,
    maximo numeric(3),
    CONSTRAINT pk_subcriterio_evaluacion PRIMARY KEY (id),
    CONSTRAINT "fk_subcriterio_evaluacion-id_criterio" FOREIGN KEY (id_criterio_evaluacion)
        REFERENCES public.be_tipo_beca_criterio_eval (id_criterio_evaluacion) MATCH SIMPLE
        ON UPDATE CASCADE
        ON DELETE RESTRICT
        NOT VALID
)


INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Cargo ayudante alumno/a o Adscripci�n por Concurso Conclu�da','5 puntos', 5);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripci�n conclu�da','3 puntos', 3);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripci�n por concurso en curso','2 puntos (con seis o m�s meses de duraci�n a la fecha de esta convocatoria', 2);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripci�n en curso','1 punto (con seis o m�s meses de duraci�n a la fecha de esta convocatoria', 5);

