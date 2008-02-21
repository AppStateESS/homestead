ALTER TABLE hms_application ADD COLUMN physical_disability smallint;
ALTER TABLE hms_application ADD COLUMN psych_disability smallint;
ALTER TABLE hms_application ADD COLUMN medical_need smallint;
ALTER TABLE hms_application ADD COLUMN gender_need smallint;


ALTER TABLE hms_learning_community_applications ALTER COLUMN rlc_second_choice_id DROP NOT NULL;
ALTER TABLE hms_learning_community_applications ALTER COLUMN rlc_third_choice_id DROP NOT NULL;

ALTER TABLE hms_deadlines DROP COLUMN student_login_begin_timestamp;
ALTER TABLE hms_deadlines DROP COLUMN student_login_end_timestamp;
