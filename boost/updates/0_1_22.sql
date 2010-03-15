alter table hms_room add column displayed_room_number character varying(8);
update hms_room set displayed_room_number = room_number;
