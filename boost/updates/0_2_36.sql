ALTER TABLE hms_deadlines ADD COLUMN move_in_timestamp integer;
UPDATE hms_deadlines set move_in_timestamp = 0;
ALTER TABLE hms_deadlines ALTER COLUMN move_in_timestamp SET NOT NULL;
