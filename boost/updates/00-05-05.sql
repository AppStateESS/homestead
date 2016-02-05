ALTER TABLE hms_room ADD COLUMN reserved_reason character varying;
ALTER TABLE hms_room ADD COLUMN reserved_notes character varying;
UPDATE hms_room SET reserved_reason = 'See Notes Section' WHERE reserved = 1;
