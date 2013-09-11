drop table hms_room_change_preferences;
alter table hms_room_change_participants rename to hms_room_change_participants_old;
alter table hms_room_change_request rename to hms_room_change_request_old;

create table hms_room_change_request (
    id                      INTEGER NOT NULL,
    term                    INTEGER NOT NULL REFERENCES hms_term(term),
    denied_reason_public    TEXT,
    denied_reason_private   TEXT,
    PRIMARY KEY(id)
);

create table hms_room_change_request_state (
    request_id              INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    state                   character varying,
    effective_date          INTEGER NOT NULL,
    effective_until_date    INTEGER,
    committed_by            character varying,
    PRIMARY KEY(request_id, effective_date)
);

create table hms_room_change_participant (
    id              INTEGER NOT NULL,
    request_id      INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    banner_id       INTEGER NOT NULL,
    from_bed        INTEGER NOT NULL,
    to_bed          INTEGER,
    hall_pref1      INTEGER,
    hall_pref2      INTEGER,
    PRIMARY KEY(id)
);

create table hms_room_change_participant_state (
    participant_id          INTEGER NOT NULL REFERENCES hms_room_change_participant(id),
    state                   character varying,
    effective_date          INTEGER NOT NULL,
    effective_until_date    INTEGER,
    committed_by            character varying,
    PRIMARY KEY(participant_id, effective_date)
);