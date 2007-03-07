ALTER TABLE hms_learning_community_applications ALTER COLUMN assigned_by_user DROP NOT  NULL;
ALTER TABLE hms_learning_community_applications ALTER COLUMN assigned_by_user DROP DEFAULT;

ALTER TABLE hms_learning_community_applications ALTER COLUMN assigned_by_initials DROP NOT NULL;
ALTER TABLE hms_learning_community_applications ALTER COLUMN assigned_by_initials DROP DEFAULT;
