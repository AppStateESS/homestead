create table hms_student_autocomplete (
    banner_id           integer NOT NULL,
    username            character varying,
    first_name          character varying,
    middle_name         character varying,
    last_name           character varying,
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
