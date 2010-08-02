CREATE TABLE hms_assignment_version AS (SELECT *, 1 AS seq, 1 AS active FROM hms_assignment);
DROP TABLE hms_assignment;
CREATE VIEW hms_assignment AS SELECT id, bed_id, meal_option, letter_printed, asu_username, term, updated_on, updated_by, added_on, added_by, lottery, auto_assigned, email_sent FROM hms_assignment_version WHERE active=1;
