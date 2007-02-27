CREATE TABLE hms_learning_community_applications (
    id                              integer NOT NULL,
    user_id                         character varying(16) NOT NULL,
    date_submitted                  integer NOT NULL,
    rlc_first_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_second_choice_id            integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_third_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    why_specific_communities        character varying(500) NOT NULL,
    strengths_weaknesses            character varying(500) NOT NULL,
    rlc_question_0                  character varying(500),
    rlc_question_1                  character varying(500),
    rlc_question_2                  character varying(500),
    UNIQUE(user_id),
    PRIMARY KEY(id)
);

