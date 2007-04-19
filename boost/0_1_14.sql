ALTER TABLE hms_student_profiles DROP COLUMN study_time;

ALTER TABLE hms_student_profiles ADD COLUMN study_early_morning smallint default 0;
ALTER TABLE hms_student_profiles ADD COLUMN study_morning_afternoon smallint default 0;
ALTER TABLE hms_student_profiles ADD COLUMN study_afternoon_evening smallint default 0;
ALTER TABLE hms_student_profiles ADD COLUMN study_evening smallint default 0;
ALTER TABLE hms_student_profiles ADD COLUMN study_late_night smallint default 0;
