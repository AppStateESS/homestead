ALTER TABLE hms_residence_hall ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_residence_hall SET term=200740;
ALTER TABLE hms_residence_hall ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_residence_hall DROP COLUMN number_floors;
ALTER TABLE hms_residence_hall DROP COLUMN rooms_per_floor;
ALTER TABLE hms_residence_hall DROP COLUMN bedrooms_per_room;
ALTER TABLE hms_residence_hall DROP COLUMN beds_per_bedroom;
ALTER TABLE hms_residence_hall DROP COLUMN numbering_scheme;

ALTER TABLE hms_residence_hall ADD COLUMN per_freshmen_rsvd integer;
ALTER TABLE hms_residence_hall ADD COLUMN per_sophomore_rsvd integer;
ALTER TABLE hms_residence_hall ADD COLUMN per_junior_rsvd integer;
ALTER TABLE hms_residence_hall ADD COLUMN per_senior_rsvd integer;

UPDATE hms_residence_hall SET per_freshmen_rsvd = 0, per_sophomore_rsvd = 0, per_junior_rsvd = 0, per_senior_rsvd = 0;

ALTER TABLE hms_residence_hall ALTER COLUMN per_freshmen_rsvd SET NOT NULL;
ALTER TABLE hms_residence_hall ALTER COLUMN per_sophomore_rsvd SET NOT NULL;
ALTER TABLE hms_residence_hall ALTER COLUMN per_junior_rsvd SET NOT NULL;
ALTER TABLE hms_residence_hall ALTER COLUMN per_senior_rsvd SET NOT NULL;


ALTER TABLE hms_floor ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_floor SET term = 200740;
ALTER TABLE hms_floor ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_floor RENAME COLUMN building TO residence_hall_id;
ALTER TABLE hms_floor ADD FOREIGN KEY (residence_hall_id) REFERENCES hms_residence_hall(id);

ALTER TABLE hms_floor DROP COLUMN number_rooms;
ALTER TABLE hms_floor DROP COLUMN bedrooms_per_room;
ALTER TABLE hms_floor DROP COLUMN beds_per_bedroom;
ALTER TABLE hms_floor DROP COLUMN freshman_reserved;

ALTER TABLE hms_room DROP COLUMN room_number;
ALTER TABLE hms_room RENAME COLUMN displayed_room_number TO room_number;
ALTER TABLE hms_room DROP COLUMN building_id;
ALTER TABLE hms_room DROP COLUMN floor_number;
ALTER TABLE hms_room DROP COLUMN freshman_reserved;
ALTER TABLE hms_room DROP COLUMN bedrooms_per_room;
ALTER TABLE hms_room DROP COLUMN beds_per_bedroom;
ALTER TABLE hms_room RENAME COLUMN learning_community TO learning_community_id;
ALTER TABLE hms_room DROP COLUMN phone_number;

ALTER TABLE hms_room ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_room SET term = 200740;
ALTER TABLE hms_room ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_room ADD FOREIGN KEY (floor_id) REFERENCES hms_floor(id);
ALTER TABLE hms_room ADD COLUMN suite_id integer REFERENCES hms_suite(id);

UPDATE hms_room SET suite_id = hms_suite.id WHERE hms_room.id = hms_suite.room_id_zero;
UPDATE hms_room SET suite_id = hms_suite.id WHERE hms_room.id = hms_suite.room_id_one;
UPDATE hms_room SET suite_id = hms_suite.id WHERE hms_room.id = hms_suite.room_id_two;
UPDATE hms_room SET suite_id = hms_suite.id WHERE hms_room.id = hms_suite.room_id_three;

ALTER TABLE hms_suite DROP COLUMN room_id_zero;
ALTER TABLE hms_suite DROP COLUMN room_id_one;
ALTER TABLE hms_suite DROP COLUMN room_id_two;
ALTER TABLE hms_suite DROP COLUMN room_id_three;

ALTER TABLE hms_suite ADD COLUMN floor_id integer REFERENCES hms_floor(id);
UPDATE hms_suite SET floor_id = hms_room.floor_id WHERE hms_suite.id = hms_room.suite_id;
ALTER TABLE hms_suite ALTER COLUMN floor_id SET NOT NULL;

ALTER TABLE hms_suite ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_suite SET term = 200740;
ALTER TABLE hms_suite ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_suite ADD COLUMN deleted smallint;
ALTER TABLE hms_suite ALTER COLUMN deleted set default 0;
UPDATE hms_suite SET deleted = 0;
ALTER TABLE hms_suite ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE hms_suite ADD COLUMN added_by smallint;
ALTER TABLE hms_suite ADD COLUMN added_on integer;
ALTER TABLE hms_suite ADD COLUMN updated_by smallint;
ALTER TABLE hms_suite ADD COLUMN updated_on integer;
ALTER TABLE hms_suite ADD COLUMN deleted_by smallint;
ALTER TABLE hms_suite ADD COLUMN deleted_on integer;

ALTER TABLE hms_bedrooms RENAME TO hms_bedroom;
ALTER TABLE hms_bedroom DROP COLUMN is_online;
ALTER TABLE hms_bedroom DROP COLUMN gender_type;
ALTER TABLE hms_bedroom DROP COLUMN number_beds;
ALTER TABLE hms_bedroom DROP COLUMN is_reserved;
ALTER TABLE hms_bedroom DROP COLUMN is_medical;
ALTER TABLE hms_bedroom DROP COLUMN phone_number;

ALTER TABLE hms_bedroom ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_bedroom SET term = 200740;
ALTER TABLE hms_bedroom ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_bedrooms_seq RENAME TO hms_bedroom_seq;

ALTER TABLE hms_beds RENAME TO hms_bed;
ALTER TABLE hms_bed ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_bed SET term = 200740;
ALTER TABLE hms_bed ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_beds_seq RENAME TO hms_bed_seq;

ALTER TABLE hms_assignment DROP COLUMN timestamp;

ALTER TABLE hms_assignment ADD COLUMN term integer REFERENCES hms_term(term);
UPDATE hms_assignment SET term = 200740;
ALTER TABLE hms_assignment ALTER COLUMN term SET NOT NULL;

ALTER TABLE hms_assignment ADD FOREIGN KEY (bed_id) REFERENCES hms_bed(id);

ALTER TABLE hms_assignment ADD COLUMN deleted_on integer;
ALTER TABLE hms_assignment ADD COLUMN deleted_by smallint;
ALTER TABLE hms_assignment ADD COLUMN updated_on integer;
ALTER TABLE hms_assignment ADD COLUMN updated_by smallint;
ALTER TABLE hms_assignment ADD COLUMN added_on integer;
ALTER TABLE hms_assignment ADD COLUMN added_by smallint;
