BEGIN;

CREATE TABLE hms_fall_application (
    id                      integer     NOT NULL,
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    rlc_interest            smallint    NOT NULL,
    PRIMARY KEY(id)
);

ALTER TABLE hms_new_application ADD COLUMN student_type character(1) NOT NULL;

COMMIT;
