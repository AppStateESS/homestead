BEGIN;
CREATE TABLE hms_term (
    term    integer NOT NULL,
    banner_queue smallint NOT NULL,
    docusign_template_id character varying,
    docusign_under18_template_id character varying,
    primary key(term)
);

create table hms_contract (
	id 			integer not null,
	banner_id 	integer not null,
	term 		integer not null REFERENCES hms_term(term),
	envelope_id character varying not null,
	PRIMARY KEY(id)
);

create sequence hms_contract_seq;

CREATE TABLE hms_student_cache (
    banner_id           integer NOT NULL,
    term                integer NOT NULL,
    timestamp           integer NOT NULL,
    username            character varying NOT NULL,
    last_name           character varying NOT NULL,
    first_name          character varying NOT NULL,
    middle_name         character varying,
    preferred_name      character varying,
    confidential        character(1) NOT NULL,
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
    greek               character varying,
    disabled_pin        smallint NOT NULL DEFAULT 0,
    housing_waiver      smallint NOT NULL DEFAULT 0,
    admissions_decision_code    character varying,
    admission_decision_desc     character varying,
    PRIMARY KEY (banner_id, term)
);

CREATE INDEX hms_student_cache_usr_idx ON hms_student_cache(username);

CREATE TABLE hms_student_address_cache (
    banner_id       integer NOT NULL,
    atyp_code       character varying(2) NOT NULL,
    line1           character varying(255) NOT NULL,
    line2           character varying(255),
    line3           character varying(255),
    city            character varying(255) NOT NULL,
    state           character varying(255),
    zip             character varying(10)
);

CREATE INDEX hms_student_address_cache_idx ON hms_student_address_cache(banner_id);

CREATE TABLE hms_student_phone_cache (
    banner_id       integer NOT NULL,
    number          character varying(32) NOT NULL
);

CREATE INDEX hms_student_phone_cache_idx ON hms_student_phone_cache(banner_id);


create table hms_student_autocomplete (
    banner_id           integer NOT NULL,
    username            character varying,
    first_name          character varying,
    middle_name         character varying,
    last_name           character varying,
    first_name_lower    character varying,
    middle_name_lower   character varying,
    last_name_lower	    character varying,
    first_name_meta     character varying,
    middle_name_meta    character varying,
    last_name_meta      character varying,
    start_term          integer,
    end_term            integer,
    PRIMARY KEY(banner_id)
);

create index hms_student_autocomplete_banner_id_index on hms_student_autocomplete (banner_id);
create index hms_student_autocomplete_username on hms_student_autocomplete (username);

create index hms_student_autocomplete_start_term on hms_student_autocomplete (start_term);
create index hms_student_autocomplete_end_term on hms_student_autocomplete (end_term);

create index hms_student_autocomplete_first_meta on hms_student_autocomplete (first_name_meta);
create index hms_student_autocomplete_middle_meta on hms_student_autocomplete (middle_name_meta);
create index hms_student_autocomplete_last_meta on hms_student_autocomplete (last_name_meta);

CREATE TABLE hms_movein_time (
    id              integer NOT NULL,
    begin_timestamp   integer NOT NULL,
    end_timestamp   integer NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    primary key(id)
);
ALTER TABLE hms_movein_time ADD CONSTRAINT unique_time UNIQUE (begin_timestamp, end_timestamp, term);

CREATE TABLE hms_package_desk (
    id          integer NOT NULL,
    name        character varying,
    location    character varying,
    street      character varying,
    city        character varying,
    state       character varying,
    zip         character varying,
    PRIMARY KEY(id)
);

CREATE TABLE hms_package (
    id                  integer NOT NULL,
    carrier             character varying,
    tacking_number      character varying,
    addressed_to        character varying,
    addressed_phone     character varying,
    recipient_banner_id integer NOT NULL,
    received_on         integer NOT NULL,
    received_by         character varying,
    package_desk        integer NOT NULL references hms_package_desk(id),
    pickedup_on         integer,
    released_by         character varying,
    PRIMARY KEY(id)
);

