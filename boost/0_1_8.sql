ALTER TABLE hms_residence_hall ADD COLUMN numbering_scheme smallint;
ALTER TABLE hms_residence_hall ALTER COLUMN numbering_scheme SET NOT NULL;
