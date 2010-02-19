delete from hms_student_profiles;
alter table hms_student_profiles add column term integer references hms_term(term);
update hms_student_profiles set term = 200940;
alter table hms_student_profiles alter column term SET NOT NULL;
alter table hms_student_profiles RENAME user_id to username;
