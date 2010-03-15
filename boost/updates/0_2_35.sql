alter table hms_term add column new_applications smallint;
alter table hms_term alter new_applications set default 0;
update hms_term set new_applications=0;
alter table hms_term alter new_applications set not null;
