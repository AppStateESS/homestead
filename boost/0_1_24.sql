ALTER TABLE hms_assignment ADD COLUMN timestamp INTEGER;
ALTER TABLE hms_assignment ADD COLUMN deleted SMALLINT;
UPDATE hms_assignment SET deleted = 0;
ALTER TABLE hms_assignment ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN freshman_reserved SMALLINT;
UPDATE hms_room SET freshman_reserved = 0;
ALTER TABLE hms_room ALTER COLUMN freshman_reserved SET NOT NULL;

ALTER TABLE hms_floor ADD COLUMN freshman_reserved SMALLINT;
UPDATE hms_floor SET freshman_reserved = 0;
ALTER TABLE hms_floor ALTER COLUMN freshman_reserved SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN ra_room SMALLINT;
UPDATE hms_room SET ra_room = 0;
ALTER TABLE hms_room ALTER COLUMN ra_room SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN private_room SMALLINT;
UPDATE hms_room SET private_room = 0;
ALTER TABLE hms_room ALTER COLUMN private_room SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN is_lobby SMALLINT;
UPDATE hms_room SET is_lobby = 0;
ALTER TABLE hms_room ALTER COLUMN is_lobby SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN pricing_tier SMALLINT;
UPDATE hms_room SET pricing_tier = hms_residence_hall.pricing_tier WHERE hms_room.building_id = hms_residence_hall.id;

ALTER TABLE hms_residence_hall DROP COLUMN pricing_tier;
