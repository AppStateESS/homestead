ALTER TABLE hms_residence_hall ADD COLUMN numbering_scheme smallint;
ALTER TABLE hms_residence_hall ALTER COLUMN numbering_scheme SET NOT NULL;

ALTER TABLE hms_room ADD COLUMN temp_number character varying(5);
UPDATE hms_room SET temp_number = room_number;
ALTER TABLE hms_room DROP COLUMN room_number;
ALTER TABLE hms_room ADD COLUMN room_number character varying(5);
UPDATE hms_room SET room_number = temp_number;
ALTER TABLE hms_room DROP COLUMN temp_number;

