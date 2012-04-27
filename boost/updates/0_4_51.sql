alter table hms_lottery_application add column invited_on integer;

alter table hms_assignment add column application_term integer;
alter table hms_assignment add column class character(2);

alter table hms_assignment_history add column application_term integer;
alter table hms_assignment_history add column class character(2);
