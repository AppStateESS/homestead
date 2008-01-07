ALTER TABLE hms_floor DROP COLUMN ft_movein;
ALTER TABLE hms_floor DROP COLUMN c_movein;

ALTER TABLE hms_floor ADD COLUMN ft_movein_time_id smallint REFERENCES hms_movein_time(id);
ALTER TABLE hms_floor ADD COLUMN rt_movein_time_id smallint REFERENCES hms_movein_time(id);
