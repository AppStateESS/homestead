DELETE FROM hms_application WHERE deleted = 1;
ALTER TABLE hms_application DROP COLUMN deleted;
ALTER TABLE hms_application DROP COLUMN deleted_by;
ALTER TABLE hms_application DROP COLUMN deleted_on;

ALTER TABLE hms_application RENAME COLUMN hms_student_id TO asu_username;

ALTER TABLE hms_application ALTER COLUMN meal_option DROP NOT NULL;

