delete from hms_student_cache;

alter table hms_student_cache add column preferred_name character varying;
alter table hms_student_cache add column confidential character(1) NOT NULL;
alter table hms_student_cache alter column admissions_decision_code type character varying;
alter table hms_student_cache add column admissions_decision_desc character varying;
alter table hms_student_cache add column greek character varying;