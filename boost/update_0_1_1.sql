CREATE TABLE hms_assignment (
    id integer NOT NULL,
    asu_username character varying(16) NOT NULL,
    building_id integer NOT NULL,
    floor_id integer NOT NULL,
    room_id integer NOT NULL,
    primary key(id)
);

CREATE TABLE hms_roommates (
    id integer NOT NULL,
    roommate_zero character varying(16) NOT NULL,
    roommate_one character varying(16) NOT NULL,
    roommate_two character varying(16),
    roommate_three character varying(16),
    primary key(id)
);

CREATE TABLE hms_roommate_hashes (
    id integer NOT NULL,
    roommate_zero character varying(16) NOT NULL,
    roommate_one character varying(16) NOT NULL,
    roommate_two character varying(16),
    roommate_three character varying(16),
    approval_hash character varying(),
    approved smallint default 0,
    primary key(id)
);
