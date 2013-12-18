insert into hms_permission values (3, 'assess_damage', 'Assess Room Damages');
insert into hms_role_perm values (1, 3);

alter table hms_room_damage_responsibility add column assessed_by character varying;
alter table hms_room_damage_responsibility add column assessed_on character varying;