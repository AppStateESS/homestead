alter table hms_bed add column persistent_id character varying;
alter table hms_checkin add column bed_persistent_id character varying;