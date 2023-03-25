ALTER TABLE IF EXISTS public.sap_grupo_informe_evaluacion DROP CONSTRAINT IF EXISTS pk_grupo_informe_evaluacion;

ALTER TABLE IF EXISTS public.sap_grupo_informe_evaluacion
ADD CONSTRAINT pk_grupo_informe_evaluacion PRIMARY KEY (id_grupo, id_convocatoria, nro_documento_evaluador);

ALTER TABLE IF EXISTS public.sap_grupo_informe_evaluacion
ADD COLUMN IF NOT EXISTS categoria_concedida_dir numeric(1);

ALTER TABLE IF EXISTS public.sap_grupo
ADD COLUMN IF NOT EXISTS permite_cambio_categoria boolean NOT NULL DEFAULT false;

ALTER TABLE IF EXISTS public.sap_grupo_informe_evaluacion
    ADD COLUMN IF NOT EXISTS estado character(1) NOT NULL DEFAULT 'A';

ALTER TABLE IF EXISTS public.sap_grupo_informe_evaluacion
    ADD COLUMN IF NOT EXISTS last_updated_at timestamp without time zone NOT NULL DEFAULT current_timestamp;