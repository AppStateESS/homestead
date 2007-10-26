ALTER TABLE hms_application DROP CONSTRAINT application_key;
ALTER TABLE hms_application RENAME COLUMN entry_term TO term;
ALTER TABLE hms_application ADD CONSTRAINT application_key UNIQUE (hms_student_id, term);

INSERT INTO hms_term (term) VALUES (200740);
INSERT INTO hms_term (term) VALUES (200810);
ALTER TABLE hms_application ADD CONSTRAINT term_fkey FOREIGN KEY(term) REFERENCES hms_term(term);