CREATE TABLE hms_residence_hall (
    id                          integer NOT NULL,
    term                        integer NOT NULL REFERENCES hms_term(term),
    banner_building_code        character varying(6) NULL,
    hall_name                   character varying(64) NOT NULL,
    gender_type                 smallint NOT NULL,
    air_conditioned             smallint NOT NULL,
    is_online                   smallint NOT NULL,
    meal_plan_required          smallint DEFAULT 0 NOT NULL,
    added_by                    integer NOT NULL,
    added_on                    integer NOT NULL,
    updated_by                  integer,
    updated_on                  integer,
    exterior_image_id           integer DEFAULT 0,
    other_image_id              integer DEFAULT 0,
    map_image_id                integer DEFAULT 0,
    room_plan_image_id          integer DEFAULT 0,
    assignment_notifications    integer NOT NULL DEFAULT 1,
    package_desk                integer REFERENCES hms_package_desk(id),
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
    allowed_reapplication_student_types character varying(16),
    members_reapply integer not null,
    terms_conditions text,
    primary key(id)
);


CREATE TABLE hms_floor (
    id                  integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    floor_number        smallint DEFAULT (0)::smallint NOT NULL,
    residence_hall_id   smallint NOT NULL REFERENCES hms_residence_hall(id),
    is_online           smallint DEFAULT (0)::smallint NOT NULL,
    gender_type         smallint DEFAULT (0)::smallint NOT NULL,
    added_by            integer NOT NULL,
    added_on            integer NOT NULL,
    updated_by          integer NOT NULL,
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
    persistent_id           character varying NOT NULL,
    term                    integer NOT NULL REFERENCES hms_term(term),
    room_number             character varying(32) NOT NULL,
    floor_id                integer NOT NULL REFERENCES hms_floor(id),
    gender_type             smallint NOT NULL,
    default_gender          smallint NOT NULL,
    ra                      smallint NOT NULL,
    private                 smallint NOT NULL,
    overflow                smallint NOT NULL,
    reserved                smallint NOT NULL DEFAULT 0,
    reserved_reason         character varying,
    reserved_notes          character varying,
    offline                 smallint NOT NULL DEFAULT 0,
    parlor                  smallint NOT NULL DEFAULT 0,
    ada                     smallint NOT NULL DEFAULT 0,
    hearing_impaired        smallint NOT NULL DEFAULT 0,
    bath_en_suite           smallint NOT NULL DEFAULT 0,
    reserved_rlc_id         integer NULL REFERENCES hms_learning_communities(id),
    added_by                integer NOT NULL,
    added_on                integer NOT NULL,
    updated_by              integer,
    updated_on              integer,
    primary key(id)
);

