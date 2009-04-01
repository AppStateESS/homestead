BEGIN;
CREATE TABLE hms_new_application (
    id                  integer                 NOT NULL,
    term                integer                 NOT NULL REFERENCES hms_term(term),
    banner_id           character varying(9)    NOT NULL,
    username            character varying(32)   NOT NULL,
    gender              smallint                NOT NULL,
    application_term    integer                 NOT NULL,
    cell_phone          character varying(10),
    PRIMARY KEY(id)
);

ALTER TABLE hms_new_application ADD CONSTRAINT new_application_key UNIQUE (username, term);
ALTER TABLE hms_new_application ADD CONSTRAINT new_application_key2 UNIQUE (banner_id, term);

CREATE TABLE hms_summer_application (
    id          integer NOT NULL,
    room_type   integer NOT NULL,
    PRIMARY KEY(id)
);
COMMIT;
