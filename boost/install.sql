BEGIN;
CREATE TABLE hms_term (
    term    integer NOT NULL,
    banner_queue smallint NOT NULL,
    pdf_terms character varying(255),
    txt_terms character varying(255),
    primary key(term)
);

CREATE TABLE hms_student_cache (
    banner_id           integer NOT NULL,
    term                integer NOT NULL,
    timestamp           integer NOT NULL,
    username            character varying(155) NOT NULL,
    last_name           character varying(255) NOT NULL,
    first_name          character varying(255) NOT NULL,
    middle_name         character varying(255),
    dob                 character(10) NOT NULL,
    gender              character(1) NOT NULL,
    deposit_date        character(10),
    type                character(1) NOT NULL,
    application_term    character(6) NOT NULL,
    class               character(2) NOT NULL,
    credit_hours        integer NOT NULL,
    student_level       character varying(16) NOT NULL,
    international       character varying(5) NOT NULL,
    honors              character varying(5) NOT NULL,
    teaching_fellow     character varying(5) NOT NULL,
    watauga_member      character varying(5) NOT NULL,
    PRIMARY KEY (banner_id, term)
);

CREATE INDEX hms_student_cache_usr_idx ON hms_student_cache(username);

CREATE TABLE hms_student_address_cache (
    banner_id       integer NOT NULL,
    atyp_code       character varying(2) NOT NULL,
    line1           character varying(255) NOT NULL,
    line2           character varying(255) NOT NULL,
    line3           character varying(255) NOT NULL,
    city            character varying(255) NOT NULL,
    state           character varying(255) NOT NULL,
    zip             character varying(10)  NOT NULL
);

CREATE INDEX hms_student_address_cache_idx ON hms_student_address_cache(banner_id);

CREATE TABLE hms_student_phone_cache (
    banner_id       integer NOT NULL,
    number          character varying(32) NOT NULL
);

CREATE INDEX hms_student_phone_cache_idx ON hms_student_phone_cache(banner_id);

CREATE TABLE hms_pricing_tiers (
    id          integer NOT NULL,
    tier_value  numeric NOT NULL,
    primary key(id)
);

CREATE TABLE hms_movein_time (
    id              integer NOT NULL,
    begin_timestamp   integer NOT NULL,
    end_timestamp   integer NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    primary key(id)
);
ALTER TABLE hms_movein_time ADD CONSTRAINT unique_time UNIQUE (begin_timestamp, end_timestamp, term);

CREATE TABLE hms_residence_hall (
    id                          integer NOT NULL,
    term                        integer NOT NULL REFERENCES hms_term(term),
    banner_building_code        character varying(6) NULL,
    hall_name                   character varying(64) NOT NULL,
    gender_type                 smallint NOT NULL,
    air_conditioned             smallint NOT NULL,
    is_online                   smallint NOT NULL,
    rooms_for_lottery           integer DEFAULT 0 NOT NULL,
    meal_plan_required          smallint DEFAULT 0 NOT NULL,
    added_by                    smallint NOT NULL,
    added_on                    integer NOT NULL,
    updated_by                  smallint,
    updated_on                  integer,
    exterior_image_id           integer DEFAULT 0,
    other_image_id              integer DEFAULT 0,
    map_image_id                integer DEFAULT 0,
    room_plan_image_id          integer DEFAULT 0,
    assignment_notifications    integer NOT NULL DEFAULT 1,
    primary key(id)
);

-- Referenced by hms_floor, needs to be created first
CREATE TABLE hms_learning_communities (
    id integer DEFAULT 0 NOT NULL,
    community_name character varying(64) NOT NULL,
    abbreviation character varying(16) NOT NULL,
    capacity integer NOT NULL,
    hide integer NOT NULL DEFAULT 0,
    extra_info text,
    allowed_student_types varchar(16),
    primary key(id)
);


