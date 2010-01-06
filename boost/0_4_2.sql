ALTER TABLE hms_application_feature RENAME "startdate" TO start_date;
ALTER TABLE hms_application_feature RENAME "enddate" TO end_date;
ALTER TABLE hms_application_feature ADD COLUMN enabled smallint NOT NULL DEFAULT 0;
ALTER TABLE hms_application_feature ADD PRIMARY KEY id;
ALTER TABLE hms_term ADD COLUMN pdf_terms VARCHAR(255);
ALTER TABLE hms_term ADD COLUMN txt_terms VARCHAR(255);