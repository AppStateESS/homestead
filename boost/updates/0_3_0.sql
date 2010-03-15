BEGIN;
CREATE TABLE hms_lottery_entry (
    id                  INTEGER                 NOT NULL,
    asu_username        CHARACTER VARYING(32)   NOT NULL,
    term                INTEGER                 NOT NULL,
    created_on          INTEGER                 NOT NULL,
    application_term    INTEGER                 NOT NULL,
    gender              smallint                NOT NULL,
    roommate1_username  CHARACTER VARYING(32),
    roommate2_username  CHARACTER VARYING(32),
    roommate3_username  CHARACTER VARYING(32),
    physical_disability smallint DEFAULT 0,
    psych_disability    smallint DEFAULT 0,
    medical_need        smallint DEFAULT 0,
    gender_need         smallint DEFAULT 0,
    invite_expires_on   INTEGER,
    PRIMARY KEY (id)
);

CREATE TABLE hms_lottery_reservation (
    id                  INTEGER                 NOT NULL,
    asu_username        CHARACTER VARYING(32)   NOT NULL,
    requestor           CHARACTER VARYING(32)   NOT NULL,
    term                INTEGER                 NOT NULL,
    bed_id              INTEGER                 NOT NULL,
    expires_on          INTEGER                 NOT NULL,
    PRIMARY KEY (id)
);

ALTER TABLE hms_assignment ADD COLUMN lottery smallint;
UPDATE hms_assignment set lottery = 0;
ALTER TABLE hms_assignment ALTER COLUMN lottery SET DEFAULT 0;
ALTER TABLE hms_assignment ALTER COLUMN lottery SET NOT NULL;

ALTER TABLE hms_assignment ADD COLUMN auto_assigned smallint;
UPDATE hms_assignment SET auto_assigned = 0;
ALTER TABLE hms_assignment ALTER COLUMN auto_assigned SET DEFAULT 0;
ALTER TABLE hms_assignment ALTER COLUMN auto_assigned SET NOT NULL;

ALTER TABLE hms_residence_hall DROP COLUMN per_freshmen_rsvd;
ALTER TABLE hms_residence_hall DROP COLUMN per_sophomore_rsvd;
ALTER TABLE hms_residence_hall DROP COLUMN per_junior_rsvd;
ALTER TABLE hms_residence_hall DROP COLUMN per_senior_rsvd;

ALTER TABLE hms_residence_hall ADD COLUMN rooms_for_lottery integer;
ALTER TABLE hms_residence_hall ALTER COLUMN rooms_for_lottery SET DEFAULT 0;
UPDATE hms_residence_hall SET rooms_for_lottery = 0;
ALTER TABLE hms_residence_hall ALTER COLUMN rooms_for_lottery SET NOT NULL;

ALTER TABLE hms_lottery_entry ADD CONSTRAINT unique_entry UNIQUE (term, asu_username);

ALTER TABLE hms_room DROP COLUMN learning_community_id;

ALTER TABLE hms_residence_hall ADD COLUMN meal_plan_required smallint default 0 NOT NULL;

ALTER TABLE hms_floor ADD COLUMN floor_plan_image_id integer DEFAULT 0;
ALTER TABLE hms_residence_hall ADD COLUMN exterior_image_id integer DEFAULT 0;
ALTER TABLE hms_residence_hall ADD COLUMN other_image_id integer DEFAULT 0;
ALTER TABLE hms_residence_hall ADD COLUMN map_image_id integer DEFAULT 0;
ALTER TABLE hms_residence_hall ADD COLUMN room_plan_image_id integer DEFAULT 0;
COMMIT;
