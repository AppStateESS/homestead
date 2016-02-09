ALTER TABLE hms_learning_community_applications ADD COLUMN denied_email_sent smallint;
UPDATE hms_learning_community_applications set denied_email_sent = 1;
