alter table hms_new_application add column application_type character varying(255);

update hms_new_application set application_type = 'summer' where id IN (select id from hms_summer_application);
update hms_new_application set application_type = 'fall' where id IN (select id from hms_fall_application);
update hms_new_application set application_type = 'spring' where id IN (select id from hms_spring_application);
update hms_new_application set application_type = 'lottery' where id IN (select id from hms_lottery_application);

create table hms_waitlist_application (id integer NOT NULL references hms_new_application (id), waiting_list_hide integer NOT NULL default 0, PRIMARY KEY(id));