alter table hms_term add column new_applications smallint;
alter table hms_term alter new_applications set default 0;
