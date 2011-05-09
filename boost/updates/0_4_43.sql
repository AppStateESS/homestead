drop table hms_pricing_tiers;
drop sequence hms_pricing_tiers_seq ;
alter table hms_room drop column pricing_tier;

alter table hms_student_address_cache alter column state drop not null;