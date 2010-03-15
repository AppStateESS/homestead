CREATE TABLE hms_roommate_hack (
    requestor          character varying(32)  NOT NULL,
    requestee          character varying(255) NOT NULL,
    requestee_username character varying(32)  NOT NULL,
    primary key(requestor)
);
