CREATE TABLE hms_cached_student_info (
    id              INTEGER                NOT NULL,
    asu_username    CHARACTER VARYING(32)  NOT NULL,
    room_number     CHARACTER VARYING(10)  NOT NULL,
    hall_name       CHARACTER VARYING(64)  NOT NULL,
    first_name      CHARACTER VARYING(64)  NOT NULL,
    middle_name     CHARACTER VARYING(64),
    last_name       CHARACTER VARYING(64)  NOT NULL,
    address1        CHARACTER VARYING(128),
    address2        CHARACTER VARYING(128),
    address3        CHARACTER VARYING(128),
    city            CHARACTER VARYING(64),
    state           CHARACTER VARYING(5),
    zip             CHARACtER VARYING(11),
    roommate_name   CHARACTER VARYING(172),
    roommate_email  CHARACTER VARYING(128),
    room_phone      CHARACTER VARYING(20),
    phone_number    CHARACTER VARYING(20),
    gender          CHARACTER(1),
    student_type    CHARACTER(5),
    class           CHARACTER(5),
    credit_hours    INTEGER,
    deposit_date    CHARACTER(10),
    deposit_waived  CHARACTER(5),
    PRIMARY KEY (id)
);

CREATE TABLE hms_pending_assignment (
    id               INTEGER               NOT NULL,
    gender           SMALLINT              NOT NULL,
    lifestyle_option SMALLINT              NOT NULL,
    chosen           SMALLINT              NOT NULL,
    roommate_zero    CHARACTER VARYING(32) NOT NULL,
    meal_zero        SMALLINT              NOT NULL,
    roommate_one     CHARACTER VARYING(32),
    meal_one         SMALLINT,
    PRIMARY KEY (id)
);
