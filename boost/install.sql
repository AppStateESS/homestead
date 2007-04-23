CREATE TABLE hms_assignment (
    id integer NOT NULL,
    asu_username character varying(32) NOT NULL,
    bed_id integer NOT NULL,
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
    bedrooms_per_room smallint NOT NULL,
    beds_per_bedroom smallint NOT NULL,
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
    primary key(id)
);

CREATE TABLE hms_learning_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(32) NOT NULL,
    abbreviation character varying(16) NOT NULL,
    capacity integer NOT NULL,
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
    asu_username         character varying(32) UNIQUE NOT NULL,
    rlc_id               integer NOT NULL REFERENCES hms_learning_communities(id),
    assigned_by_user     integer NOT NULL,
    assigned_by_initials character varying(8),
    PRIMARY KEY (id)
);

CREATE TABLE hms_learning_community_applications (
    id                              integer NOT NULL,
    user_id                         character varying(32) UNIQUE NOT NULL,
    date_submitted                  integer NOT NULL,
    rlc_first_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_second_choice_id            integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_third_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    why_specific_communities        character varying(2048) NOT NULL,
    strengths_weaknesses            character varying(2048) NOT NULL,
    rlc_question_0                  character varying(2048),
    rlc_question_1                  character varying(2048),
    rlc_question_2                  character varying(2048),
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
    hms_student_id character varying(32) NOT NULL,
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
    created_by character varying(32) NOT NULL,
    primary key(id)
);


CREATE TABLE hms_residence_hall (
    id integer DEFAULT 0 NOT NULL,
    banner_building_code character varying(6) NULL,
    hall_name character varying(64) NOT NULL,
    number_floors smallint NOT NULL,
    rooms_per_floor smallint NOT NULL,
    bedrooms_per_room smallint NOT NULL,
    beds_per_bedroom smallint NOT NULL,
    pricing_tier smallint NOT NULL,
    gender_type smallint NOT NULL,
    air_conditioned smallint NOT NULL,
    is_online smallint NOT NULL,
    numbering_scheme smallint NOT NULL,
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
    room_number character varying(6) NOT NULL,
    building_id smallint NOT NULL,
    floor_number smallint NOT NULL,
    floor_id integer NOT NULL,
    gender_type smallint NOT NULL,
    bedrooms_per_room smallint NOT NULL,
    beds_per_bedroom smallint NOT NULL,
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
    roommate_zero character varying(32) NOT NULL,
    roommate_one character varying(32) NOT NULL,
    roommate_two character varying(32),
    roommate_three character varying(32),
    primary key(id)
);

CREATE TABLE hms_roommate_approval (
    id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    number_roommates SMALLINT NOT NULL,
    approval_hash CHARACTER VARYING(64) NOT NULL,
    roommate_zero CHARACTER VARYING(32) NOT NULL,
    roommate_zero_approved SMALLINT NOT NULL,
    roommate_one CHARACTER VARYING(32) NOT NULL,
    roommate_one_approved SMALLINT NOT NULL,
    roommate_two CHARACTER VARYING(32),
    roommate_two_approved SMALLINT,
    roommate_three CHARACTER VARYING(32),
    roommate_three_approved SMALLINT,
    PRIMARY KEY (id)
)

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

