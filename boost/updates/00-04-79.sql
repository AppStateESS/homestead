drop table hms_room_change_preferences;
alter table hms_room_change_participants rename to hms_room_change_participants_old;
alter table hms_room_change_request rename to hms_room_change_request_old;

create table hms_room_change_request (
    id                      INTEGER NOT NULL,
    term                    INTEGER NOT NULL REFERENCES hms_term(term),
    reason                  TEXT,
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

CREATE VIEW hms_room_change_curr_request AS
    SELECT * FROM hms_room_change_request
    JOIN hms_room_change_request_state ON hms_room_change_request.id = hms_room_change_request_state.request_id
    WHERE 
        effective_date < extract(epoch from now()) AND
        effective_until_date IS NULL;

CREATE VIEW hms_room_change_curr_participant AS
    SELECT * FROM hms_room_change_participant
    JOIN hms_room_change_participant_state ON hms_room_change_participant.id = hms_room_change_participant_state.participant_id
    WHERE 
        effective_date < extract(epoch from now()) AND
        effective_until_date IS NULL;

CREATE VIEW hms_room_change_curr_request_participants AS 
    SELECT
        hms_room_change_curr_request.id,
        hms_room_change_curr_request.term,
        hms_room_change_curr_request.reason,
        hms_room_change_curr_request.denied_reason_public,
        hms_room_change_curr_request.denied_reason_private,
        hms_room_change_curr_request.state,
        hms_room_change_curr_request.effective_date,
        hms_room_change_curr_request.effective_until_date,
        hms_room_change_curr_request.committed_by,
        hms_room_change_curr_participant.id AS participant_id,
        hms_room_change_curr_participant.banner_id,
        hms_room_change_curr_participant.from_bed,
        hms_room_change_curr_participant.to_bed,
        hms_room_change_curr_participant.state AS participant_state,
        hms_room_change_curr_participant.effective_date AS participant_effective_date,
        hms_room_change_curr_participant.effective_until_date AS participant_effective_until_date
    FROM hms_room_change_curr_request
    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id;

