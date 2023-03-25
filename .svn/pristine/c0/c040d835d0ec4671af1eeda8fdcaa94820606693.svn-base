ALTER TABLE IF EXISTS public.be_cumplimiento_obligacion
    RENAME fecha_cumplimiento TO fecha_carga;

ALTER TABLE IF EXISTS public.be_cumplimiento_obligacion
    ADD COLUMN IF NOT EXISTS cumplido boolean NOT NULL DEFAULT true;