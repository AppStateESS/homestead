CREATE TABLE hms_assign_queue (
    id              integer NOT NULL,
    asu_username    character varying(32) NOT NULL,
    building_code   character varying(6) NOT NULL,
    bed_code        character varying(15) NOT NULL,
    meal_plan       character varying(5) NOT NULL,
    meal_code       smallint NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    queued_on       integer NOT NULL,
    queued_by       integer NOT NULL,
    primary key(id)
);

CREATE TABLE hms_remove_queue (
    id            integer NOT NULL,
    asu_username  character varying(32) NOT NULL,
    building_code character varying(6) NOT NULL,
    bed_code      character varying(15) NOT NULL,
    term          integer NOT NULL REFERENCES hms_term(term),
    queued_on     integer NOT NULL,
    queued_by     integer NOT NULL,
    primary key(id)
);
