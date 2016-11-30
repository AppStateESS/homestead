alter table hms_contract add column envelope_status character varying not null default 'sent';
alter table hms_contract add column envelope_status_time integer;
alter table hms_contract alter column envelope_status_time set not null;
