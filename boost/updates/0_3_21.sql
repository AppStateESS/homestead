BEGIN;

ALTER TABLE hms_banner_queue ALTER COLUMN meal_code TYPE character(2); 
ALTER TABLE hms_banner_queue ALTER COLUMN meal_code set default 1;

UPDATE hms_banner_queue SET meal_code = 'S4' WHERE term = 200920;

COMMIT;
