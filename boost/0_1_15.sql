ALTER TABLE add constraint hms_student_profile_user UNIQUE user_id;

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
