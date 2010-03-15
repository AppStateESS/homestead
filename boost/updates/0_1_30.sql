ALTER TABLE hms_application ADD COLUMN entry_term integer;
UPDATE hms_application SET entry_term=200740;
ALTER TABLE hms_application ALTER COLUMN entry_term SET NOT NULL;
ALTER TABLE hms_application ADD CONSTRAINT application_key UNIQUE (hms_student_id, entry_term); 


ALTER TABLE hms_learning_community_applications ADD COLUMN entry_term integer;
UPDATE hms_learning_community_applications SET entry_term=200740;
ALTER TABLE hms_learning_community_applications ALTER COLUMN entry_term set NOT NULL;
ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlc_application_key UNIQUE (user_id, entry_term); 