CREATE TABLE hms_bed (
    id              integer NOT NULL,
    term            integer NOT NULL REFERENCES hms_term(term),
    room_id         integer NOT NULL REFERENCES hms_room(id),
    bed_letter      character(1) NOT NULL,
    bedroom_label   character varying(255),
    ra_roommate     smallint NOT NULL DEFAULT 0,
    added_by        integer NOT NULL,
    added_on        integer NOT NULL,
    updated_by      integer NOT NULL,
    updated_on      integer NOT NULL,
    banner_id       character varying(15),
    phone_number    character(4),
    room_change_reserved    smallint NOT NULL DEFAULT 0,
    international_reserved  smallint NOT NULL DEFAULT 0,
    persistent_id           character varying,
    ra                      smallint NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

create table hms_checkin (
    id                  integer NOT NULL,
    banner_id           integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    bed_id              integer NOT NULL REFERENCES hms_bed(id),
    room_id             integer NOT NULL REFERENCES hms_room(id),
    checkin_date        integer NOT NULL,
    checkin_by          character varying,
    key_code            character varying,
    checkout_date       integer,
    checkout_by         character varying,
    express_checkout    smallint,
    improper_checkout   smallint,
    checkout_key_code   character varying,
    key_not_returned    smallint,
    bed_persistent_id 	character varying,
    improper_checkout_note  character varying,
    PRIMARY KEY (id)
);

create index hms_checkin_banner_id_idx ON hms_checkin(banner_id);

create table hms_damage_type(
    id          integer not null,
    category    character varying NOT NULL,
    description character varying NOT NULL,
    cost        integer,
    PRIMARY KEY(id)
);

create sequence hms_damage_type_seq;

create table hms_room_damage(
    id                  integer not null,
    room_persistent_id  character varying not null,
    term                integer not null REFERENCES hms_term(term),
    damage_type         integer not null REFERENCES hms_damage_type(id),
    side                character varying not null,
    note                character varying,
    repaired            smallint not null default 0,
    reported_by         character varying not null,
    reported_on         integer not null,
    PRIMARY KEY(id)
);

create sequence hms_room_damage_seq;

create table hms_room_damage_responsibility (
    id          integer NOT NULL,
    damage_id   integer NOT NULL REFERENCES hms_room_damage(id),
    banner_id   integer NOT NULL,
    state       character varying,
    amount      float,
    assessed_on integer,
    assessed_by character varying,
    PRIMARY KEY(id)
);

alter table hms_room_damage_responsibility add constraint room_damage_responsibility_uniq_key UNIQUE (damage_id, banner_id);

create sequence hms_room_damage_responsibility_seq;


CREATE TABLE hms_assignment (
    id              integer     NOT NULL,
    term            integer     NOT NULL REFERENCES hms_term(term),
    banner_id       integer     NOT NULL,
    asu_username    character varying(32) NOT NULL,
    bed_id          integer     NOT NULL REFERENCES hms_bed(id),
    meal_option     character(2),
    lottery         smallint    NOT NULL DEFAULT 0,
    auto_assigned   smallint    NOT NULL DEFAULT 0,
    added_by        integer     NOT NULL,
    added_on        integer     NOT NULL,
    updated_by      integer     NOT NULL,
    updated_on      integer     NOT NULL,
    letter_printed  smallint    NOT NULL DEFAULT 0,
    email_sent      smallint    NOT NULL DEFAULT 0,
    reason          character varying(20),
    application_term integer,
    class           character(2),
    primary key(id)
);

CREATE TABLE hms_assignment_history (
    id                  integer         NOT NULL,
    banner_id           integer         NOT NULL,
    bed_id              integer		    NOT NULL,
    assigned_on         integer         NOT NULL,
    assigned_by         character varying(32) NOT NULL,
    assigned_reason     character varying(20) NOT NULL,
    removed_on          integer,
    removed_by          character varying(32),
    removed_reason      character varying(20),
    term                integer,
    application_term    integer,
    class               character(2),
    primary key(id)
);

ALTER TABLE hms_assignment ADD CONSTRAINT hms_assignment_uniq_student_const UNIQUE (asu_username, term);
ALTER TABLE hms_assignment ADD CONSTRAINT hms_assignment_uniq_bed_const UNIQUE (bed_id, term);

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
    application_type                character varying(32) NOT NULL,
    denied_email_sent               smallint DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
);

ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlc_application_key UNIQUE (username, term);

CREATE TABLE hms_learning_community_assignment (
    id                  integer NOT NULL,
    application_id      integer NOT NULL REFERENCES hms_learning_community_applications(id),
    rlc_id              integer NOT NULL REFERENCES hms_learning_communities(id),
    gender              integer NOT NULL,
    assigned_by         character varying(32) NOT NULL,
    state               character varying,
    PRIMARY KEY (id)
);

