CREATE TABLE hms_activity_log (
    user_id     CHARACTER VARYING(32)   NOT NULL,
    timestamp   INTEGER                 NOT NULL,
    activity    INTEGER                 NOT NULL,
    actor       CHARACTER VARYING(32)   NOT NULL,
    notes       CHARACTER VARYING(512)  NOT NULL,
    PRIMARY KEY (id)
);
