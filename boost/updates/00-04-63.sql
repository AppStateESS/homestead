alter table hms_learning_communities add column f_movein_time_id integer REFERENCES hms_movein_time(id);
alter table hms_learning_communities add column c_movein_time_id integer REFERENCES hms_movein_time(id);
alter table hms_learning_communities add column t_movein_time_id integer REFERENCES hms_movein_time(id);