CREATE TABLE hms_application_feature (
	id          int NOT NULL,
    term        int NOT NULL REFERENCES hms_term(term),
    name 		character varying(32) NOT NULL,
    start_date  int NOT NULL,
    end_date    int NOT NULL,
    edit_date   int not null default 0,
    enabled	    smallint NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE UNIQUE INDEX hms_application_feature_term_name_idx ON hms_application_feature (term, name);

CREATE TABLE hms_new_application (
    id                              integer                 NOT NULL,
    term                            integer                 NOT NULL REFERENCES hms_term(term),
    banner_id                       integer                 NOT NULL,
    username                        character varying(32)   NOT NULL,
    gender                          smallint                NOT NULL,
    student_type                    character(1)            NOT NULL,
    application_term                integer                 NOT NULL,
    application_type                character varying(255)  NOT NULL,
    cell_phone                      character varying,
    meal_plan                       character varying(3)	NOT NULL,
    physical_disability             smallint,
    psych_disability                smallint,
    medical_need                    smallint,
    gender_need                     smallint,
    created_on                      integer NOT NULL,
    created_by                      character varying(32) NOT NULL,
    modified_on                     integer NOT NULL,
    modified_by                     character varying(32) NOT NULL,
    cancelled                       smallint not null default 0,
    cancelled_reason                character varying(32),
    cancelled_on                    integer,
    cancelled_by                    character varying(32),
    international                   smallint NOT NULL default 0,
    emergency_contact_name 			varchar,
    emergency_contact_relationship 	varchar,
    emergency_contact_phone 		varchar,
    emergency_contact_email 		varchar,
    emergency_medical_condition 	varchar,
    missing_person_name 			varchar,
    missing_person_relationship 	varchar,
    missing_person_phone 			varchar,
    missing_person_email 			varchar,
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
    smoking_preference      smallint    NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_spring_application (
    id                      integer     NOT NULL REFERENCES hms_new_application(id),
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    smoking_preference      smallint    NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_summer_application (
    id                      integer     NOT NULL REFERENCES hms_new_application(id),
    room_type               integer     NOT NULL,
    smoking_preference      smallint    NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_lottery_application (
    id                      integer NOT NULL REFERENCES hms_new_application(id),
    special_interest        character varying(32),
    magic_winner            smallint NOT NULL default 0,
    invite_expires_on       integer,
    waiting_list_date       integer,
    rlc_interest            smallint not null default 0,
    sorority_pref           character varying(32),
    tf_pref                 smallint NOT NULL default 0,
    wg_pref                 smallint NOT NULL default 0,
    honors_pref             smallint NOT NULL default 0,
    invited_on              integer,
    early_release           character varying,
    PRIMARY KEY(id)
);

create table hms_waitlist_application (
    id integer NOT NULL references hms_new_application (id),
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
    banner_id INTEGER NOT NULL,
    username character varying(32) NOT NULL,
    term            INTEGER NOT NULL REFERENCES hms_term(term),
    date_submitted INTEGER NOT NULL,
    alternate_email character varying(128) NULL,
    fb_link         character varying,
    instagram_sn    character varying,
    twitter_sn      character varying,
    tumblr_sn       character varying,
    kik_sn          character varying,
    about_me        character varying,
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
    christian smallint,
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
    arabic smallint,
    bengali smallint,
    chinese smallint,
    english smallint,
    french smallint,
    german smallint,
    hindi smallint,
    italian smallint,
    japanese smallint,
    javanese smallint,
    korean smallint,
    malay smallint,
    marathi smallint,
    portuguese smallint,
    punjabi smallint,
    russian smallint,
    spanish smallint,
    tamil smallint,
    telugu smallint,
    vietnamese smallint,
    PRIMARY KEY(id)
);

ALTER TABLE hms_student_profiles ADD CONSTRAINT hms_student_profile_user UNIQUE (username, term);

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
    percent_refund integer,
    queued_on integer NOT NULL,
    queued_by integer NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE hms_activity_log (
    user_id     CHARACTER VARYING(32)   NOT NULL,
    timestamp   INTEGER                 NOT NULL,
    activity    INTEGER                 NOT NULL,
    actor       CHARACTER VARYING(32)   NOT NULL,
    notes       CHARACTER VARYING
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

create table hms_room_change_request (
    id                      INTEGER NOT NULL,
    term                    INTEGER NOT NULL REFERENCES hms_term(term),
    reason                  TEXT,
    denied_reason_public    TEXT,
    denied_reason_private   TEXT,
    PRIMARY KEY(id)
);

create sequence hms_room_change_request_seq;

create table hms_room_change_request_state (
    request_id              INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    state_name              character varying,
    effective_date          INTEGER NOT NULL,
    effective_until_date    INTEGER,
    committed_by            character varying,
    PRIMARY KEY(request_id, state_name)
);

create table hms_room_change_participant (
    id              INTEGER NOT NULL,
    request_id      INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    banner_id       INTEGER NOT NULL,
    from_bed        INTEGER NOT NULL,
    to_bed          INTEGER,
    hall_pref1      INTEGER,
    hall_pref2      INTEGER,
    cell_phone      character varying,
    PRIMARY KEY(id)
);

create sequence hms_room_change_participant_seq;

create table hms_room_change_participant_state (
    participant_id          INTEGER NOT NULL REFERENCES hms_room_change_participant(id),
    state_name              character varying,
    effective_date          INTEGER NOT NULL,
    effective_until_date    INTEGER,
    committed_by            character varying,
    PRIMARY KEY(participant_id, state_name)
);

CREATE VIEW hms_room_change_curr_request AS
    SELECT * FROM hms_room_change_request
    JOIN hms_room_change_request_state ON hms_room_change_request.id = hms_room_change_request_state.request_id
    WHERE
        effective_date < extract(epoch from now()) AND
        effective_until_date IS NULL;

CREATE VIEW hms_room_change_curr_participant AS
    SELECT * FROM hms_room_change_participant
    JOIN hms_room_change_participant_state ON hms_room_change_participant.id = hms_room_change_participant_state.participant_id
    WHERE
        effective_date < extract(epoch from now()) AND
        effective_until_date IS NULL;

CREATE VIEW hms_room_change_curr_request_participants AS
    SELECT
        hms_room_change_curr_request.id,
        hms_room_change_curr_request.term,
        hms_room_change_curr_request.reason,
        hms_room_change_curr_request.denied_reason_public,
        hms_room_change_curr_request.denied_reason_private,
        hms_room_change_curr_request.state_name,
        hms_room_change_curr_request.effective_date,
        hms_room_change_curr_request.effective_until_date,
        hms_room_change_curr_request.committed_by,
        hms_room_change_curr_participant.id AS participant_id,
        hms_room_change_curr_participant.banner_id,
        hms_room_change_curr_participant.from_bed,
        hms_room_change_curr_participant.to_bed,
        hms_room_change_curr_participant.state_name AS participant_state_name,
        hms_room_change_curr_participant.effective_date AS participant_effective_date,
        hms_room_change_curr_participant.effective_until_date AS participant_effective_until_date
    FROM hms_room_change_curr_request
    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id;

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
    csv_output_filename  character varying,
    PRIMARY KEY (id)
);

CREATE TABLE hms_report_param (
    id                  INTEGER NOT NULL,
    report_id           INTEGER NOT NULL,
    param_name          character varying,
    param_value         character varying,
    PRIMARY KEY (id)
);

create table hms_temp_assignment (
	room_number character(5) NOT NULL,
	banner_id integer,
	PRIMARY KEY(room_number)
);

CREATE INDEX hms_floor_residence_hall_id_idx ON hms_floor (residence_hall_id);

CREATE INDEX hms_lottery_reservation_expiration_idx ON hms_lottery_reservation (expires_on);

CREATE INDEX hms_assignment_term_idx ON hms_assignment (term);

CREATE INDEX hms_room_crazy_idx ON hms_room (gender_type, reserved, offline, private, ra, overflow, parlor);

CREATE INDEX hms_room_floor_id_idx ON hms_room (floor_id);

INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (3, 'Language & Culture Community', 'LCC', 50, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (20, 'Watauga Global Community', 'WG', 50, 0, 'F', '<p>Watauga Global Community is where classes meet general education requirements in interdisciplinary team-taught (multiple professor) core classes that blend fact, fiction, culture, philosophy, motion, art, music, myth, and religion.</p><p><strong>This community requires a separate application in addition to marking it as a housing preference.  For more information, go to the <a href="http://wataugaglobal.appstate.edu/pagesmith/4" target="_blank" style="color: blue;">Watauga Global Community Website</a>.</strong></p>', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (21, 'The Honors College', 'HN', 50, 0, 'F', '<p><strong>This community requires a separate application in addition to marking it as a housing preference.</strong></p><p>To apply for The Honors College, log into <a href="https://firstconnections.appstate.edu/ugaweb/" target="_blank" style="color: blue;">First Connections</a> and complete the on-line application accordingly.</p><p>For more information, go to <a href="http://www.honors.appstate.edu/" target="_blank" style="color: blue;">The Honors College website</a>.</p>', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (2, 'Academy of Science', 'AS', 40, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (14, 'Art Haus', 'AC', 68, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (4, 'Black & Gold Community', 'BGC', 68, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (15, 'Brain Matters - A Psychology Community', 'PC', 41, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (8, 'Business Exploration', 'AE', 41, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (19, 'Cycling Community', 'CC', 28, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (5, 'Future Educators', 'FE', 38, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (7, 'Living Free Community', 'LF', 34, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (11, 'Living Green', 'LG', 38, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (1, 'Outdoor Community', 'OC', 42, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (6, 'Quiet Study Community', 'QS', 34, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (10, 'Service and Leadership Community', 'SL', 38, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (12, 'Sophomore Year Experience', 'SYE', 32, 0, 'C', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (13, 'Transfer Teacher Educators Community', 'TE', 38, 0, 'T', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (16, 'Sisterhood Experience', 'PC', 116, 0, 'F', '', 0);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info, members_reapply) VALUES (18, 'Band of Brothers Community for Men', 'MC', 114, 0, 'F', '', 0);

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
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (28, 21, 'Why are you interested in The Honors College?');

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id)) FROM hms_learning_communities;

CREATE SEQUENCE hms_learning_community_questions_seq;
SELECT setval('hms_learning_community_questions_seq', max(hms_learning_community_questions.id)) FROM hms_learning_community_questions;

INSERT INTO hms_damage_type VALUES (1, 'Ceiling', 'Light Cover- Cracked/Missing', 25);
INSERT INTO hms_damage_type VALUES (10, 'Cleaning', 'Bathroom/Shower (suite-style buildings)', 30);
INSERT INTO hms_damage_type VALUES (14, 'Cleaning', 'Microfridge', 25);
INSERT INTO hms_damage_type VALUES (15, 'Cleaning', 'Student Moving a Microfridge', 25);
INSERT INTO hms_damage_type VALUES (16, 'Cleaning', 'Microwave', 20);
INSERT INTO hms_damage_type VALUES (17, 'Cleaning', 'Oven', 30);
INSERT INTO hms_damage_type VALUES (18, 'Cleaning', 'Refrigerator', 30);
INSERT INTO hms_damage_type VALUES (20, 'Cleaning', 'Sink (kitchen or bathroom)', 25);
INSERT INTO hms_damage_type VALUES (21, 'Cleaning', 'Tile Floor', 25);
INSERT INTO hms_damage_type VALUES (22, 'Cleaning', 'Vacuum', 25);
INSERT INTO hms_damage_type VALUES (23, 'Cleaning', 'Housekeeping Labor Charges (per hour)', 20);
INSERT INTO hms_damage_type VALUES (38, 'Door', 'Tape Residue (per side)', 20);
INSERT INTO hms_damage_type VALUES (77, 'Furnishings', 'Scratched / Stained / Carved', 25);
INSERT INTO hms_damage_type VALUES (78, 'Keys', 'Apartment / Suite', 45);
INSERT INTO hms_damage_type VALUES (79, 'Keys', 'Traditional Residence Hall', 45);
INSERT INTO hms_damage_type VALUES (100, 'Walls', 'Paint (per wall)', 40);
INSERT INTO hms_damage_type VALUES (103, 'Walls', 'Tape residue (per wall)', 10);
INSERT INTO hms_damage_type VALUES (2, 'Ceiling', 'Tape residue', 35);
INSERT INTO hms_damage_type VALUES (8, 'Ceiling', 'Tile replacement / holes', 10);
INSERT INTO hms_damage_type VALUES (19, 'Cleaning', 'Entire Room (Traditional Hall)', 50);
INSERT INTO hms_damage_type VALUES (9, 'Cleaning', 'Entire Suite/Apartment', 125);
INSERT INTO hms_damage_type VALUES (11, 'Cleaning', 'Carpet (Traditional Hall)', 30);
INSERT INTO hms_damage_type VALUES (12, 'Cleaning', 'Carpet (Suite/Apartment)', 80);
INSERT INTO hms_damage_type VALUES (82, 'Cleaning', 'Furniture Removal', 15);
INSERT INTO hms_damage_type VALUES (83, 'Cleaning', 'Trash Removal', 25);
INSERT INTO hms_damage_type VALUES (42, 'Furnishings', 'Air Conditioning Unit', 10);
INSERT INTO hms_damage_type VALUES (45, 'Furnishings', 'Bed - Frame Damaged/Reassemble/Replace', 25);
INSERT INTO hms_damage_type VALUES (60, 'Furnishings', 'Bed - Mattress - Clean/Replace', 0);
INSERT INTO hms_damage_type VALUES (50, 'Furnishings', 'Chair - Missing/Cracked/Refinish', 25);
INSERT INTO hms_damage_type VALUES (49, 'Furnishings', 'Cabinet', 25);
INSERT INTO hms_damage_type VALUES (69, 'Furnishings', 'Shelves - Broken/Scratched/Missing', 15);
INSERT INTO hms_damage_type VALUES (68, 'Furnishings', 'Mirror', 10);
INSERT INTO hms_damage_type VALUES (72, 'Furnishings', 'Shower Curtain - Missing/Torn/Hooks', 20);
INSERT INTO hms_damage_type VALUES (74, 'Furnishings', 'Towel Bar - Dented/Broken/Missing', 25);
INSERT INTO hms_damage_type VALUES (55, 'Furnishings', 'Desk/Dresser & Drawers', 20);
INSERT INTO hms_damage_type VALUES (57, 'Furnishings', 'Desk/Dresser - Tape Residue', 10);
INSERT INTO hms_damage_type VALUES (65, 'Furnishings', 'Microfridge - Broken/Missing Parts', 10);
INSERT INTO hms_damage_type VALUES (58, 'Furnishings', 'Microwave - Broken/Missing Parts', 50);
INSERT INTO hms_damage_type VALUES (94, 'Walls', 'Cracks/Scratches/Scuffs/Pin holes', 15);
INSERT INTO hms_damage_type VALUES (98, 'Walls', 'Hole (other than pin/nail holes)', 30);
INSERT INTO hms_damage_type VALUES (99, 'Walls', 'Outlet Cover - Damaged/missing', 15);
INSERT INTO hms_damage_type VALUES (39, 'Door', 'Writing on Door', 50);
INSERT INTO hms_damage_type VALUES (31, 'Door', 'Peep Hole - Missing/damaged', 10);
INSERT INTO hms_damage_type VALUES (37, 'Door', 'Door Closer - Missing/Disassembled', 50);
INSERT INTO hms_damage_type VALUES (28, 'Door', 'Hinges', 25);
INSERT INTO hms_damage_type VALUES (34, 'Door', 'Scratches/Holes', 70);
INSERT INTO hms_damage_type VALUES (32, 'Door', 'Replace - Broken/scratches/writing', 150);
INSERT INTO hms_damage_type VALUES (84, 'Window', 'Blinds - Broken/Missing Fins', 20);
INSERT INTO hms_damage_type VALUES (88, 'Window', 'Glass - Broken/Cracked', 75);
INSERT INTO hms_damage_type VALUES (90, 'Window', 'Glass - Tape Residue', 10);
INSERT INTO hms_damage_type VALUES (91, 'Window', 'Screen - Dirty/Damaged/Bent/Replace/Reinstall', 20);
INSERT INTO hms_damage_type VALUES (40, 'Floors', 'Carpet - Holes/replacement', 0);
INSERT INTO hms_damage_type VALUES (41, 'Floors', 'Tile - Missing/damaged', 10);
INSERT INTO hms_damage_type VALUES (5, 'Ceiling', 'Tear/Scratches', 35);
INSERT INTO hms_damage_type VALUES (25, 'Closet', 'Door - Broken/Split/Missing', 125);
INSERT INTO hms_damage_type VALUES (26, 'Closet', 'Door - Remount', 32);
INSERT INTO hms_damage_type VALUES (27, 'Closet', 'Paint', 20);
INSERT INTO hms_damage_type VALUES (80, 'Keys', 'App. Heights/LLC', 45);
INSERT INTO hms_damage_type VALUES (104, 'DSL Equipment', 'Modem/Hub/Power Adapters/Cables (Justice)', 10);
INSERT INTO hms_damage_type VALUES (105, 'Checkout', 'Improper Checkout', 50);

CREATE VIEW hms_hall_structure AS
SELECT hms_bed.id AS bedid,
    hms_room.id AS roomid,
    hms_floor.id AS floorid,
    hms_residence_hall.id AS hallid,
    hms_bed.term AS bed_term,
    hms_room.term AS room_term,
    hms_floor.term AS floor_term,
    hms_residence_hall.term AS hall_term,
    hms_bed.bed_letter,
    hms_bed.banner_id,
    hms_bed.bedroom_label,
    hms_bed.ra_roommate,
    hms_bed.room_change_reserved,
    hms_bed.international_reserved,
    hms_room.gender_type AS room_gender,
    hms_room.reserved,
    hms_room.room_number,
    hms_room.ra,
    hms_room.private,
    hms_room.overflow,
    hms_room.default_gender,
    hms_room.offline,
    hms_room.ada,
    hms_room.hearing_impaired,
    hms_room.bath_en_suite,
    hms_room.parlor,
    hms_floor.floor_number,
    hms_floor.is_online AS floor_online,
    hms_floor.gender_type AS floor_gender,
    hms_floor.f_movein_time_id,
    hms_floor.rt_movein_time_id,
    hms_floor.t_movein_time_id,
    hms_floor.rlc_id,
    hms_floor.floor_plan_image_id,
    hms_residence_hall.banner_building_code,
    hms_residence_hall.hall_name,
    hms_residence_hall.gender_type AS hall_gender,
    hms_residence_hall.air_conditioned,
    hms_residence_hall.is_online AS hall_online,
    hms_residence_hall.meal_plan_required,
    hms_residence_hall.exterior_image_id,
    hms_residence_hall.other_image_id,
    hms_residence_hall.map_image_id,
    hms_residence_hall.room_plan_image_id,
    hms_residence_hall.assignment_notifications
FROM hms_bed
   JOIN hms_room ON hms_bed.room_id = hms_room.id
   JOIN hms_floor ON hms_room.floor_id = hms_floor.id
   JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id;

COMMIT;
