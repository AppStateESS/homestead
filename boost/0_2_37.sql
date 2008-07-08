ALTER TABLE hms_application ADD COLUMN withdrawn smallint;
ALTER TABLE hms_application ALTER COLUMN withdrawn SET DEFAULT 0;
UPDATE hms_application SET withdrawn = 0;
ALTER TABLE hms_application ALTER COLUMN withdrawn SET NOT NULL;
