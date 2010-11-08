CREATE TABLE hms_report_exec (
    id              INTEGER NOT NULL,
    report          character varying(255) NOT NULL,
    format          character varying(255) NOT NULL,
    from_term       INTEGER NOT NULL REFERENCES hms_term(term),
    to_term         INTEGER NOT NULL REFERENCES hms_term(term),
    exec_timestamp  INTEGER NOT NULL,
    exec_by_user_id INTEGER NOT NULL,
    PRIMARY KEY (id)
);