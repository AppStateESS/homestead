ALTER TABLE hms_email_log
ADD COLUMN opened int NOT NULL DEFAULT 0,
ADD COLUMN link_clicked int NOT NULL DEFAULT 0,
ADD COLUMN email_content VARCHAR,
ADD COLUMN time_made int;
