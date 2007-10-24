CREATE TABLE hms_term (
    id      integer NOT NULL,
    term    integer NOT NULL,
    primary key(id)
);

ALTER TABLE hms_term ADD CONSTRAINT hms_term_unique_key UNIQUE (term);
