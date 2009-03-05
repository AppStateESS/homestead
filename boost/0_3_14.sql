ALTER TABLE hms_lottery_entry RENAME phone_number TO cell_phone;
ALTER TABLE hms_application RENAME cellphone TO cell_phone;
ALTER TABLE hms_lottery_entry ADD COLUMN meal_option smallint;
