ALTER TABLE hms_student_cache ADD COLUMN disabled_pin smallint NOT NULL DEFAULT 0;
ALTER TABLE hms_student_cache ADD COLUMN housing_waiver smallint NOT NULL DEFAULT 0;