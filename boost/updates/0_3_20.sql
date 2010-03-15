BEGIN;

CREATE TABLE hms_spring_application (
    id                      integer     NOT NULL,
    lifestyle_option        smallint    NOT NULL,
    preferred_bedtime       smallint    NOT NULL,
    room_condition          smallint    NOT NULL,
    PRIMARY KEY(id)
);


ALTER TABLE hms_spring_application ADD CONSTRAINT id_fkey FOREIGN KEY (id) REFERENCES hms_new_application (id);
ALTER TABLE hms_fall_application ADD CONSTRAINT id_fkey FOREIGN KEY (id) REFERENCES hms_new_application (id);
ALTER TABLE hms_summer_application ADD CONSTRAINT id_fkey FOREIGN KEY (id) REFERENCES hms_new_application (id);

COMMIT;
