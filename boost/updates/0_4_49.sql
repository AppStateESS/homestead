alter table hms_room drop column is_medical;
alter table hms_room RENAME COLUMN is_reserved TO reserved;

alter table hms_room add column offline smallint not null default 0;
update hms_room set offline = 1 where is_online = 0;
alter table hms_room drop column is_online;

alter table hms_room RENAME COLUMN ra_room TO ra;
alter table hms_room RENAME COLUMN private_room TO private;
alter table hms_room RENAME COLUMN is_overflow TO overflow;

alter table hms_room add column ada smallint not null default 0;
alter table hms_room add column hearing_impaired smallint not null default 0;
alter table hms_room add column bath_en_suite smallint not null default 0;
alter table hms_room add column parlor smallint not null default 0;