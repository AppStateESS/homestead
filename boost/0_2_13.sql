CREATE TABLE hms_movein_time (
    id              integer NOT NULL,
    begin_timestamp   integer NOT NULL,
    end_timestamp   integer NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    primary key(id)
);
ALTER TABLE hms_movein_time ADD CONSTRAINT unique_time UNIQUE (begin_timestamp, end_timestamp, term);
