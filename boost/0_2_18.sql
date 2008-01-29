ALTER TABLE hms_deadlines ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_deadlines SET term = 200810;
ALTER TABLE hms_deadlines ALTER COLUMN term SET NOT NULL;

DELETE FROM hms_deadlines;
ALTER TABLE hms_deadlines ADD COLUMN id integer;
ALTER TABLE hms_deadlines ALTER COLUMN id SET NOT NULL;
ALTER TABLE hms_deadlines ADD CONSTRAINT id_pkey PRIMARY KEY(id);
