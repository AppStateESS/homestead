CREATE TABLE hms_role (
    id                  INTEGER                 NOT NULL,
    name                text                    NOT NULL,
    PRIMARY KEY(id)
);

INSERT INTO hms_role VALUES (1, 'RDs');
INSERT INTO hms_role VALUES (2, 'RLCs');
INSERT INTO hms_role VALUES (3, 'Assoc Director');

CREATE SEQUENCE hms_role_seq;
SELECT setval('hms_role_seq', (SELECT max(id) FROM hms_role));

CREATE TABLE hms_permission (
    id                  INTEGER                 NOT NULL,
    name                VARCHAR(32)             NOT NULL,
    full_name           text,
    PRIMARY KEY(id)
);

INSERT INTO hms_permission VALUES (1, 'email', 'send emails');
CREATE SEQUENCE hms_permission_seq;
SELECT setval('hms_permission_seq', (SELECT max(id) FROM hms_permission));

CREATE TABLE hms_role_perm (
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    permission          INTEGER NOT NULL REFERENCES hms_permission(id),
    PRIMARY KEY(role, permission)
);

INSERT INTO hms_role_perm VALUES (1,1);
INSERT INTO hms_role_perm VALUES (2,1);
INSERT INTO hms_role_perm VALUES (3,1);

CREATE TABLE hms_user_role (
    id                  INTEGER NOT NULL,
    user_id             INTEGER NOT NULL REFERENCES users(id),
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    class               VARCHAR(64),
    instance            INTEGER,
    UNIQUE (user_id, role, class, instance),
    PRIMARY KEY(id)
);

