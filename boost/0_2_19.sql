ALTER TABLE hms_deadlines ADD COLUMN lottery_signup_begin_timestamp integer;
ALTER TABLE hms_deadlines ADD COLUMN lottery_signup_end_timestamp integer;

UPDATE hms_deadlines SET lottery_signup_begin_timestamp = 0;
UPDATE hms_deadlines SET lottery_signup_end_timestamp = 0;

ALTER TABLE hms_deadlines ALTER COLUMN lottery_signup_begin_timestamp SET NOT NULL;
ALTER TABLE hms_deadlines ALTER COLUMN lottery_signup_end_timestamp SET NOT NULL;
