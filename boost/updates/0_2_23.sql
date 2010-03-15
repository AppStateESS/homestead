DROP TABLE hms_roommate_approval;
DROP SEQUENCE hms_roommate_approval_seq;
DROP TABLE hms_roommate_hack;
DROP TABLE hms_roommate_hashes;
DROP SEQUENCE hms_roommate_seq;
DROP TABLE hms_roommates;
DROP SEQUENCE hms_roommates_seq;

CREATE TABLE hms_roommate (
    id           INTEGER NOT NULL,
    term         INTEGER NOT NULL REFERENCES hms_term(term),
    requestor    CHARACTER VARYING(32) NOT NULL,
    requestee    CHARACTER VARYING(32) NOT NULL,
    confirmation_hash CHARACTER VARYING(32) NOT NULL,
    confirmed    INTEGER NOT NULL DEFAULT 0,
    requested_on INTEGER NOT NULL,
    confirmed_on INTEGER,
    PRIMARY KEY(id)
);
