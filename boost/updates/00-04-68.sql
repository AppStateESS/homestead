create table hms_checkin (
    id                  integer NOT NULL,
    banner_id           integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    bed_id              integer NOT NULL REFERENCES hms_bed(id),
    room_id             integer NOT NULL REFERENCES hms_room(id),
    checkin_date        integer NOT NULL,
    checkin_by          character varying,
    key_code            character varying,
    checkout_date       integer,
    checkout_by         character varying,
    express_checkout    smallint,
    improper_checkout   smallint,
    PRIMARY KEY (id)
);

create index hms_ckecin_banner_id_idx ON hms_checkin(banner_id);