BEGIN;

CREATE TABLE hms_spring_application (
    id                      integer     NOT NULL,
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    PRIMARY KEY(id)
);

COMMIT;
