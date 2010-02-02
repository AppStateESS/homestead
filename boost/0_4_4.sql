alter table hms_learning_communities add column allowed_student_types varchar(16);
CREATE UNIQUE INDEX hms_application_feature_term_name_idx ON hms_application_feature (term, name);
