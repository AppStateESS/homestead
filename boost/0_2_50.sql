CREATE TABLE hms_application_features (
    term    int NOT NULL REFERENCES hms_term(term),
    feature int NOT NULL,
    enabled int NOT NULL default 0
);