CREATE TABLE hms_floor (
    id                  integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    floor_number        smallint DEFAULT (0)::smallint NOT NULL,
    residence_hall_id   smallint NOT NULL REFERENCES hms_residence_hall(id),
    is_online           smallint DEFAULT (0)::smallint NOT NULL,
    gender_type         smallint DEFAULT (0)::smallint NOT NULL,
    added_by            smallint NOT NULL,
    added_on            integer NOT NULL,
    updated_by          smallint NOT NULL,
    updated_on          integer NOT NULL,
    rlc_id              smallint REFERENCES hms_learning_communities(id),
    f_movein_time_id    integer REFERENCES hms_movein_time(id),
    t_movein_time_id    integer REFERENCES hms_movein_time(id),
    rt_movein_time_id   integer REFERENCES hms_movein_time(id),
    floor_plan_image_id integer DEFAULT 0,
    primary key(id)
);

CREATE TABLE hms_room (
    id                      integer NOT NULL,
    term                    integer NOT NULL REFERENCES hms_term(term),
    room_number             character varying(32) NOT NULL,
    floor_id                integer NOT NULL REFERENCES hms_floor(id),
    gender_type             smallint NOT NULL,
    default_gender          smallint NOT NULL,
    ra_room                 smallint NOT NULL,
    private_room            smallint NOT NULL,
    is_overflow             smallint NOT NULL,
    phone_number            integer DEFAULT 0,
    pricing_tier            smallint REFERENCES hms_pricing_tiers(id),
    is_medical              smallint DEFAULT (0)::smallint,
    is_reserved             smallint DEFAULT (0)::smallint,
    is_online               smallint DEFAULT (0)::smallint NOT NULL,
    added_by                smallint NOT NULL,
    added_on                integer NOT NULL,
    updated_by              smallint,
    updated_on              integer,
    primary key(id)
);

CREATE TABLE hms_bed (
    id              integer NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    room_id         integer NOT NULL REFERENCES hms_room(id),
    bed_letter      character(1) NOT NULL,
    bedroom_label   character varying(255),
    ra_bed          smallint NOT NULL DEFAULT (0)::smallint,
    added_by        integer NOT NULL,
    added_on        integer NOT NULL,
    updated_by      integer NOT NULL,
    updated_on      integer NOT NULL,
    banner_id       character varying(15),
    phone_number    character(4),
    room_change_reserved smallint NOT NULL DEFAULT(0)::smallint,
    PRIMARY KEY(id)
);

CREATE TABLE hms_assignment (
    id              integer     NOT NULL,
    term            integer     NOT NULL REFERENCES hms_term(term),
    asu_username    character varying(32) NOT NULL,
    bed_id          integer     NOT NULL REFERENCES hms_bed(id),
    meal_option     smallint default 0,
    lottery         smallint    NOT NULL DEFAULT 0,
    auto_assigned   smallint    NOT NULL DEFAULT 0,
    added_by        integer     NOT NULL,
    added_on        integer     NOT NULL,
    updated_by      integer     NOT NULL,
    updated_on      integer     NOT NULL,
    letter_printed  smallint    NOT NULL DEFAULT 0,
    email_sent      smallint    NOT NULL DEFAULT 0,
    primary key(id)
);

CREATE TABLE hms_assignment_queue (
    id              integer NOT NULL,
    action          integer NOT NULL,
    asu_username    character varying(32) NOT NULL,
    building_code   character varying(6) NOT NULL,
    bed_code        character varying(15) NOT NULL,
    meal_option     smallint default 0,
    term            integer NOT NULL REFERENCES hms_term(term),
    queued_on       integer NOT NULL,
    queued_by       integer NOT NULL,
    primary key(id)
);

CREATE TABLE hms_learning_community_questions (
    id integer DEFAULT 0 NOT NULL,
    learning_community_id integer DEFAULT 0 NOT NULL REFERENCES hms_learning_communities(id),
    question_text text NOT NULL,
    primary key(id)
);

CREATE TABLE hms_learning_community_applications (
    id                              integer NOT NULL,
	username                        character varying(32) NOT NULL,
    term                            integer NOT NULL REFERENCES hms_term(term),
    date_submitted                  integer NOT NULL,
    rlc_first_choice_id             integer NOT NULL REFERENCES hms_learning_communities(id),
    rlc_second_choice_id            integer REFERENCES hms_learning_communities(id),
    rlc_third_choice_id             integer REFERENCES hms_learning_communities(id),
    why_specific_communities        character varying(4096) NOT NULL,
    strengths_weaknesses            character varying(4096) NOT NULL,
    rlc_question_0                  character varying(4096),
    rlc_question_1                  character varying(4096),
    rlc_question_2                  character varying(4096),
    denied                          integer DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
);

ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlc_application_key UNIQUE (username, term);

