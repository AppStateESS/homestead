ALTER TABLE hms_lottery_entry ADD COLUMN special_interest CHARACTER VARYING(32);

ALTER TABLE hms_lottery_entry ADD COLUMN roommate1_app_term integer;
ALTER TABLE hms_lottery_entry ADD COLUMN roommate2_app_term integer;
ALTER TABLE hms_lottery_entry ADD COLUMN roommate3_app_term integer;
