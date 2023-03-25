CREATE TABLE public.sap_grupo_evaluador
(
    nro_documento character varying(15) NOT NULL,
    id_grupo smallint NOT NULL,
    CONSTRAINT pk_grupo_evaluador PRIMARY KEY (nro_documento, id_grupo),
    CONSTRAINT "fk_grupo_evaluador-nro_documento" FOREIGN KEY (nro_documento)
        REFERENCES public.sap_personas (nro_documento) MATCH SIMPLE
        ON UPDATE CASCADE
        ON DELETE RESTRICT
        NOT VALID,
    CONSTRAINT "fk_grupo_evaluador-id_grupo" FOREIGN KEY (id_grupo)
        REFERENCES public.sap_grupo (id_grupo) MATCH SIMPLE
        ON UPDATE CASCADE
        ON DELETE RESTRICT
        NOT VALID
)