CREATE TABLE hms_bedrooms (
    id INTEGER NOT NULL,
    room_id INTEGER NOT NULL REFERENCES hms_room(id),
    is_online SMALLINT NOT NULL,
    gender_type SMALLINT NOT NULL,
    number_beds SMALLINT NOT NULL,
    is_reserved SMALLINT NOT NULL,
    is_medical SMALLINT NOT NULL,
    added_by INTEGER NOT NULL,
    added_on INTEGER NOT NULL,
    updated_by INTEGER NOT NULL,
    updated_on INTEGER NOT NULL,
    deleted_by INTEGER,
    deleted_on INTEGER,
    bedroom_letter character(1) NOT NULL,
    phone_number INTEGER,
    deleted smallint default 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_beds (
    id INTEGER NOT NULL,
    bedroom_id INTEGER NOT NULL REFERENCES hms_bedrooms(id),
    bed_letter character(1) NOT NULL,
    deleted smallint default 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_student_profiles (
    id INTEGER NOT NULL,
    user_id character varying(32) NOT NULL,
    date_submitted INTEGER NOT NULL,
    arts_and_crafts smallint default 0,
    books_and_reading smallint default 0,
    cars smallint default 0,
    church_activities smallint default 0,
    collecting smallint default 0,
    computers_and_technology smallint default 0,
    dancing smallint default 0,
    fashion smallint default 0,
    fine_arts smallint default 0,
    gardening smallint default 0,
    games smallint default 0,
    humor smallint default 0,
    investing_personal_finance smallint default 0,
    movies smallint default 0,
    music smallint default 0,
    outdoor_activities smallint default 0,
    pets_and_animals smallint default 0,
    photography smallint default 0,
    politics smallint default 0,
    sports smallint default 0,
    travel smallint default 0,
    tv_shows smallint default 0,
    volunteering smallint default 0,
    alternative smallint default 0,
    ambient smallint default 0,
    beach smallint default 0,
    bluegrass smallint default 0,
    blues smallint default 0,
    classical smallint default 0,
    classic_rock smallint default 0,
    country smallint default 0,
    electronic smallint default 0,
    folk smallint default 0,
    heavy_metal smallint default 0,
    hip_hop smallint default 0,
    house smallint default 0,
    industrial smallint default 0,
    jazz smallint default 0,
    popular_music smallint default 0,
    progressive smallint default 0,
    punk smallint default 0,
    r_and_b smallint default 0,
    rap smallint default 0,
    reggae smallint default 0,
    rock smallint default 0,
    world_music smallint default 0,
    study_early_morning smallint default 0,
    study_morning_afternoon smallint default 0,
    study_afternoon_evening smallint default 0,
    study_evening smallint default 0,
    study_late_night smallint default 0,
    political_view smallint default 0,
    major smallint default 0,
    experience smallint default 0,
    sleep_time smallint default 0,
    wakeup_time smallint default 0,
    overnight_guests smallint default 0,
    loudness smallint default 0,
    cleanliness smallint default 0,
    free_time smallint default 0,
    primay key(id)
);


INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (0, 'Leadership & Service Community', 'LSC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (1, 'Outdoor Community', 'OC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (2, 'Wellness Community', 'WC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (3, 'Community of Scientific Interest', 'CSI', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (4, 'Language & Culture Community', 'LCC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (5, 'Black & Gold Community', 'BGC', 50);

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id));

INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (0, 0, 'Describe your current leadership and community service experience and the opportunities you are looking for.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (1, 1, 'What outdoor opportunities would you like to be involved in and describe your current experience.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (2, 2, 'What are your personal experienes with wellness and in what areas of wellness are you most interested?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (3, 3, 'What knowledge, skills, or talent could you offer other students in the Community of Scientific Interests?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (4, 4, 'In what languages are you proficient, learning to speak, or interested in learning?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (5, 5, 'How do you plan to be an active member of the ASU community?');

CREATE SEQUENCE hms_learning_community_questions_seq;
SELECT setval('hms_learning_community_questions_seq', max(hms_learning_community_questions.id));

INSERT INTO hms_pricing_tiers VALUES (1, 3250.00);
INSERT INTO hms_pricing_tiers VALUES (2, 3550.00);
INSERT INTO hms_pricing_tiers VALUES (3, 3650.00);
INSERT INTO hms_pricing_tiers VALUES (4, 4150.00);
INSERT INTO hms_pricing_tiers VALUES (5, 4800.00);

CREATE SEQUENCE hms_pricing_tiers_seq;
SELECT setval('hms_pricing_tiers_seq', max(hms_pricing_tiers.id));
