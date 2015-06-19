alter table hms_new_application add column banner_id_int integer;

update hms_new_application set banner_id_int=subquery.banner_id from (select id, CAST(banner_id AS numeric) from hms_new_application) as subquery where hms_new_application.id = subquery.id;

alter table hms_new_application drop column banner_id;

alter table hms_new_application rename column banner_id_int to banner_id;

alter table hms_new_application alter column banner_id set not null;
