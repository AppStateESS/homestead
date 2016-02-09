ALTER TABLE hms_learning_community_applications ADD COLUMN denied_email_sent smallint default 0 not null;
UPDATE hms_learning_community_applications set denied_email_sent = 1;
