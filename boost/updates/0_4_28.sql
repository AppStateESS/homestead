drop table hms_cached_student_info;

CREATE TABLE hms_student_cache (
    banner_id           integer NOT NULL,
    term                integer NOT NULL,
    timestamp           integer NOT NULL,
    username            character varying(155) NOT NULL,
    last_name           character varying(255) NOT NULL,
    first_name          character varying(255) NOT NULL,
    middle_name         character varying(255),
    dob                 character(10) NOT NULL,
    gender              character(1) NOT NULL,
    deposit_date        character(10),
    type                character(1) NOT NULL,
    application_term    character(6) NOT NULL,
    class               character(2) NOT NULL,
    credit_hours        integer NOT NULL,
    student_level       character varying(16) NOT NULL,
    international       character varying(5) NOT NULL,
    honors              character varying(5) NOT NULL,
    teaching_fellow     character varying(5) NOT NULL,
    watauga_member      character varying(5) NOT NULL,
    PRIMARY KEY (banner_id, term)
);

CREATE TABLE hms_student_address_cache (
    banner_id       integer NOT NULL,
    atyp_code       character varying(2) NOT NULL,
    line1           character varying(255) NOT NULL,
    line2           character varying(255) NOT NULL,
    line3           character varying(255) NOT NULL,
    city            character varying(255) NOT NULL,
    state           character varying(255) NOT NULL,
    zip             integer NOT NULL
);

CREATE INDEX hms_student_address_cache_idx ON hms_student_address_cache(banner_id);

CREATE TABLE hms_student_phone_cache (
    banner_id       integer NOT NULL,
    number          character varying(32) NOT NULL
);

CREATE INDEX hms_student_phone_cache_idx ON hms_student_phone_cache(banner_id);