CREATE TABLE hms_deadlines (
    student_login_begin_timestamp integer NOT NULL,
    student_login_end_timestamp integer NOT NULL,
    submit_questionnaire_begin_timestamp integer NOT NULL,
    submit_questionnaire_end_timestamp integer NOT NULL,
    search_questionnaires_begin_timestamp integer NOT NULL,
    search_questionnaires_end_timestamp integer NOT NULL,
    view_assignment_begin_timestamp integer NOT NULL,
    view_assignment_end_timestamp integer NOT NULL,
    updated_by smallint NOT NULL,
    updated_on integer NOT NULL
);

CREATE TABLE hms_floor (
    id integer DEFAULT 0 NOT NULL,
    floor_number smallint DEFAULT 0::smallint NOT NULL,
    number_rooms smallint DEFAULT 0::smallint NOT NULL,
    capacity_per_room smallint NOT NULL,
    building smallint DEFAULT 0::smallint NOT NULL,
    is_online smallint DEFAULT 0::smallint NOT NULL,
    gender_type smallint DEFAULT 0::smallint NOT NULL,
    deleted smallint DEFAULT 0::smallint,
    deleted_by smallint NULL,
    deleted_on integer NULL,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    updated_by smallint not null,
    updated_on integer not null,
    PRIMARY KEY (id)
);

CREATE TABLE hms_hall_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(32) NOT NULL
);

CREATE TABLE hms_learning_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(32) NOT NULL
);

CREATE TABLE hms_pricing_tiers (
    id integer DEFAULT 0 NOT NULL,
    tier_value numeric NOT NULL
);

INSERT INTO hms_pricing_tiers (id, tier_value) VALUES ('1', '1500.00');

CREATE TABLE hms_questionnaire (
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
    deleted smallint DEFAULT 0 NOT NULL,
    deleted_by smallint NULL,
    deleted_on integer NULL,
    created_on integer NOT NULL,
    created_by character varying(10) NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_residence_hall (
    id integer DEFAULT 0 NOT NULL,
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
    deleted smallint DEFAULT 0::smallint NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_room (
    id integer DEFAULT 0 NOT NULL,
    room_number smallint NOT NULL,
    building_id smallint NOT NULL,
    floor_number smallint NOT NULL,
    floor_id integer NOT NULL,
    gender_type smallint NOT NULL,
    capacity_per_room smallint NOT NULL,
    learning_community smallint DEFAULT 0::smallint,
    phone_number integer DEFAULT 0,
    is_medical smallint DEFAULT 0::smallint,
    is_reserved smallint DEFAULT 0::smallint,
    is_online smallint DEFAULT 0::smallint NOT NULL,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    updated_by smallint,
    updated_on integer,
    deleted smallint DEFAULT 0::smallint NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_student (
    id integer DEFAULT 0 NOT NULL,
    asu_username character varying(10) NOT NULL,
    first_name character varying(32) NOT NULL,
    middle_name character varying(32) NULL,
    last_name character varying(32) NOT NULL,
    gender smallint NOT NULL,
    application_received smallint DEFAULT 0,
    added_by smallint NOT NULL,
    added_on integer NOT NULL,
    deleted_by smallint,
    deleted_on integer,
    updated_by smallint,
    updated_on integer,
    deleted smallint default 0,
    PRIMARY KEY (id)
);

CREATE TABLE hms_suite (
    id integer not null,
    room_id_zero integer not null,
    room_id_one integer not null,
    room_id_two integer,
    room_id_three integer,
    PRIMARY KEY(id)
);

ALTER TABLE hms_suite ADD CONSTRAINT room_id_zero_key UNIQUE (room_id_zero);
ALTER TABLE hms_suite ADD CONSTRAINT room_id_one_key UNIQUE (room_id_one);
ALTER TABLE hms_suite ADD CONSTRAINT room_id_two_key UNIQUE (room_id_two);
ALTER TABLE hms_suite ADD CONSTRAINT room_id_three_key UNIQUE (room_id_three);

CREATE TABLE hms_roommate (
    id integer not null,
    roommate_zero character varying(16) not null,
    roommate_one character varying(16) not null,
    roommate_two character varying(16),
    roommate_three character varying(16),
    deleted smallint default 0,
    deleted_by smallint,
    deleted_on integer,
    PRIMARY KEY(id)
);
    
CREATE SEQUENCE hms_questionnaire_seq;
CREATE SEQUENCE hms_floor_seq;
CREATE SEQUENCE hms_hall_communities_seq;
CREATE SEQUENCE hms_learning_communities_seq;
CREATE SEQUENCE hms_pricing_tiers_seq;
CREATE SEQUENCE hms_residence_hall_seq;
CREATE SEQUENCE hms_room_seq;
CREATE SEQUENCE hms_student_seq;
CREATE SEQUENCE hms_roommate_seq;
