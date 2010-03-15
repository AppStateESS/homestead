ALTER TABLE hms_bed ADD COLUMN ra_bed smallint;
ALTER TABLE hms_bed ALTER COLUMN ra_bed SET DEFAULT 0;
UPDATE hms_bed SET ra_bed = 0;
ALTER TABLE hms_bed ALTER COLUMN ra_bed SET NOT NULL;
