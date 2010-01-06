DROP TABLE hms_application_features;

CREATE TABLE hms_application_feature (
	id			int NOT NULL,
    term    	int NOT NULL REFERENCES hms_term(term),
    name 		character varying(32) NOT NULL,
    startDate	int NOT NULL,
    endDate		int NOT NULL
);