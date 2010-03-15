DROP TABLE hms_questionnaire;

CREATE TABLE hms_application (
    id integer DEFAULT 0 NOT NULL,
    hms_student_id character varying(10) NOT NULL,
    student_status smallint NOT NULL,
    term_classification smallint NOT NULL,
    gender smallint NOT NULL,
    meal_option smallint NOT NULL,
    lifestyle_option smallint NOT NULL,
    preferred_bedtime smallint NOT NULL,
    room_condition smallint NOT NULL,
    in_relationship smallint NOT NULL,
    currently_employed smallint NOT NULL,
    rlc_interest smallint NOT NULL,
    agreed_to_terms smallint NOT NULL default 0,
    deleted smallint DEFAULT 0 NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    created_on integer NOT NULL,
    created_by character varying(10) NOT NULL,
    primary key(id)
);
