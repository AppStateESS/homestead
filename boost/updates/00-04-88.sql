delete from mod_settings where module = 'hms' and setting_name = 'assign_queue_enabled';
delete from mod_settings where module = 'hms' and setting_name = 'remove_queue_enabled';

alter table hms_term add column docusign_template_id character varying;

