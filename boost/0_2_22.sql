ALTER TABLE hms_deadlines ADD COLUMN select_roommate_begin_timestamp integer;
UPDATE hms_deadlines SET select_roommate_begin_timestamp = 0;
ALTER TABLE hms_deadlines ALTER COLUMN select_roommate_begin_timestamp SET NOT NULL;

ALTER TABLE hms_deadlines ADD COLUMN select_roommate_end_timestamp integer;
UPDATE hms_deadlines SET select_roommate_end_timestamp = 0;
ALTER TABLE hms_deadlines ALTER COLUMN select_roommate_end_timestamp SET NOT NULL;
