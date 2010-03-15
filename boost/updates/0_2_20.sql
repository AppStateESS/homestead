ALTER TABLE hms_learning_community_applications RENAME COLUMN entry_term TO term;
ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlc_app_fkey FOREIGN KEY(term) REFERENCES hms_term(term);
