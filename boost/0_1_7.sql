ALTER TABLE hms_learning_community_applications DROP COLUMN approved;
ALTER TABLE hms_learning_community_applications DROP COLUMN assigned_by_user;
ALTER TABLE hms_learning_community_applications DROP COLUMN assigned_by_initials;

CREATE TABLE hms_learning_community_assignment (
    id                   integer NOT NULL,
    asu_username         character varying(11) UNIQUE NOT NULL,
    rlc_id               integer NOT NULL REFERENCES hms_learning_communities(id),
    assigned_by_user     integer NOT NULL,
    assigned_by_initials character varying(8),
    PRIMARY KEY (id)
);

ALTER TABLE hms_learning_community_applications ADD COLUMN hms_assignment_id integer;
ALTER TABLE hms_learning_community_applications ADD CONSTRAINT rlcapp_assignment_id
    FOREIGN KEY (hms_assignment_id) REFERENCES hms_learning_community_assignment (id);
