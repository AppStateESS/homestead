CREATE TABLE hms_package_desk (
    id          integer NOT NULL,
    name        character varying,
    location    character varying,
    street      character varying,
    city        character varying,
    state       character varying,
    zip         character varying,
    PRIMARY KEY(id)
);

CREATE TABLE hms_package (
    id                  integer NOT NULL,
    carrier             character varying,
    tacking_number      character varying,
    addressed_to        character varying,
    addressed_phone     character varying,
    recipient_banner_id integer NOT NULL,
    received_on         integer NOT NULL,
    received_by         character varying,
    package_desk        integer NOT NULL references hms_package_desk(id),
    pickedup_on         integer,
    released_by         character varying,
    PRIMARY KEY(id)
);

ALTER TABLE hms_residence_hall ADD COLUMN package_desk integer REFERENCES hms_package_desk(id);
