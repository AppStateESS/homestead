BEGIN;

alter table hms_new_application drop constraint new_application_key;
alter table hms_new_application drop constraint new_application_key2

alter table hms_new_application add constraint unique_application_username_const UNIQUE (username, term, application_type);
alter table hms_new_application add constraint unique_application_bannerid_const UNIQUE (banner_id, term, application_type);

COMMIT;