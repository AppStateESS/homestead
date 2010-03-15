delete from hms_learning_community_assignment;
ALTER TABLE hms_learning_community_assignment DROP COLUMN gender;
ALTER TABLE hms_learning_community_assignment ADD COLUMN gender integer;
ALTER TABLE hms_learning_community_assignment ALTER COLUMN gender SET NOT NULL;

ALTER TABLE hms_learning_community_assignment DROP COLUMN asu_username;
