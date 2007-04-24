ALTER TABLE hms_student_profiles add constraint hms_student_profile_user UNIQUE (user_id);

ALTER TABLE hms_deadlines ADD COLUMN edit_profile_begin_timestamp integer;
ALTER TABLE hms_deadlines ADD COLUMN edit_profile_end_timestamp integer;

UPDATE hms_deadlines SET edit_profile_begin_timestamp = 1177348714;
UPDATE hms_deadlines SET edit_profile_end_timestamp = 1177349774;

ALTER TABLE hms_deadlines ALTER COLUMN edit_profile_begin_timestamp SET NOT NULL;
ALTER TABLE hms_deadlines ALTER COLUMN edit_profile_end_timestamp SET NOT NULL;

ALTER TABLE hms_student_profiles ADD COLUMN alternate_email character varying(64);
ALTER TABLE hms_student_profiles ADD COLUMN aim_sn character varying(32);
ALTER TABLE hms_student_profiles ADD COLUMN yahoo_sn character varying(32);
ALTER TABLE hms_student_profiles ADD COLUMN msn_sn character varying(32);

ALTER TABLE hms_student_profiles ALTER COLUMN alternate_email SET NULL;
ALTER TABLE hms_student_profiles ALTER COLUMN aim_sn SET NULL;
ALTER TABLE hms_student_profiles ALTER COLUMN yahoo_sn SET NULL;
ALTER TABLE hms_student_profiles ALTER COLUMN msn_sn SET NULL;

DROP TABLE hms_roommate_hashes;
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
);
