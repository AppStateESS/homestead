delete from mod_settings where module = 'hms' and setting_name = 'assign_queue_enabled';
delete from mod_settings where module = 'hms' and setting_name = 'remove_queue_enabled';

alter table hms_term add column docusign_template_id character varying;

create table hms_contract (
	id 			integer not null,
	banner_id 	integer not null,
	term 		integer not null REFERENCES hms_term(term),
	envelope_id integer,
	PRIMARY KEY(id)
);