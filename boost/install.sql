CREATE TABLE hms_assignment (
    id integer NOT NULL,
    asu_username character varying(32) NOT NULL,
    bed_id integer NOT NULL,
    timestamp integer NOT NULL,
    deleted smallint NOT NULL,
    primary key(id)
);

CREATE TABLE hms_deadlines (
    student_login_begin_timestamp integer NOT NULL,
    student_login_end_timestamp integer NOT NULL,
    submit_application_begin_timestamp integer NOT NULL,
    submit_application_end_timestamp integer NOT NULL,
    edit_application_end_timestamp integer NOT NULL,
    edit_profile_begin_timestamp integer NOT NULL,
    edit_profile_end_timestamp integer NOT NULL,
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
    freshman_reserved smallint NOT NULL,
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
    gender               character varying(2) NOT NULL,
    assigned_by          character varying(32) NOT NULL,
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
    aggregate smallint default 0,
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
    displayed_room_number character varying(8) NOT NULL,
    building_id smallint NOT NULL,
    floor_number smallint NOT NULL,
    floor_id integer NOT NULL,
    gender_type smallint NOT NULL,
    freshman_reserved smallint NOT NULL,
    ra_room smallint NOT NULL,
    private_room smallint NOT NULL,
    is_lobby smallint NOT NULL,
    bedrooms_per_room smallint NOT NULL,
    beds_per_bedroom smallint NOT NULL,
    learning_community smallint DEFAULT (0)::smallint,
    phone_number integer DEFAULT 0,
    pricing_tier smallint DEFAULT 0,
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
    number_roommates SMALLINT NOT NULL,
    roommate_zero CHARACTER VARYING(32) NOT NULL,
    roommate_zero_approved SMALLINT NOT NULL,
    roommate_zero_personal_hash CHARACTER VARYING(32) NOT NULL,
    roommate_one CHARACTER VARYING(32) NOT NULL,
    roommate_one_approved SMALLINT NOT NULL,
    roommate_one_personal_hash CHARACTER VARYING(32) NOT NULL,
    roommate_two CHARACTER VARYING(32),
    roommate_two_approved SMALLINT,
    roommate_two_personal_hash CHARACTER VARYING(32),
    roommate_three CHARACTER VARYING(32),
    roommate_three_approved SMALLINT,
    roommate_three_personal_hash CHARACTER VARYING(32),
    PRIMARY KEY (id)
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
    banner_id character varying(15),
    phone_number character(4),
    PRIMARY KEY(id)
);

CREATE TABLE hms_student_profiles (
    id INTEGER NOT NULL,
    user_id character varying(32) UNIQUE NOT NULL,
    date_submitted INTEGER NOT NULL,
    alternate_email character varying(64) NULL,
    aim_sn character varying(32) NULL,
    yahoo_sn character varying(32) NULL,
    msn_sn character varying(32) NULL,
    arts_and_crafts smallint,
    books_and_reading smallint,
    cars smallint,
    church_activities smallint,
    collecting smallint,
    computers_and_technology smallint,
    dancing smallint,
    fashion smallint,
    fine_arts smallint,
    gardening smallint,
    games smallint,
    humor smallint,
    investing_personal_finance smallint,
    movies smallint,
    music smallint,
    outdoor_activities smallint,
    pets_and_animals smallint,
    photography smallint,
    politics smallint,
    sports smallint,
    travel smallint,
    tv_shows smallint,
    volunteering smallint,
    writing smallint,
    alternative smallint,
    ambient smallint,
    beach smallint,
    bluegrass smallint,
    blues smallint,
    classical smallint,
    classic_rock smallint,
    country smallint,
    electronic smallint,
    folk smallint,
    heavy_metal smallint,
    hip_hop smallint,
    house smallint,
    industrial smallint,
    jazz smallint,
    popular_music smallint,
    progressive smallint,
    punk smallint,
    r_and_b smallint,
    rap smallint,
    reggae smallint,
    rock smallint,
    world_music smallint,
    study_early_morning smallint,
    study_morning_afternoon smallint,
    study_afternoon_evening smallint,
    study_evening smallint,
    study_late_night smallint,
    political_view smallint,
    major smallint,
    experience smallint,
    sleep_time smallint,
    wakeup_time smallint,
    overnight_guests smallint,
    loudness smallint,
    cleanliness smallint,
    free_time smallint,
    PRIMARY KEY(id)
);

CREATE TABLE hms_cached_student_info (
    id              INTEGER                NOT NULL,
    asu_username    CHARACTER VARYING(32)  NOT NULL,
    room_number     CHARACTER VARYING(10)  NOT NULL,
    hall_name       CHARACTER VARYING(64)  NOT NULL,
    first_name      CHARACTER VARYING(64)  NOT NULL,
    middle_name     CHARACTER VARYING(64),
    last_name       CHARACTER VARYING(64)  NOT NULL,
    address1        CHARACTER VARYING(128),
    address2        CHARACTER VARYING(128),
    address3        CHARACTER VARYING(128),
    city            CHARACTER VARYING(64),
    state           CHARACTER VARYING(5),
    zip             CHARACtER VARYING(11),
    roommate_name   CHARACTER VARYING(172),
    roommate_user   CHARACTER VARYING(32),
    room_phone      CHARACTER VARYING(20),
    phone_number    CHARACTER VARYING(20),
    gender          CHARACTER(1),
    student_type    CHARACTER(5),
    class           CHARACTER(5),
    credit_hours    INTEGER,
    deposit_date    CHARACTER(10),
    deposit_waived  CHARACTER(5),
    movein_time     CHARACTER VARYING(64),
    PRIMARY KEY (id)
);

CREATE TABLE hms_pending_assignment (
    id               INTEGER               NOT NULL,
    gender           SMALLINT              NOT NULL,
    lifestyle_option SMALLINT              NOT NULL,
    chosen           SMALLINT              NOT NULL,
    roommate_zero    CHARACTER VARYING(32) NOT NULL,
    meal_zero        SMALLINT              NOT NULL,
    roommate_one     CHARACTER VARYING(32),
    meal_one         SMALLINT,
    PRIMARY KEY (id)
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
