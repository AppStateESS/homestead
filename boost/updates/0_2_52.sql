ALTER TABLE hms_room ADD default_gender smallint;
UPDATE hms_room set default_gender = (select gender_type from hms_room as foo where foo.id = hms_room.id);
ALTER TABLE hms_room ALTER default_gender SET NOT NULL;
