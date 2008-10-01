CREATE TABLE hms_term_applications (
    app_term integer NOT NULL REFERENCES hms_term(term),
    term     integer NOT NULL REFERENCES hms_term(term),
    required integer NOT NULL default 0
);
ALTER TABLE hms_term_applications ADD CONSTRAINT unique_term_pairing UNIQUE (app_term, term);
