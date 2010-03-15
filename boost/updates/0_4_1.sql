CREATE TABLE hms_lottery_application (
    id                      integer NOT NULL REFERENCES hms_new_application(id),
    roommate1_username      character varying(32),
    roommate2_username      character varying(32),
    roommate3_username      character varying(32),
    roommate1_app_term      integer,
    roommate2_app_term      integer,
    roommate3_app_term      integer,
    special_interest        character varying(32),
    magic_winner            smallint NOT NULL default 0,
    invite_expires_on       integer,
    waiting_list_hide       smallint DEFAULT 0,
    PRIMARY KEY(id)
);