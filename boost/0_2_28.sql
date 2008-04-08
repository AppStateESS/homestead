ALTER TABLE hms_term ADD COLUMN banner_queue SMALLINT;
UPDATE hms_term SET banner_queue = 0;
ALTER TABLE hms_term ALTER COLUMN banner_queue SET NOT NULL;

CREATE TABLE hms_banner_queue(
    id INTEGER NOT NULL,
    type INTEGER NOT NULL,
    asu_username CHARACTER VARYING(32) NOT NULL,
    building_code CHARACTER VARYING(6) NOT NULL,
    bed_code CHARACTER VARYING(15) NOT NULL,
    meal_plan CHARACTER VARYING(5),
    meal_code SMALLINT DEFAULT 0,
    term INTEGER NOT NULL,
    queued_on INTEGER NOT NULL,
    queued_by INTEGER NOT NULL,
    PRIMARY KEY(id)
);

DROP TABLE hms_assign_queue;
DROP TABLE hms_remove_queue;
