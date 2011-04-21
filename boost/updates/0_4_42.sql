BEGIN;

ALTER TABLE hms_assignment ADD COLUMN banner_id integer;
alter table hms_assignment ADD CONSTRAINT hms_assignment_uniq_banner_id_const UNIQUE (banner_id, term);
COMMIT;