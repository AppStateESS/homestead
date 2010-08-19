CREATE TABLE hms_room_change_request (
    id                  INTEGER NOT NULL,
    state               INTEGER NOT NULL DEFAULT 0,
    term                INTEGER NOT NULL REFERENCES hms_term(term),
    curr_hall           INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    requested_bed_id    INTEGER REFERENCES hms_bed(id),
    reason              TEXT,
    cell_phone          VARCHAR(11),
    username            VARCHAR(32),
    rd_username         VARCHAR(32),
    rd_timestamp        INTEGER,
    denied_reason       TEXT,
    denied_by           VARCHAR(32),
    updated_on          INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_participants (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    username            VARCHAR(32),
    name                VARCHAR(255),
    role                VARCHAR(255),
    added_on            INTEGER NOT NULL,
    updated_on          INTEGER NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_preferences (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    building            INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    PRIMARY KEY(id)
);

INSERT INTO hms_permission (id, name, full_name) VALUES (2, 'room_change_approve', 'Approve Room Changes');
INSERT INTO hms_role_perm VALUES (1, 2);

ALTER TABLE hms_bed add column room_change_reserved SMALLINT NOT NULL DEFAULT(0)::smallint;
