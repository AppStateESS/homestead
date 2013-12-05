create table hms_room_damage_responsibility (
    id          integer NOT NULL,
    damage_id   integer NOT NULL REFERENCES hms_room_damage(id),
    banner_id   integer NOT NULL,
    state       character varying,
    amount      integer,
    PRIMARY KEY(id)
);

alter table hms_room_damage_responsibility add constraint room_damage_responsibility_uniq_key UNIQUE (damage_id, banner_id);

create sequence hms_room_damage_responsibility_seq;