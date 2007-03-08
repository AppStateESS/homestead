ALTER TABLE hms_learning_community_applications DROP COLUMN approved;
ALTER TABLE hms_learning_community_applications DROP COLUMN assigned_by_user;
ALTER TABLE hms_learning_community_applications DROP COLUMN assigned_by_initials;

CREATE TABLE hms_learning_community_assignment (
    id                   integer NOT NULL,
    asu_username         character varying(11) UNIQUE NOT NULL,
    rlc_id               integer NOT NULL REFERENCES hms_learning_communities(id),
    assigned_by_user     integer NOT NULL,
    assigned_by_initials character varying(8),
    PRIMARY KEY (id)
);

ALTER TABLE hms_learning_community_applications ADD COLUMN hms_assignment_id integer;
ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlcapp_assignment_id
    FOREIGN KEY (hms_assignment_id) REFERENCES hms_learning_community_assignment (id);

ALTER TABLE hms_learning_communities ADD COLUMN abbreviation character varying(16);
ALTER TABLE hms_learning_communities ALTER COLUMN abbreviation SET NOT NULL;

ALTER TABLE hms_learning_communities ADD COLUMN capacity integer;
ALTER TABLE hms_learning_communities ALTER COLUMN capacity SET NOT NULL;

ALTER TABLE hms_residence_hall ADD COLUMN bedrooms_per_room smallint;
ALTER TABLE hms_residence_hall ALTER COLUMN bedrooms_per_room SET NOT NULL;
ALTER TABLE hms_residence_hall ADD COLUMN beds_per_bedroom smallint;
ALTER TABLE hms_residence_hall ALTER COLUMN beds_per_bedroom SET NOT NULL;
ALTER TABLE hms_residence_hall DROP COLUMN capacity_per_room;

ALTER TABLE hms_floor ADD COLUMN bedrooms_per_room smallint;
ALTER TABLE hms_floor ALTER COLUMN bedrooms_per_room SET NOT NULL;
ALTER TABLE hms_floor ADD COLUMN beds_per_bedroom smallint;
ALTER TABLE hms_floor ALTER COLUMN beds_per_bedroom SET NOT NULL;
ALTER TABLE hms_floor DROP COLUMN capacity_per_room;

ALTER TABLE hms_room ADD COLUMN bedrooms_per_room smallint;
ALTER TABLE hms_room ALTER COLUMN bedrooms_per_room SET NOT NULL;
ALTER TABLE hms_room ADD COLUMN beds_per_bedroom smallint;
ALTER TABLE hms_room ALTER COLUMN beds_per_bedroom SET NOT NULL;
ALTER TABLE hms_room DROP COLUMN capacity_per_room;

CREATE TABLE hms_bedrooms (
    id INTEGER NOT NULL,
    room_id INTEGER NOT NULL REFERENCES hms_room(id),
    is_online SMALLINT NOT NULL,
    gender_type SMALLINT NOT NULL,
    number_beds SMALLINT NOT NULL,
    is_reserved SMALLINT NOT NULL,
    is_medical SMALLINT NOT NULL,
    added_by INTEGER NOT NULL,
    added_on INTEGER NOT NULL,
    updated_by INTEGER NOT NULL,
    updated_on INTEGER NOT NULL,
    deleted_by INTEGER,
    deleted_on INTEGER,
    bedroom_letter character(1) NOT NULL,
    phone_number INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE hms_beds (
    id INTEGER NOT NULL,
    bedroom_id INTEGER NOT NULL REFERENCE hms_bedrooms(id),
    bed_letter character(1) NOT NULL,
    PRIMARY KEY(id)
);
