UPDATE hms_bedrooms SET deleted = '0' WHERE deleted IS NULL;
UPDATE hms_beds SET deleted = '0' WHERE deleted IS NULL;
ALTER TABLE hms_bedrooms ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE hms_beds ALTER COLUMN deleted SET DEFAULT 0;

ALTER TABLE hms_application ADD COLUMN temp_col character varying (32);
UPDATE hms_application SET temp_col = hms_student_id;
ALTER TABLE hms_application DROP COLUMN hms_student_id;
ALTER TABLE hms_application ADD COLUMN hms_student_id character varying(32);
UPDATE hms_application SET hms_student_id = temp_col;

UPDATE hms_application SET temp_col = created_by;
ALTER TABLE hms_application DROP COLUMN created_by;
ALTER TABLE hms_application ADD COLUMN created_by character varying(32);
UPDATE hms_application SET created_by = temp_col;
ALTER TABLE hms_application DROP COLUMN temp_col;

ALTER TABLE hms_learning_community_applications ADD COLUMN temp_col character varying(32);
UPDATE hms_learning_community_applications SET temp_col = user_id;
ALTER TABLE hms_learning_community_applications DROP COLUMN user_id;
ALTER TABLE hms_learning_community_applications ADD COLUMN user_id character varying(32);
UPDATE hms_learning_community_applications SET user_id = temp_col;
ALTER TABLE hms_learning_community_applications DROP COLUMN temp_col;

ALTER TABLE hms_learning_community_applications ADD COLUMN temp_col character varying(2048);
UPDATE hms_learning_community_applications SET temp_col = why_specific_communities;
ALTER TABLE hms_learning_community_applications DROP COLUMN why_specific_communities;
ALTER TABLE hms_learning_community_applications ADD COLUMN why_specific_communities character varying(2048);
UPDATE hms_learning_community_applications SET why_specific_communities = temp_col;

UPDATE hms_learning_community_applications SET temp_col = strengths_weaknesses;
ALTER TABLE hms_learning_community_applications DROP COLUMN strengths_weaknesses;
ALTER TABLE hms_learning_community_applications ADD COLUMN strengths_weaknesses character varying(2048);
UPDATE hms_learning_community_applications SET strengths_weaknesses = temp_col;

UPDATE hms_learning_community_applications SET temp_col = rlc_question_0;
ALTER TABLE hms_learning_community_applications DROP COLUMN rlc_question_0;
ALTER TABLE hms_learning_community_applications ADD COLUMN rlc_question_0 character varying(2048);
UPDATE hms_learning_community_applications SET rlc_question_0 = temp_col;

UPDATE hms_learning_community_applications SET temp_col = rlc_question_1;
ALTER TABLE hms_learning_community_applications DROP COLUMN rlc_question_1;
ALTER TABLE hms_learning_community_applications ADD COLUMN rlc_question_1 character varying(2048);
UPDATE hms_learning_community_applications SET rlc_question_1 = temp_col;

UPDATE hms_learning_community_applications SET temp_col = rlc_question_2;
ALTER TABLE hms_learning_community_applications DROP COLUMN rlc_question_2;
ALTER TABLE hms_learning_community_applications ADD COLUMN rlc_question_2 character varying(2048);
UPDATE hms_learning_community_applications SET rlc_question_2 = temp_col;

UPDATE hms_learning_community_applications SET temp_col = rlc_question_3;
ALTER TABLE hms_learning_community_applications DROP COLUMN rlc_question_3;
ALTER TABLE hms_learning_community_applications ADD COLUMN rlc_question_3 character varying(2048);
UPDATE hms_learning_community_applications SET rlc_question_3 = temp_col;
ALTER TABLE hms_learning_community_applications DROP COLUMN temp_col;

ALTER TABLE hms_roommate_hashes ADD COLUMN temp_col character varying(32);
UPDATE hms_roommate_hashes SET temp_col = roommate_zero;
ALTER TABLE hms_roommate_hashes DROP COLUMN roommate_zero;
ALTER TABLE hms_roommate_hashes ADD COLUMN roommate_zero character varying(32);
UPDATE hms_roommate_hashes SET roommate_zero = temp_col;

UPDATE hms_roommate_hashes SET temp_col = roommate_one;
ALTER TABLE hms_roommate_hashes DROP COLUMN roommate_one;
ALTER TABLE hms_roommate_hashes ADD COLUMN roommate_one character varying(32);
UPDATE hms_roommate_hashes SET roommate_one = temp_col;

UPDATE hms_roommate_hashes SET temp_col = roommate_two;
ALTER TABLE hms_roommate_hashes DROP COLUMN roommate_two;
ALTER TABLE hms_roommate_hashes ADD COLUMN roommate_two character varying(32);
UPDATE hms_roommate_hashes SET roommate_two = temp_col;

UPDATE hms_roommate_hashes SET temp_col = roommate_three;
ALTER TABLE hms_roommate_hashes DROP COLUMN roommate_three;
ALTER TABLE hms_roommate_hashes ADD COLUMN roommate_three character varying(32);
UPDATE hms_roommate_hashes SET roommate_three = temp_col;
ALTER TABLE hms_roommate_hashes DROP COLUMN temp_col;

ALTER TABLE hms_assignment ADD COLUMN temp_col character varying(32);
UPDATE hms_assignment SET temp_col = asu_username;
ALTER TABLE hms_assignment DROP COLUMN asu_username;
ALTER TABLE hms_assignment ADD COLUMN asu_username character varying(32);
UPDATE hms_assignment SET asu_username = temp_col;
ALTER TABLE hms_assignment DROP COLUMN temp_col;

ALTER TABLE hms_learning_community_assignment ADD COLUMN temp_col character varying(32);
UPDATE hms_learning_community_assignment SET temp_col = asu_username;
ALTER TABLE hms_learning_community_assignment DROP COLUMN asu_username;
ALTER TABLE hms_learning_community_assignment ADD COLUMN asu_username character varying(32);
UPDATE hms_learning_community_assignment SET asu_username = temp_col;
ALTER TABLE hms_learning_community_assignment DROP COLUMN temp_col;

ALTER TABLE hms_roommates ADD COLUMN temp_col character varying(32);
UPDATE hms_roommates SET temp_col = roommate_zero;
ALTER TABLE hms_roommates DROP COLUMN roommate_zero;
ALTER TABLE hms_roommates ADD COLUMN roommate_zero character varying(32);
UPDATE hms_roommates SET roommate_zero = temp_col;

UPDATE hms_roommates SET temp_col = roommate_one;
ALTER TABLE hms_roommates DROP COLUMN roommate_one;
ALTER TABLE hms_roommates ADD COLUMN roommate_one character varying(32);
UPDATE hms_roommates SET roommate_one = temp_col;

UPDATE hms_roommates SET temp_col = roommate_two;
ALTER TABLE hms_roommates DROP COLUMN roommate_two;
ALTER TABLE hms_roommates ADD COLUMN roommate_two character varying(32);
UPDATE hms_roommates SET roommate_two = temp_col;

UPDATE hms_roommates SET temp_col = roommate_three;
ALTER TABLE hms_roommates DROP COLUMN roommate_three;
ALTER TABLE hms_roommates ADD COLUMN roommate_three character varying(32);
UPDATE hms_roommates SET roommate_three = temp_col;
ALTER TABLE hms_roommates DROP COLUMN temp_col;

