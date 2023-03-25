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


INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Cargo ayudante alumno/a o Adscripción por Concurso Concluída','5 puntos', 5);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripción concluída','3 puntos', 3);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripción por concurso en curso','2 puntos (con seis o más meses de duración a la fecha de esta convocatoria', 2);
INSERT INTO be_subcriterio_evaluacion VALUES (default, 43, 'Adscripción en curso','1 punto (con seis o más meses de duración a la fecha de esta convocatoria', 5);

