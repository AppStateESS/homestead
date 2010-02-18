alter table hms_student_profiles add column term integer not null references hms_term(term);
