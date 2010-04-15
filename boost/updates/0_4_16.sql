CREATE TABLE hms_role (
    id                  INTEGER                 NOT NULL,
    name                text                    NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_permission (
    id                  INTEGER                 NOT NULL,
    name                VARCHAR(32)             NOT NULL,
    full_name           text,
    PRIMARY KEY(id)
);

CREATE TABLE hms_role_perm (
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    permission          INTEGER NOT NULL REFERENCES hms_permission(id),
    PRIMARY KEY(role, permission)
);

CREATE TABLE hms_user_role (
    user_id             INTEGER NOT NULL REFERENCES users(id),
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    class               VARCHAR(64),
    instance            INTEGER,
    PRIMARY KEY(user_id, role)
);
