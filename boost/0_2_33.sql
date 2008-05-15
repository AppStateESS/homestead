BEGIN;
UPDATE hms_floor SET deleted = 1 WHERE hms_residence_hall.deleted = 1 AND hms_residence_hall.id = hms_floor.residence_hall_id;
UPDATE hms_suite SET deleted = 1 WHERE hms_floor.deleted = 1 AND hms_suite.floor_id = hms_floor.id;
UPDATE hms_room SET deleted = 1 WHERE hms_floor.deleted = 1 AND hms_floor.id = hms_room.floor_id;
UPDATE hms_bed SET deleted = 1 WHERE hms_room.deleted = 1 AND hms_room.id = hms_bed.room_id;
UPDATE hms_assignment SET deleted = 1 WHERE hms_bed.deleted = 1 AND hms_bed.id = hms_assignment.bed_id;
DELETE FROM hms_assignment WHERE deleted = 1;
DELETE from hms_bed where deleted = 1;
DELETE from hms_room where deleted = 1;
DELETE FROM hms_suite WHERE deleted = 1;
DELETE from hms_floor where deleted = 1;
DELETE from hms_residence_hall where deleted = 1;


ALTER TABLE hms_residence_hall DROP COLUMN deleted;
ALTER TABLE hms_residence_hall DROP COLUMN deleted_on;
ALTER TABLE hms_residence_hall DROP COLUMN deleted_by;
ALTER TABLE hms_floor DROP COLUMN deleted;
ALTER TABLE hms_floor DROP COLUMN deleted_on;
ALTER TABLE hms_floor DROP COLUMN deleted_by;
ALTER TABLE hms_room DROP COLUMN deleted;
ALTER TABLE hms_room DROP COLUMN deleted_on;
ALTER TABLE hms_room DROP COLUMN deleted_by;
ALTER TABLE hms_bed DROP COLUMN deleted;
ALTER TABLE hms_assignment DROP COLUMN deleted;
ALTER TABLE hms_assignment DROP COLUMN deleted_by;
ALTER TABLE hms_assignment DROP COLUMN deleted_on;
ALTER TABLE hms_suite DROP COLUMN deleted;
ALTER TABLE hms_suite DROP COLUMN deleted_on;
ALTER TABLE hms_suite DROP COLUMN deleted_by;

COMMIT;
