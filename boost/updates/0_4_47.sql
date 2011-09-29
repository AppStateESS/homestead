ALTER TABLE hms_assignment ADD COLUMN reason character varying(20) default 'anone';

CREATE TABLE hms_assignment_history (
    id                  integer         NOT NULL,
    banner_id           integer         NOT NULL,
    room                character varying(50) NOT NULL,
    assigned_on         integer         NOT NULL,
    assigned_by         character varying(32) NOT NULL,
    assigned_reason     character varying(20) default 'anone',
    removed_on          integer,
    removed_by          character varying(32),
    removed_reason      character varying(20),
    term                integer,
    primary key(id)
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

alter table hms_new_application alter column banner_id TYPE integer USING CAST(trim(banner_id) AS integer);