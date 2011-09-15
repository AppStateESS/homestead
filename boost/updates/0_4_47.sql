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
