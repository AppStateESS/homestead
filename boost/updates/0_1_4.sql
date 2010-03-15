DROP TABLE hms_deadlines;

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
