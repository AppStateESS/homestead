ALTER TABLE hms_learning_community_applications ADD COLUMN denied smallint;
UPDATE hms_learning_community_applications SET denied = 0;
ALTER TABLE hms_learning_community_applications ALTER COLUMN denied SET DEFAULT 0;
ALTER TABLE hms_learning_community_applications ALTER COLUMN denied SET NOT NULL;
