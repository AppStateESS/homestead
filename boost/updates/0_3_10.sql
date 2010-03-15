CREATE TABLE hms_eligibility_waiver (
   id                   INTEGER                 NOT NULL,
   asu_username         CHARACTER VARYING(32)   NOT NULL,
   term                 INTEGER                 NOT NULL,
   created_on           INTEGER                 NOT NULL,
   created_by           CHARACTER VARYING(32)   NOT NULL,
   PRIMARY KEY (id)
);
