ALTER TABLE hms_residence_hall ADD COLUMN banner_building_code character varying(6);

ALTER TABLE hms_learning_community_applications ADD COLUMN required_course smallint;
ALTER TABLE hms_learning_community_applications ALTER COLUMN required_course SET NOT NULL;
ALTER TABLE hms_learning_community_applications ALTER COLUMN required_course SET DEFAULT 0;

ALTER TABLE hms_learning_community_applications ADD COLUMN approved smallint;
ALTER TABLE hms_learning_community_applications ALTER COLUMN approved SET NOT NULL;
ALTER TABLE hms_learning_community_applications ALTER COLUMN approved SET DEFAULT 0;

CREATE TABLE hms_learning_community_floor (
    learning_communities_id integer NOT NULL REFERENCES hms_learning_communities(id),
    floor_id                integer NOT NULL REFERENCES hms_floor(id),
    PRIMARY KEY (learning_communities_id)
)
