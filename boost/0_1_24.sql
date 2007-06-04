ALTER TABLE hms_assignment ADD COLUMN timestamp INTEGER;
ALTER TABLE hms_assignment ADD COLUMN deleted SMALLINT;
UPDATE hms_assignment SET deleted = 0;
