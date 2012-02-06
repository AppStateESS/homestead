alter table hms_bed add column international_reserved smallint not null default 0;
alter table hms_bed rename column ra_bed to ra_roommate;