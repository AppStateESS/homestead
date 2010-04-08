BEGIN;
CREATE TABLE hms_term (
    term    integer NOT NULL,
    banner_queue smallint NOT NULL,
    pdf_terms character varying(255),
    txt_terms character varying(255),
    primary key(term)
);

-- Terms that can recieve applications for a specific app_term
CREATE TABLE hms_term_applications (
    app_term integer NOT NULL REFERENCES hms_term(term),
    term     integer NOT NULL REFERENCES hms_term(term),
    required integer NOT NULL default 0
);
ALTER TABLE hms_term_applications ADD CONSTRAINT unique_term_pairing UNIQUE (app_term, term);

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
    community_name character varying(32) NOT NULL,
    abbreviation character varying(16) NOT NULL,
    capacity integer NOT NULL,
    hide integer NOT NULL DEFAULT 0,
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

CREATE TABLE hms_deadlines (
    id                                      integer NOT NULL,
    term                                    integer NOT NULL REFERENCES hms_term(term),
    submit_application_begin_timestamp      integer NOT NULL,
    submit_application_end_timestamp        integer NOT NULL,
    edit_application_end_timestamp          integer NOT NULL,
    edit_profile_begin_timestamp            integer NOT NULL,
    edit_profile_end_timestamp              integer NOT NULL,
    search_profiles_begin_timestamp         integer NOT NULL,
    search_profiles_end_timestamp           integer NOT NULL,
    submit_rlc_application_end_timestamp    integer NOT NULL,
    select_roommate_begin_timestamp         integer NOT NULL,
    select_roommate_end_timestamp           integer NOT NULL,
    view_assignment_begin_timestamp         integer NOT NULL,
    view_assignment_end_timestamp           integer NOT NULL,
    move_in_timestamp                       integer NOT NULL,
    lottery_signup_begin_timestamp          integer NOT NULL,
    lottery_signup_end_timestamp            integer NOT NULL,
    updated_by                              smallint NOT NULL,
    updated_on                              integer NOT NULL,
    primary key(id)
);

ALTER TABLE hms_deadlines ADD UNIQUE(term);

CREATE TABLE hms_hall_communities (
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
    rlc_id               integer NOT NULL REFERENCES hms_learning_communities(id),
    gender               integer NOT NULL,
    assigned_by          character varying(32) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_learning_community_applications (
    id                              integer NOT NULL,
    user_id                         character varying(32) NOT NULL,
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
    hms_assignment_id               integer REFERENCES hms_learning_community_assignment(id),
    PRIMARY KEY(id)
);

ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlc_application_key UNIQUE (user_id, term);

CREATE TABLE hms_learning_community_floors (
    learning_communities_id integer NOT NULL REFERENCES hms_learning_communities(id),
    floor_id                integer NOT NULL REFERENCES hms_floor(id),
    PRIMARY KEY (learning_communities_id)
); 

CREATE TABLE hms_application_feature (
	id			int NOT NULL,
    term    	int NOT NULL REFERENCES hms_term(term),
    name 		character varying(32) NOT NULL,
    startDate	int NOT NULL,
    endDate		int NOT NULL,
    edit_date   int not null default 0,
    PRIMARY KEY(id)
);

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

CREATE TABLE hms_lottery_entry (
    id                  INTEGER                 NOT NULL,
    asu_username        CHARACTER VARYING(32)   NOT NULL,
    term                INTEGER                 NOT NULL,
    created_on          INTEGER                 NOT NULL,
    application_term    INTEGER                 NOT NULL,
    gender              smallint                NOT NULL,
    roommate1_username  CHARACTER VARYING(32),
    roommate1_app_term  INTEGER,
    roommate2_username  CHARACTER VARYING(32),
    roommate2_app_term  INTEGER,
    roommate3_username  CHARACTER VARYING(32),
    roommate3_app_term  INTEGER,
    cell_phone          CHARACTER VARYING(32),
    physical_disability smallint DEFAULT 0,
    psych_disability    smallint DEFAULT 0,
    medical_need        smallint DEFAULT 0,
    gender_need         smallint DEFAULT 0,
    magic_winner        smallint DEFAULT 0      NOT NULL,
    special_interest    CHARACTER VARYING(32),
    waiting_list_hide   INTEGER,
    meal_option         smallint,
    PRIMARY KEY (id)
);
ALTER TABLE hms_lottery_entry ADD CONSTRAINT unique_entry UNIQUE (term, asu_username);

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

INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (10, 'Community of Servant Leaders', 'LSC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (1, 'Outdoor Community', 'OC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (2, 'Community of Scientific Interest', 'CSI', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (3, 'Language & Culture Community', 'LCC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (4, 'Black & Gold Community', 'BGC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (5, 'Community for Future Educators', 'FE', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (6, 'Quiet Study Community', 'QS', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (7, 'Living Free Community', 'LF', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (8, 'Entrepreneurs Community', 'EN', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (9, 'The Man Floor', 'TMF', 50);

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id)) FROM hms_learning_communities;

INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (10, 10, 'Describe your current leadership and community service experience and the opportunities you are looking for.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (1, 1, 'What outdoor opportunities would you like to be involved in and describe your current experience.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (2, 2, 'What knowledge, skills, or talent could you offer other students in the Community of Scientific Interests?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (3, 3, 'In what languages are you proficient, learning to speak, or interested in learning?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (4, 4, 'How do you plan to be an active member of the ASU community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (5, 5, 'What are your future education goals and how will this community be of benefit to you?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (6, 6, 'What are your study goals and how will this community help you to reach them?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (7, 7, 'What lifestyle choices have you made that will help you contribute to this community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (8, 8, 'What are your goals for joining this community and how do you plan to be an active member in this community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (9, 9, 'The man floor question here!');

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