CREATE TABLE hms_learning_community_assignment (
    id                  integer NOT NULL,
    application_id      integer NOT NULL REFERENCES hms_learning_community_applications(id), 
    rlc_id              integer NOT NULL REFERENCES hms_learning_communities(id),
    gender              integer NOT NULL,
    assigned_by         character varying(32) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_application_feature (
	id			int NOT NULL,
    term    	int NOT NULL REFERENCES hms_term(term),
    name 		character varying(32) NOT NULL,
    start_date	int NOT NULL,
    end_date	int NOT NULL,
    edit_date   int not null default 0,
    enabled		smallint NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE UNIQUE INDEX hms_application_feature_term_name_idx ON hms_application_feature (term, name);

CREATE TABLE hms_new_application (
    id                              integer                 NOT NULL,
    term                            integer                 NOT NULL REFERENCES hms_term(term),
    banner_id                       character varying(9)    NOT NULL,
    username                        character varying(32)   NOT NULL,
    gender                          smallint                NOT NULL,
    student_type                    character(1)            NOT NULL,
    application_term                integer                 NOT NULL,
    application_type                character varying(255)  NOT NULL,
    cell_phone                      character varying(10),
    meal_plan                       character varying(3),
    physical_disability             smallint,
    psych_disability                smallint,
    medical_need                    smallint,
    gender_need                     smallint,
    withdrawn                       smallint NOT NULL default 0,
    created_on                      integer NOT NULL,
    created_by                      character varying(32) NOT NULL,
    modified_on                     integer NOT NULL,
    modified_by                     character varying(32) NOT NULL,
    PRIMARY KEY(id)
);

ALTER TABLE hms_new_application ADD CONSTRAINT new_application_key UNIQUE (username, term);
ALTER TABLE hms_new_application ADD CONSTRAINT new_application_key2 UNIQUE (banner_id, term);

CREATE TABLE hms_fall_application (
    id                      integer     NOT NULL REFERENCES hms_new_application(id),
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    rlc_interest            smallint    NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_spring_application (
    id                      integer     NOT NULL REFERENCES hms_new_application(id),
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_summer_application (
    id          integer NOT NULL REFERENCES hms_new_application(id),
    room_type   integer NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_lottery_application (
    id                      integer NOT NULL REFERENCES hms_new_application(id),
    roommate1_username      character varying(32),
    roommate2_username      character varying(32),
    roommate3_username      character varying(32),
    roommate1_app_term      integer,
    roommate2_app_term      integer,
    roommate3_app_term      integer,
    special_interest        character varying(32),
    magic_winner            smallint NOT NULL default 0,
    invite_expires_on       integer,
    waiting_list_hide       smallint DEFAULT 0,
    PRIMARY KEY(id)
);

create table hms_waitlist_application (
    id integer NOT NULL references hms_new_application (id),
    waiting_list_hide integer NOT NULL default 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_roommate (
    id           INTEGER NOT NULL,
    term         INTEGER NOT NULL REFERENCES hms_term(term),
    requestor    CHARACTER VARYING(32) NOT NULL,
    requestee    CHARACTER VARYING(32) NOT NULL,
    confirmed    INTEGER NOT NULL DEFAULT 0,
    requested_on INTEGER NOT NULL,
    confirmed_on INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE hms_student_profiles (
    id INTEGER NOT NULL,
    username character varying(32) UNIQUE NOT NULL,
    term            INTEGER NOT NULL REFERENCES hms_term(term),
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
    rotc smallint,
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

CREATE TABLE hms_banner_queue (
    id integer NOT NULL,
    "type" integer NOT NULL,
    asu_username character varying(32) NOT NULL,
    building_code character varying(6) NOT NULL,
    bed_code character varying(15) NOT NULL,
    meal_plan character varying(5),
    meal_code smallint DEFAULT 0,
    term integer NOT NULL,
    queued_on integer NOT NULL,
    queued_by integer NOT NULL
);

CREATE TABLE hms_activity_log (
    user_id     CHARACTER VARYING(32)   NOT NULL,
    timestamp   INTEGER                 NOT NULL,
    activity    INTEGER                 NOT NULL,
    actor       CHARACTER VARYING(32)   NOT NULL,
    notes       CHARACTER VARYING(512)
);

CREATE TABLE hms_lottery_reservation (
    id                  INTEGER                 NOT NULL,
    asu_username        CHARACTER VARYING(32)   NOT NULL,
    requestor           CHARACTER VARYING(32)   NOT NULL,
    term                INTEGER                 NOT NULL,
    bed_id              INTEGER                 NOT NULL,
    expires_on          INTEGER                 NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_eligibility_waiver (
   id                   INTEGER                 NOT NULL,
   asu_username         CHARACTER VARYING(32)   NOT NULL,
   term                 INTEGER                 NOT NULL,
   created_on           INTEGER                 NOT NULL,
   created_by           CHARACTER VARYING(32)   NOT NULL,
   PRIMARY KEY (id)
);

CREATE TABLE hms_special_assignment (
    id INTEGER NOT NULL,
    term INTEGER NOT NULL,
    username VARCHAR(16) NOT NULL,
    hall VARCHAR(6) NOT NULL,
    floor INTEGER,
    room INTEGER
);

CREATE TABLE hms_role (
    id                  INTEGER                 NOT NULL,
    name                text                    NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_permission (
    id                  INTEGER                 NOT NULL,
    name                VARCHAR(32)             NOT NULL,
    full_name           text,
    PRIMARY KEY(id)
);

CREATE TABLE hms_role_perm (
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    permission          INTEGER NOT NULL REFERENCES hms_permission(id),
    PRIMARY KEY(role, permission)
);

CREATE TABLE hms_user_role (
	id                  INTEGER NOT NULL,
    user_id             INTEGER NOT NULL REFERENCES users(id),
    role                INTEGER NOT NULL REFERENCES hms_role(id),
    class               VARCHAR(64),
    instance            INTEGER,
	UNIQUE (user_id, role, class, instance),
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_request (
    id                  INTEGER NOT NULL,
    state               INTEGER NOT NULL DEFAULT 0,
    term                INTEGER NOT NULL REFERENCES hms_term(term),
    curr_hall           INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    requested_bed_id    INTEGER REFERENCES hms_bed(id),
    switch_with         VARCHAR(32),
    reason              TEXT,
    cell_phone          VARCHAR(11),
    username            VARCHAR(32),
    denied_reason       TEXT,
    denied_by           VARCHAR(32),
    updated_on          INTEGER,
    switch_with         VARCHAR(32),
    is_swap             SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_participants (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    username            VARCHAR(32),
    name                VARCHAR(255),
    role                VARCHAR(255),
    added_on            INTEGER NOT NULL,
    updated_on          INTEGER NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_preferences (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    building            INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    PRIMARY KEY(id)
);

CREATE TABLE hms_report (
    id                   INTEGER NOT NULL,
    report               character varying(255) NOT NULL,
    created_by           character varying(255) NOT NULL,
    created_on           integer NOT NULL,
    scheduled_exec_time  integer NOT NULL,
    began_timestamp      integer,
    completed_timestamp  integer,
    html_output_filename character varying,
    pdf_output_filename  character varying,
    PRIMARY KEY (id)
);

INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (3, 'Language & Culture Community', 'LCC', 50, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (20, 'Watauga Global Community', 'WG', 50, 0, 'F', '<p>Watauga Global Community is where classes meet general education requirements in interdisciplinary team-taught (multiple professor) core classes that blend fact, fiction, culture, philosophy, motion, art, music, myth, and religion.</p><p><strong>This community requires a separate application in addition to marking it as a housing preference.  For more information, go to the <a href="http://wataugaglobal.appstate.edu/pagesmith/4" target="_blank" style="color: blue;">Watauga Global Community Website</a>.</strong></p>');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (21, 'Heltzer Honors Program', 'HN', 50, 0, 'F', '<p><strong>This community requires a separate application in addition to marking it as a housing preference.</strong></p><p>To apply for the Heltzer Honors Program, log into <a href="https://firstconnections.appstate.edu/ugaweb/" target="_blank" style="color: blue;">First Connections</a> and complete the on-line application accordingly.</p><p>For more information, go to the <a href="http://www.honors.appstate.edu/" target="_blank" style="color: blue;"> Heltzer Honors Program website</a>.</p>');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (2, 'Academy of Science', 'AS', 40, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (14, 'Art Haus', 'AC', 68, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (4, 'Black & Gold Community', 'BGC', 68, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (15, 'Brain Matters - A Psychology Community', 'PC', 41, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (8, 'Business Exploration', 'AE', 41, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (19, 'Cycling Community', 'CC', 28, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (5, 'Future Educators', 'FE', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (7, 'Living Free Community', 'LF', 34, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (11, 'Living Green', 'LG', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (1, 'Outdoor Community', 'OC', 42, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (6, 'Quiet Study Community', 'QS', 34, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (10, 'Service and Leadership Community', 'SL', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (12, 'Sophomore Year Experience', 'SYE', 32, 0, 'C', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (13, 'Transfer Teacher Educators Community', 'TE', 38, 0, 'T', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (16, 'Sisterhood Experience', 'PC', 116, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (18, 'Band of Brothers Community for Men', 'MC', 114, 0, 'F', '');

INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (11, 1, 'What role have outdoor adventure experiences played in your life and how do you see these continuing in your college years? Are there more experiences you want to have or contribute to, and skills or abilities you want to develop?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (12, 10, 'What service and/or leadership experiences do you bring to the community and what do you hope to gain from involvement with service and/or leadership activities on campus?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (13, 5, 'What are your future education goals and how will this community be of benefit to you?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (14, 11, 'What do you hope to learn by living in the Living Green Community and what will you contribute to sustainability effors in the residence hall?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (15, 14, 'How do you feel an artist and/or the creative process can be supported though community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (16, 15, 'What about psychology interests you and why are you interested in living in a residential community focused on exploring relationships between the brain, behavior, and mind?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (17, 8, 'Why do you think business would make a great career? Give one example of some business event that has been of interest to you.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (18, 16, 'Why are you interested in living in an all female residence hall?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (19, 18, 'How will your involvement in a community of men enhance your college experience?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (20, 19, 'What is it about bicycling that you enjoy?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (21, 3, 'What language(s) do you know/want to learn, and how would you take action in this community to craete an environment that promotes language appreciation and cultural understanding?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (23, 13, 'How will this community be of benefit to you as a future teacher? How will this community be of benefit to you as a transfer student?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (22, 4, 'How do you plan to be an active member of the ASU community and the Black and Gold Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (24, 7, 'What lifestyle choices have you made that will help you contribute to the Living Free Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (25, 6, 'What are your study goals and how will the quiet study community help you to reach them?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (26, 2, 'This National Science Foundation-supported scholarship provides mentoring and research in the math and science disciplines of chemistry, computer science, geology, mathematics, physics, and astronomy. Which of these areas are you most interested in and why?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (27, 20, 'Why are you interested in the Watauga Global Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (28, 21, 'Why are you interested in the Heltzer Honors Program?');

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id)) FROM hms_learning_communities;

CREATE SEQUENCE hms_learning_community_questions_seq;
SELECT setval('hms_learning_community_questions_seq', max(hms_learning_community_questions.id)) FROM hms_learning_community_questions;

INSERT INTO hms_pricing_tiers VALUES (1, 3250.00);
INSERT INTO hms_pricing_tiers VALUES (2, 3550.00);
INSERT INTO hms_pricing_tiers VALUES (3, 3650.00);
INSERT INTO hms_pricing_tiers VALUES (4, 4150.00);
INSERT INTO hms_pricing_tiers VALUES (5, 4800.00);

CREATE SEQUENCE hms_pricing_tiers_seq;
SELECT setval('hms_pricing_tiers_seq', max(hms_pricing_tiers.id)) FROM hms_pricing_tiers;
COMMIT;
