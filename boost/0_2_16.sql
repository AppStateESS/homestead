ALTER TABLE hms_bed ADD COLUMN room_id integer REFERENCES hms_room(id);
UPDATE hms_bed SET room_id = hms_bedroom.room_id WHERE hms_bed.bedroom_id = hms_bedroom.id;
ALTER TABLE hms_bed ALTER COLUMN room_id SET NOT NULL;

ALTER TABLE hms_bed ADD COLUMN bedroom_label character varying(255);
UPDATE hms_bed SET bedroom_label = hms_bedroom.bedroom_letter WHERE hms_bed.bedroom_id = hms_bedroom.id;

ALTER TABLE hms_bed DROP COLUMN bedroom_id;
DROP TABLE hms_bedrooms;
