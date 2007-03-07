CREATE TABLE hms_assignment (
    id integer NOT NULL,
    asu_username character varying(16) NOT NULL,
    building_id integer NOT NULL,
    floor_id integer NOT NULL,
    room_id integer NOT NULL,
    primary key(id)
);

CREATE TABLE hms_deadlines (
    student_login_begin_timestamp integer NOT NULL,
    student_login_end_timestamp integer NOT NULL,
    submit_application_begin_timestamp integer NOT NULL,
    submit_application_end_timestamp integer NOT NULL,
    edit_application_end_timestamp integer NOT NULL,
    search_profiles_begin_timestamp integer NOT NULL,
    search_profiles_end_timestamp integer NOT NULL,
    submit_rlc_application_end_timestamp integer NOT NULL,
    view_assignment_begin_timestamp integer NOT NULL,
    view_assignment_end_timestamp integer NOT NULL,
    updated_by smallint NOT NULL,
    updated_on integer NOT NULL
);

CREATE TABLE hms_floor (
    id integer NOT NULL,
    floor_number smallint DEFAULT (0)::smallint NOT NULL,
    number_rooms smallint DEFAULT (0)::smallint NOT NULL,
    capacity_per_room smallint NOT NULL,
    building smallint DEFAULT (0)::smallint NOT NULL,
    is_online smallint DEFAULT (0)::smallint NOT NULL,
    gender_type smallint DEFAULT (0)::smallint NOT NULL,
    deleted smallint DEFAULT (0)::smallint,
    deleted_by smallint,
    deleted_on integer,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    updated_by smallint NOT NULL,
    updated_on integer NOT NULL,
    primary key(id)
);


CREATE TABLE hms_hall_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(32) NOT NULL,
    abbreviation character varying(16) NOT NULL,
    capacity integer NOT NULL,
    primary key(id)
);

CREATE TABLE hms_learning_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(32) NOT NULL,
    primary key(id)
);

CREATE TABLE hms_learning_community_questions (
    id integer DEFAULT 0 NOT NULL,
    learning_community_id integer DEFAULT 0 NOT NULL REFERENCES hms_learning_communities(id),
    question_text text NOT NULL,
    primary key(id)
);

CREATE TABLE hms_learning_community_assignment (
    id                   integer NOT NULL,
    asu_username         character varying(11) UNIQUE NOT NULL,
    rlc_id               integer NOT NULL REFERENCES hms_learning_communities(id),
    assigned_by_user     integer NOT NULL,
    assigned_by_initials character varying(8),
    PRIMARY KEY (id)
);

CREATE TABLE hms_learning_community_applications (
    id                              integer NOT NULL,
    user_id                         character varying(16) UNIQUE NOT NULL,
    date_submitted                  integer NOT NULL,
    rlc_first_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_second_choice_id            integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_third_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    why_specific_communities        character varying(500) NOT NULL,
    strengths_weaknesses            character varying(500) NOT NULL,
    rlc_question_0                  character varying(500),
    rlc_question_1                  character varying(500),
    rlc_question_2                  character varying(500),
    required_course                 smallint NOT NULL default 0,
    hms_assignment_id               integer REFERENCES hms_learning_community_assignment(id),
    PRIMARY KEY(id)
);

CREATE TABLE hms_learning_community_floors (
    learning_communities_id integer NOT NULL REFERENCES hms_learning_communities(id),
    floor_id                integer NOT NULL REFERENCES hms_floor(id),
    PRIMARY KEY (learning_communities_id)
);

CREATE TABLE hms_pricing_tiers (
    id integer DEFAULT 0 NOT NULL,
    tier_value numeric NOT NULL,
    primary key(id)
);

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


CREATE TABLE hms_residence_hall (
    id integer DEFAULT 0 NOT NULL,
    banner_building_code character varying(6) NULL,
    hall_name character varying(64) NOT NULL,
    number_floors smallint NOT NULL,
    rooms_per_floor smallint NOT NULL,
    capacity_per_room smallint NOT NULL,
    pricing_tier smallint NOT NULL,
    gender_type smallint NOT NULL,
    air_conditioned smallint NOT NULL,
    is_online smallint NOT NULL,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    updated_by smallint,
    updated_on integer,
    deleted smallint DEFAULT (0)::smallint NOT NULL,
    primary key(id)
);

CREATE TABLE hms_room (
    id integer DEFAULT 0 NOT NULL,
    room_number smallint NOT NULL,
    building_id smallint NOT NULL,
    floor_number smallint NOT NULL,
    floor_id integer NOT NULL,
    gender_type smallint NOT NULL,
    capacity_per_room smallint NOT NULL,
    learning_community smallint DEFAULT (0)::smallint,
    phone_number integer DEFAULT 0,
    is_medical smallint DEFAULT (0)::smallint,
    is_reserved smallint DEFAULT (0)::smallint,
    is_online smallint DEFAULT (0)::smallint NOT NULL,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    updated_by smallint,
    updated_on integer,
    deleted smallint DEFAULT (0)::smallint NOT NULL,
    primary key(id)
);

CREATE TABLE hms_roommates (
    id integer NOT NULL,
    roommate_zero character varying(16) NOT NULL,
    roommate_one character varying(16) NOT NULL,
    roommate_two character varying(16),
    roommate_three character varying(16),
    primary key(id)
);

CREATE TABLE hms_roommate_hashes (
    id integer NOT NULL,
    roommate_zero character varying(16) NOT NULL,
    roommate_one character varying(16) NOT NULL,
    roommate_two character varying(16),
    roommate_three character varying(16),
    approval_hash character varying(40),
    approved smallint default 0,
    primary key(id)
);

CREATE TABLE hms_student (
    id integer DEFAULT 0 NOT NULL,
    asu_username character varying(10) NOT NULL,
    first_name character varying(32) NOT NULL,
    middle_name character varying(32),
    last_name character varying(32) NOT NULL,
    gender smallint NOT NULL,
    application_received smallint DEFAULT 0,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    updated_by smallint,
    updated_on integer,
    deleted smallint DEFAULT 0,
    primary key(id)
);

CREATE TABLE hms_suite (
    id integer NOT NULL,
    room_id_zero integer NOT NULL,
    room_id_one integer NOT NULL,
    room_id_two integer,
    room_id_three integer,
    primary key(id)
);
