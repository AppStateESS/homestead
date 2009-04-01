BEGIN;

ALTER TABLE hms_new_application ADD COLUMN meal_plan character varying(3) NOT NULL;

ALTER TABLE hms_new_application ADD COLUMN physical_disability smallint;
ALTER TABLE hms_new_application ADD COLUMN psych_disability smallint;
ALTER TABLE hms_new_application ADD COLUMN medical_need smallint;
ALTER TABLE hms_new_application ADD COLUMN gender_need smallint;

ALTER TABLE hms_new_application ADD COLUMN withdrawn smallint NOT NULL default 0;

ALTER TABLE hms_new_application ADD COLUMN created_on integer NOT NULL;
ALTER TABLE hms_new_application ADD COLUMN created_by character varying(32) NOT NULL;

ALTER TABLE hms_new_application ADD COLUMN modified_on integer NOT NULL;
ALTER TABLE hms_new_application ADD COLUMN modified_by character varying(32) NOT NULL;

COMMIT;
