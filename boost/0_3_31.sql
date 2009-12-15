ALTER TABLE hms_floor RENAME ft_movein_time_id TO f_movein_time_id;
ALTER TABLE hms_floor ADD COLUMN t_movein_time_id integer REFERENCES hms_movein_time(id);
