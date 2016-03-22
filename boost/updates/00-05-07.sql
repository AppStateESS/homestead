ALTER TABLE hms_student_profiles ADD COLUMN honors smallint default 0 not null;
UPDATE hms_student_profiles set honors = 0;
